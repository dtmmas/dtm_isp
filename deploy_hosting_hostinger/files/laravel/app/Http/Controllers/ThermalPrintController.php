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

        return response()->json([
            'printer' => config('printing.thermal.printer'),
            'copies' => max(1, (int) config('printing.thermal.copies', 2)),
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
        $copies = max(1, (int) config('printing.thermal.copies', 2));
        $lineWidth = max(24, (int) config('printing.thermal.line_width', 32));
        $content = '';

        for ($copy = 1; $copy <= $copies; $copy++) {
            $content .= "\x1B\x40";
            $content .= "\x1B\x61\x01";
            $content .= "\x1B\x45\x01";
            $content .= $this->normalizeText($company->nombre_empresa)."\n";
            $content .= "\x1B\x45\x00";

            if (!empty($company->slogan_empresa)) {
                $content .= $this->wrapText($company->slogan_empresa, $lineWidth, true);
            }

            if (!empty($company->nit_empresa)) {
                $content .= $this->wrapText('NIT: '.$company->nit_empresa, $lineWidth, true);
            }

            if (!empty($company->direccion_empresa)) {
                $content .= $this->wrapText('DIR: '.$company->direccion_empresa, $lineWidth, true);
            }

            if (!empty($company->telefono_empresa)) {
                $content .= $this->wrapText('TEL: '.$company->telefono_empresa, $lineWidth, true);
            }

            $content .= $this->divider($lineWidth);
            $content .= $this->centeredLine('RECIBO No. '.$payment->numero, $lineWidth);
            $content .= $this->centeredLine((string) $payment->created_at, $lineWidth);
            $content .= $this->divider($lineWidth);

            $content .= $this->wrapText('Cliente: '.$payment->name_client, $lineWidth);
            $content .= $this->wrapText('DPI: '.optional($payment->invoice->client_product->client->user)->dni, $lineWidth);
            $content .= $this->wrapText('Plan: '.$payment->name_product, $lineWidth);
            $content .= $this->wrapText('Dir: '.$payment->address_plan, $lineWidth);
            $content .= $this->divider($lineWidth);

            $content .= $this->wrapText('Concepto: '.(($payment->invoice->payments_advancement > 0) ? 'Pago adelantado' : 'Pago del mes de '.$this->mesFecha($payment->invoice->date)), $lineWidth);
            $content .= $this->wrapText('Forma de pago: '.$this->paymentMethodLabel($payment->method_payment), $lineWidth);
            $content .= $this->wrapText('Monto: '.$payment->price_formatted, $lineWidth);

            if (!empty($payment->no_voucher)) {
                $content .= $this->wrapText('Transferencia No.: '.$payment->no_voucher, $lineWidth);
            }

            if (!empty($payment->reportado_por)) {
                $content .= $this->wrapText('Reportado por: '.$payment->reportado_por, $lineWidth);
            }

            if (!empty($payment->observation)) {
                $content .= $this->wrapText('Obs: '.$payment->observation, $lineWidth);
            }

            $content .= $this->divider($lineWidth);
            $content .= $this->centeredLine($copy === 1 ? 'COPIA CLIENTE' : 'COPIA EMPRESA', $lineWidth);
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
