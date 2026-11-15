<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprimir recibos {{ $payment->numero }}</title>
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
            max-width: 360px;
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

        .receipt-company p {
            margin: 2px 0;
            font-size: 12px;
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

    <div class="print-actions">
        <button type="button" class="print-btn" onclick="window.print()">Imprimir de nuevo</button>
        <button type="button" class="print-btn print-btn-secondary" onclick="window.close()">Cerrar</button>
    </div>

    <div class="receipt-page">
        @for ($copy = 1; $copy <= 2; $copy++)
            <section class="receipt-copy">
                <div class="receipt-company">
                    <h1>{{ $data->nombre_empresa }}</h1>
                    @if ($data->slogan_empresa != '')
                        <p>{{ $data->slogan_empresa }}</p>
                    @endif
                    @if ($data->nit_empresa != '')
                        <p>NIT: {{ $data->nit_empresa }}</p>
                    @endif
                    @if ($data->direccion_empresa != '')
                        <p>Dir: {{ $data->direccion_empresa }}</p>
                    @endif
                    @if ($data->telefono_empresa != '')
                        <p>Tel: {{ $data->telefono_empresa }}</p>
                    @endif
                </div>

                <div class="receipt-divider"></div>

                <div class="receipt-centered">
                    <div><strong>RECIBO No. {{ $payment->numero }}</strong></div>
                    <div>{{ $payment->created_at }}</div>
                </div>

                <div class="receipt-divider"></div>

                <div class="receipt-block">
                    <strong>Cliente:</strong> {{ $payment->name_client }}<br>
                    <strong>DPI:</strong> {{ $payment->invoice->client_product->client->user->dni }}<br>
                    <strong>Plan:</strong> {{ $payment->name_product }}<br>
                    <strong>Direccion:</strong> {{ $payment->address_plan }}
                </div>

                <div class="receipt-divider"></div>

                <div class="receipt-row">
                    <span>Concepto</span>
                    <span>{{ ($payment->invoice->payments_advancement > 0) ? 'Pago adelantado' : 'Pago del mes de '.$mes }}</span>
                </div>
                <div class="receipt-row">
                    <span>Forma de pago</span>
                    <span>{{ (($payment->method_payment == '1') ? 'Deposito' : (($payment->method_payment == '3') ? 'Efectivo' : 'Tarjeta')) }}</span>
                </div>
                <div class="receipt-row">
                    <span>Monto</span>
                    <span class="receipt-total">{{ $payment->price_formatted }}</span>
                </div>

                @if ($payment->no_voucher != "")
                    <div class="receipt-row">
                        <span>Transferencia No.</span>
                        <span>{{ $payment->no_voucher }}</span>
                    </div>
                @endif

                @if ($payment->reportado_por != '')
                    <div class="receipt-row">
                        <span>Reportado por</span>
                        <span>{{ $payment->reportado_por }}</span>
                    </div>
                @endif

                @if ($payment->observation != '')
                    <div class="receipt-block">
                        <strong>Observacion:</strong> {{ $payment->observation }}
                    </div>
                @endif

                <div class="receipt-divider"></div>
                <div class="receipt-label">{{ $copy == 1 ? 'COPIA CLIENTE' : 'COPIA EMPRESA' }}</div>
            </section>
        @endfor
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 250);
        });
    </script>
</body>
</html>
