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

        .receipt-page {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .receipt-copy {
            padding: 14px 16px 16px;
        }

        .receipt-copy + .receipt-copy {
            border-top: 2px dashed #94a3b8;
            position: relative;
            margin-top: 18px;
            padding-top: 24px;
        }

        .receipt-copy + .receipt-copy::before {
            content: "CORTE AQUI";
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.12em;
        }

        .receipt-company {
            text-align: center;
            margin-bottom: 12px;
        }

        .receipt-company h1 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .receipt-company-logo {
            margin-bottom: 8px;
        }

        .receipt-company-logo img {
            display: inline-block;
            height: auto;
            object-fit: contain;
        }

        .receipt-company p {
            margin: 2px 0;
            font-size: 12px;
        }

        .receipt-cutline {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px dashed #94a3b8;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #64748b;
        }

        .receipt-divider {
            border-top: 1px dashed #0f172a;
            margin: 10px 0;
        }

        .receipt-centered {
            text-align: center;
        }

        .receipt-meta,
        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .receipt-block {
            margin-bottom: 10px;
            font-size: 12px;
        }

        .receipt-block-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 4px;
        }

        .receipt-block-value {
            max-width: 68%;
            text-align: right;
            word-break: break-word;
        }

        .receipt-block strong,
        .receipt-row strong {
            font-weight: 700;
        }

        .receipt-total {
            font-size: 15px;
            font-weight: 800;
        }

        .receipt-label {
            margin-top: 10px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.08em;
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
    <div class="print-actions">
        <button type="button" class="print-btn" onclick="window.print()">Imprimir de nuevo</button>
        <button type="button" class="print-btn print-btn-secondary" onclick="window.close()">Cerrar</button>
    </div>

    <div class="receipt-page" style="max-width: {{ $receiptWidth }}mm;">
        @for ($copy = 1; $copy <= $ticketConfig->ticket_copias; $copy++)
            <section class="receipt-copy" style="padding: {{ $receiptPaddingTop }}mm {{ $receiptPaddingRight }}mm {{ $receiptPaddingBottom }}mm {{ $receiptPaddingLeft }}mm;">
                <div class="receipt-company">
                    @if ($ticketConfig->ticket_mostrar_logo)
                        <div class="receipt-company-logo">
                            <img src="{{ asset(logoEmpresaConfig()) }}" alt="{{ $data->nombre_empresa }}" style="width: {{ (int) $ticketConfig->ticket_logo_ancho }}px;">
                        </div>
                    @endif
                    <h1 style="{{ $logoStyle }}">{{ $data->nombre_empresa }}</h1>
                    @if ($data->slogan_empresa != '')
                        <p style="{{ $encabezadoStyle }}">{{ $data->slogan_empresa }}</p>
                    @endif
                    @if ($data->nit_empresa != '')
                        <p style="{{ $encabezadoStyle }}">NIT: {{ $data->nit_empresa }}</p>
                    @endif
                    @if ($data->direccion_empresa != '')
                        <p style="{{ $encabezadoStyle }}">Dir: {{ $data->direccion_empresa }}</p>
                    @endif
                    @if ($data->telefono_empresa != '')
                        <p style="{{ $encabezadoStyle }}">Tel: {{ $data->telefono_empresa }}</p>
                    @endif
                </div>

                <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}"></div>

                <div class="receipt-centered" style="{{ $tituloStyle }}">
                    <div><strong>{{ $ticketConfig->ticket_documento_titulo }} No. {{ $payment->numero }}</strong></div>
                    <div>{{ $payment->created_at }}</div>
                </div>

                <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}"></div>

                <div class="receipt-block" style="{{ $clienteStyle }}">
                    <div class="receipt-block-row">
                        <span><strong>Cliente</strong></span>
                        <span class="receipt-block-value">{{ $payment->name_client }}</span>
                    </div>
                    @if ($ticketConfig->ticket_mostrar_dpi && optional($payment->invoice->client_product->client->user)->dni != '')
                        <div class="receipt-block-row">
                            <span><strong>DPI</strong></span>
                            <span class="receipt-block-value">{{ $payment->invoice->client_product->client->user->dni }}</span>
                        </div>
                    @endif
                    <div class="receipt-block-row">
                        <span><strong>Plan</strong></span>
                        <span class="receipt-block-value">{{ $payment->name_product }}</span>
                    </div>
                    @if ($ticketConfig->ticket_mostrar_direccion_plan && $payment->address_plan != '')
                        <div class="receipt-block-row">
                            <span><strong>Direccion</strong></span>
                            <span class="receipt-block-value">{{ $payment->address_plan }}</span>
                        </div>
                    @endif
                </div>

                <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}"></div>

                <div class="receipt-row" style="{{ $conceptoStyle }}">
                    <span>Concepto</span>
                    <span>{{ ($payment->invoice->payments_advancement > 0) ? 'Pago adelantado' : 'Pago del mes de '.$mes }}</span>
                </div>
                <div class="receipt-row" style="{{ $conceptoStyle }}">
                    <span>Forma de pago</span>
                    <span>{{ (($payment->method_payment == '1') ? 'Deposito' : (($payment->method_payment == '3') ? 'Efectivo' : 'Tarjeta')) }}</span>
                </div>
                <div class="receipt-row" style="{{ $conceptoStyle }}">
                    <span>Monto</span>
                    <span class="receipt-total">{{ $payment->price_formatted }}</span>
                </div>

                @if ($payment->no_voucher != "")
                    <div class="receipt-row" style="{{ $conceptoStyle }}">
                        <span>Transferencia No.</span>
                        <span>{{ $payment->no_voucher }}</span>
                    </div>
                @endif

                @if ($payment->reportado_por != '')
                    <div class="receipt-row" style="{{ $conceptoStyle }}">
                        <span>Reportado por</span>
                        <span>{{ $payment->reportado_por }}</span>
                    </div>
                @endif

                @if ($ticketConfig->ticket_mostrar_observacion && $payment->observation != '')
                    <div class="receipt-block" style="{{ $conceptoStyle }}">
                        <strong>Observacion:</strong> {{ $payment->observation }}
                    </div>
                @endif

                @if ($ticketConfig->ticket_mensaje_final != '')
                    <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}"></div>
                    <div class="receipt-centered" style="{{ $pieStyle }}">
                        <strong>{{ $ticketConfig->ticket_mensaje_final }}</strong>
                    </div>
                @endif

                <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}"></div>
                <div class="receipt-label" style="{{ $pieStyle }}">
                    @if ($ticketConfig->ticket_copias <= 1)
                        ORIGINAL
                    @else
                        {{ $copy == 1 ? 'COPIA CLIENTE' : 'COPIA EMPRESA' }}
                    @endif
                </div>

                @if ($ticketConfig->ticket_copias > 1 && $ticketConfig->ticket_mostrar_linea_corte && $copy < $ticketConfig->ticket_copias)
                    <div class="receipt-cutline">CORTE AQUI</div>
                @endif
            </section>
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
