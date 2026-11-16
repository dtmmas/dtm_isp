<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($ticketConfig = ticketPrintConfig())
    <title>Imprimir {{ strtolower($ticketConfig->ticket_documento_titulo) }} {{ $payment->numero }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px 14px 32px;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #eef2f7;
        }

        .print-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 18px;
        }

        .print-btn {
            border: 0;
            border-radius: 12px;
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: #2563eb;
        }

        .print-btn-secondary {
            background: #334155;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .receipt-page {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }

            @page {
                size: A4;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    @php($data = empresaConfig())
    @php($encabezadoStyle = ticketSectionStyleCss($ticketConfig, 'encabezado'))
    @php($logoStyle = ticketSectionStyleCss($ticketConfig, 'logo'))
    @php($tituloStyle = ticketSectionStyleCss($ticketConfig, 'titulo'))
    @php($clienteStyle = ticketSectionStyleCss($ticketConfig, 'cliente'))
    @php($conceptoStyle = ticketSectionStyleCss($ticketConfig, 'concepto'))
    @php($pieStyle = ticketSectionStyleCss($ticketConfig, 'pie'))
    @php($receiptWidth = (int) $ticketConfig->ticket_rollo_ancho_mm)
    @php($receiptPaddingTop = (int) $ticketConfig->ticket_margen_superior_mm)
    @php($receiptPaddingRight = (int) $ticketConfig->ticket_margen_derecho_mm)
    @php($receiptPaddingBottom = (int) $ticketConfig->ticket_margen_inferior_mm)
    @php($receiptPaddingLeft = (int) $ticketConfig->ticket_margen_izquierdo_mm)
    @php($dividerStyle = $ticketConfig->ticket_separador_estilo)
    @include('payments.partials.receipt-styles')
    <style>
        .receipt-page {
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }
    </style>
    <div class="print-actions">
        <button type="button" class="print-btn" onclick="window.print()">Imprimir de nuevo</button>
        <button type="button" class="print-btn print-btn-secondary" onclick="window.close()">Cerrar</button>
    </div>

    <div class="receipt-page">
        @php($ticketLogoSrc = asset(logoEmpresaConfig()))
        @for ($copy = 1; $copy <= $ticketConfig->ticket_copias; $copy++)
            @include('payments.partials.receipt-copy', ['ticketLogoSrc' => $ticketLogoSrc, 'copy' => $copy])
        @endfor
    </div>

    @if ($ticketConfig->ticket_auto_imprimir)
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 250);
            });
        </script>
    @endif
</body>
</html>
