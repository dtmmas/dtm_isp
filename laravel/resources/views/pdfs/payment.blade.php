<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ strtolower($ticketConfig->ticket_documento_titulo) }} {{ $payment->numero }}</title>
    @php($data = empresaConfig())
    @php($encabezadoStyle = ticketSectionStyleCss($ticketConfig, 'encabezado'))
    @php($logoStyle = ticketSectionStyleCss($ticketConfig, 'logo'))
    @php($tituloStyle = ticketSectionStyleCss($ticketConfig, 'titulo'))
    @php($clienteStyle = ticketSectionStyleCss($ticketConfig, 'cliente'))
    @php($conceptoStyle = ticketSectionStyleCss($ticketConfig, 'concepto'))
    @php($pieStyle = ticketSectionStyleCss($ticketConfig, 'pie'))
    @php($receiptPaddingTop = (int) $ticketConfig->ticket_margen_superior_mm)
    @php($receiptPaddingRight = (int) $ticketConfig->ticket_margen_derecho_mm)
    @php($receiptPaddingBottom = (int) $ticketConfig->ticket_margen_inferior_mm)
    @php($receiptPaddingLeft = (int) $ticketConfig->ticket_margen_izquierdo_mm)
    @php($dividerStyle = $ticketConfig->ticket_separador_estilo)
    @php($dividerWidth = ticketDividerWidthCss($ticketConfig->ticket_separador_grosor ?? 'normal'))
    @php($ticketLogoSrc = dirname(base_path()).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, logoEmpresaConfig()))
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            color: #0f172a;
            background: #ffffff;
        }
    </style>
    @include('payments.partials.receipt-styles')
</head>
<body>
    <div class="receipt-page">
        @for ($copy = 1; $copy <= $ticketConfig->ticket_copias; $copy++)
            @include('payments.partials.receipt-copy', ['ticketLogoSrc' => $ticketLogoSrc, 'copy' => $copy])
        @endfor
    </div>
</body>
</html>
