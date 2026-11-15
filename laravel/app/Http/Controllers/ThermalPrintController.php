<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Traits\FunctionGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ThermalPrintController extends Controller
{
    use FunctionGeneral;

    public function paymentReceipt(Payment $payment)
    {
        abort_unless($this->canUsePaymentPrinting(), 403);

        $payment->loadMissing('invoice.client_product.client.user', 'invoice.client_product.product', 'user');
        $ticketConfig = ticketPrintConfig();

        return response()->json([
            'printer' => config('printing.thermal.printer'),
            'copies' => max(1, (int) $ticketConfig->ticket_copias),
            'content' => base64_encode($this->buildEscPosReceipt($payment)),
            'fallback_url' => route('payments.print', $payment->id),
        ]);
    }

    public function qzCertificate()
    {
        abort_unless(Auth::check(), 403);

        $path = $this->resolveConfiguredPath(config('printing.thermal.qz.certificate_path'));

        if (!$path || !is_file($path)) {
            return response('', 204)->header('Content-Type', 'text/plain');
        }

        return response(file_get_contents($path), 200)->header('Content-Type', 'text/plain');
    }

    public function qzSign(Request $request)
    {
        abort_unless(Auth::check(), 403);

        $path = $this->resolveConfiguredPath(config('printing.thermal.qz.private_key_path'));
        $payload = (string) $request->query('request', '');

        if ($payload === '' || !$path || !is_file($path)) {
            return response('', 204)->header('Content-Type', 'text/plain');
        }

        $privateKey = openssl_get_privatekey(file_get_contents($path));

        if (!$privateKey) {
            return response('No fue posible cargar la llave privada de QZ Tray.', 500)
                ->header('Content-Type', 'text/plain');
        }

        $signature = null;
        $algorithm = $this->opensslAlgorithm(config('printing.thermal.qz.signature_algorithm', 'SHA512'));

        openssl_sign($payload, $signature, $privateKey, $algorithm);

        if (!$signature) {
            return response('No fue posible firmar la solicitud de QZ Tray.', 500)
                ->header('Content-Type', 'text/plain');
        }

        return response(base64_encode($signature), 200)->header('Content-Type', 'text/plain');
    }

    private function canUsePaymentPrinting()
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->can('payments.create') || $user->can('payments.index');
    }

    private function buildEscPosReceipt(Payment $payment)
    {
        $company = empresaConfig();
        $ticketConfig = ticketPrintConfig();
        $copies = max(1, (int) $ticketConfig->ticket_copias);
        $lineWidth = $this->lineWidthForRoll($ticketConfig);
        $content = '';

        for ($copy = 1; $copy <= $copies; $copy++) {
            $content .= "\x1B\x40";
            $content .= $this->applySectionStyle('logo', $ticketConfig);
            $content .= "\x1B\x61\x01";
            $content .= $this->normalizeText($company->nombre_empresa)."\n";
            $content .= $this->resetSectionStyle();

            if (!empty($company->slogan_empresa)) {
                $content .= $this->styledWrapText($company->slogan_empresa, $lineWidth, 'encabezado', $ticketConfig, true);
            }

            if (!empty($company->nit_empresa)) {
                $content .= $this->styledWrapText('NIT: '.$company->nit_empresa, $lineWidth, 'encabezado', $ticketConfig, true);
            }

            if (!empty($company->direccion_empresa)) {
                $content .= $this->styledWrapText('DIR: '.$company->direccion_empresa, $lineWidth, 'encabezado', $ticketConfig, true);
            }

            if (!empty($company->telefono_empresa)) {
                $content .= $this->styledWrapText('TEL: '.$company->telefono_empresa, $lineWidth, 'encabezado', $ticketConfig, true);
            }

            $content .= $this->divider($lineWidth);
            $content .= $this->styledCenteredLine($ticketConfig->ticket_documento_titulo.' No. '.$payment->numero, $lineWidth, 'titulo', $ticketConfig);
            $content .= $this->styledCenteredLine((string) $payment->created_at, $lineWidth, 'titulo', $ticketConfig);
            $content .= $this->divider($lineWidth);

            $content .= $this->styledKeyValueLine('Cliente', $payment->name_client, $lineWidth, 'cliente', $ticketConfig);
            if ($ticketConfig->ticket_mostrar_dpi && !empty(optional($payment->invoice->client_product->client->user)->dni)) {
                $content .= $this->styledKeyValueLine('DPI', optional($payment->invoice->client_product->client->user)->dni, $lineWidth, 'cliente', $ticketConfig);
            }
            $content .= $this->styledKeyValueLine('Plan', $payment->name_product, $lineWidth, 'cliente', $ticketConfig);
            if ($ticketConfig->ticket_mostrar_direccion_plan && !empty($payment->address_plan)) {
                $content .= $this->styledKeyValueLine('Dir', $payment->address_plan, $lineWidth, 'cliente', $ticketConfig);
            }
            $content .= $this->divider($lineWidth);

            $content .= $this->styledWrapText('Concepto: '.(($payment->invoice->payments_advancement > 0) ? 'Pago adelantado' : 'Pago del mes de '.$this->mesFecha($payment->invoice->date)), $lineWidth, 'concepto', $ticketConfig);
            $content .= $this->styledWrapText('Forma de pago: '.$this->paymentMethodLabel($payment->method_payment), $lineWidth, 'concepto', $ticketConfig);
            $content .= $this->styledWrapText('Monto: '.$payment->price_formatted, $lineWidth, 'concepto', $ticketConfig);

            if (!empty($payment->no_voucher)) {
                $content .= $this->styledWrapText('Transferencia No.: '.$payment->no_voucher, $lineWidth, 'concepto', $ticketConfig);
            }

            if (!empty($payment->reportado_por)) {
                $content .= $this->styledWrapText('Reportado por: '.$payment->reportado_por, $lineWidth, 'concepto', $ticketConfig);
            }

            if ($ticketConfig->ticket_mostrar_observacion && !empty($payment->observation)) {
                $content .= $this->styledWrapText('Obs: '.$payment->observation, $lineWidth, 'concepto', $ticketConfig);
            }

            if (!empty($ticketConfig->ticket_mensaje_final)) {
                $content .= $this->divider($lineWidth);
                $content .= $this->styledWrapText($ticketConfig->ticket_mensaje_final, $lineWidth, 'pie', $ticketConfig, true);
            }

            $content .= $this->divider($lineWidth);
            $content .= $this->styledCenteredLine($this->copyLabel($copy, $copies), $lineWidth, 'pie', $ticketConfig);
            $content .= "\n\n\n";
            $content .= "\x1D\x56\x00";
        }

        return $content;
    }

    private function centeredLine($text, $lineWidth)
    {
        $text = $this->normalizeText($text);

        if (mb_strlen($text) >= $lineWidth) {
            return $text."\n";
        }

        $padding = (int) floor(($lineWidth - mb_strlen($text)) / 2);

        return str_repeat(' ', max(0, $padding)).$text."\n";
    }

    private function divider($lineWidth)
    {
        return str_repeat('-', $lineWidth)."\n";
    }

    private function wrapText($text, $lineWidth, $center = false)
    {
        $text = $this->normalizeText($text);

        if ($text === '') {
            return '';
        }

        $wrapped = wordwrap($text, $lineWidth, "\n", true);
        $lines = explode("\n", $wrapped);
        $formatted = '';

        foreach ($lines as $line) {
            $formatted .= $center ? $this->centeredLine($line, $lineWidth) : $line."\n";
        }

        return $formatted;
    }

    private function keyValueLine($label, $value, $lineWidth)
    {
        $label = $this->normalizeText($label);
        $value = $this->normalizeText($value);

        if ($label === '') {
            return $this->wrapText($value, $lineWidth);
        }

        $labelText = $label.':';
        $minGap = 2;
        $availableValueWidth = $lineWidth - mb_strlen($labelText) - $minGap;

        if ($availableValueWidth < 8) {
            return $this->wrapText($labelText.' '.$value, $lineWidth);
        }

        if (mb_strlen($value) <= $availableValueWidth) {
            return $labelText.str_repeat(' ', $lineWidth - mb_strlen($labelText) - mb_strlen($value)).$value."\n";
        }

        $wrappedValue = explode("\n", wordwrap($value, $availableValueWidth, "\n", true));
        $output = $labelText.str_repeat(' ', max($minGap, $lineWidth - mb_strlen($labelText) - mb_strlen($wrappedValue[0]))).$wrappedValue[0]."\n";
        $indent = str_repeat(' ', mb_strlen($labelText) + $minGap);

        foreach (array_slice($wrappedValue, 1) as $line) {
            $output .= $indent.$line."\n";
        }

        return $output;
    }

    private function styledCenteredLine($text, $lineWidth, $section, $ticketConfig)
    {
        return "\x1B\x61\x01"
            .$this->applySectionStyle($section, $ticketConfig)
            .$this->centeredLine($text, $lineWidth)
            .$this->resetSectionStyle()
            ."\x1B\x61\x00";
    }

    private function styledWrapText($text, $lineWidth, $section, $ticketConfig, $center = false)
    {
        return "\x1B\x61".($center ? "\x01" : "\x00")
            .$this->applySectionStyle($section, $ticketConfig)
            .$this->wrapText($text, $lineWidth, $center)
            .$this->resetSectionStyle()
            ."\x1B\x61\x00";
    }

    private function styledKeyValueLine($label, $value, $lineWidth, $section, $ticketConfig)
    {
        return "\x1B\x61\x00"
            .$this->applySectionStyle($section, $ticketConfig)
            .$this->keyValueLine($label, $value, $lineWidth)
            .$this->resetSectionStyle();
    }

    private function applySectionStyle($section, $ticketConfig)
    {
        $style = ticketSectionStyle($ticketConfig, $section);
        $commands = '';

        $commands .= "\x1B\x4D".($style->familia === 'mono' ? "\x01" : "\x00");
        $commands .= "\x1B\x45".($style->peso === 'normal' ? "\x00" : "\x01");
        $commands .= "\x1B\x47".($style->peso === 'full' ? "\x01" : "\x00");
        $commands .= "\x1D\x21".$this->escPosSizeCommand($style->tamano);

        return $commands;
    }

    private function resetSectionStyle()
    {
        return "\x1B\x45\x00\x1B\x47\x00\x1D\x21\x00\x1B\x4D\x00";
    }

    private function escPosSizeCommand($fontSize)
    {
        if ($fontSize >= 20) {
            return "\x11";
        }

        if ($fontSize >= 16) {
            return "\x10";
        }

        return "\x00";
    }

    private function lineWidthForRoll($ticketConfig)
    {
        switch ((int) $ticketConfig->ticket_rollo_ancho_mm) {
            case 112:
                return 60;
            case 110:
                return 58;
            case 80:
                return 42;
            case 72:
                return 38;
            case 58:
            default:
                return max(24, (int) config('printing.thermal.line_width', 32));
        }
    }

    private function normalizeText($value)
    {
        $value = Str::ascii((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim((string) $value);
    }

    private function paymentMethodLabel($method)
    {
        switch ((string) $method) {
            case '1':
                return 'Deposito';
            case '2':
                return 'Tarjeta';
            case '3':
                return 'Efectivo';
            default:
                return 'Indefinido';
        }
    }

    private function copyLabel($copy, $copies)
    {
        if ($copies <= 1) {
            return 'ORIGINAL';
        }

        return $copy === 1 ? 'COPIA CLIENTE' : 'COPIA EMPRESA';
    }

    private function opensslAlgorithm($algorithm)
    {
        switch (strtoupper((string) $algorithm)) {
            case 'SHA1':
                return OPENSSL_ALGO_SHA1;
            case 'SHA256':
                return OPENSSL_ALGO_SHA256;
            case 'SHA512':
            default:
                return OPENSSL_ALGO_SHA512;
        }
    }

    private function resolveConfiguredPath($path)
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) || Str::startsWith($path, ['/', '\\'])) {
            return $path;
        }

        return base_path($path);
    }
}
