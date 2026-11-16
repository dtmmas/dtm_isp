<section class="receipt-copy" style="padding: {{ $receiptPaddingTop }}mm {{ $receiptPaddingRight }}mm {{ $receiptPaddingBottom }}mm {{ $receiptPaddingLeft }}mm;">
    <div class="receipt-company">
        @if ($ticketConfig->ticket_mostrar_logo)
            <div class="receipt-company-logo">
                <img src="{{ $ticketLogoSrc }}" alt="{{ $data->nombre_empresa }}" style="width: {{ (int) $ticketConfig->ticket_logo_ancho }}px;">
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

    <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>

    <div class="receipt-centered" style="{{ $tituloStyle }}">
        <div><strong>{{ $ticketConfig->ticket_documento_titulo }} No. {{ $payment->numero }}</strong></div>
        <div>{{ $payment->created_at }}</div>
    </div>

    <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>

    <div class="receipt-block">
        <table class="receipt-kv" style="{{ $clienteStyle }}">
            <tr>
                <td class="receipt-kv-label"><strong>Cliente</strong></td>
                <td class="receipt-kv-value">{{ $payment->name_client }}</td>
            </tr>
            @if ($ticketConfig->ticket_mostrar_dpi && optional($payment->invoice->client_product->client->user)->dni != '')
                <tr>
                    <td class="receipt-kv-label"><strong>DPI</strong></td>
                    <td class="receipt-kv-value">{{ $payment->invoice->client_product->client->user->dni }}</td>
                </tr>
            @endif
            <tr>
                <td class="receipt-kv-label"><strong>Plan</strong></td>
                <td class="receipt-kv-value">{{ $payment->name_product }}</td>
            </tr>
            @if ($ticketConfig->ticket_mostrar_direccion_plan && $payment->address_plan != '')
                <tr>
                    <td class="receipt-kv-label"><strong>Direccion</strong></td>
                    <td class="receipt-kv-value">{{ $payment->address_plan }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>

    <div class="receipt-block">
        <table class="receipt-kv" style="{{ $conceptoStyle }}">
            <tr>
                <td class="receipt-kv-label">Concepto</td>
                <td class="receipt-kv-value">{{ ($payment->invoice->payments_advancement > 0) ? 'Pago adelantado' : 'Pago del mes de '.$mes }}</td>
            </tr>
            <tr>
                <td class="receipt-kv-label">Forma de pago</td>
                <td class="receipt-kv-value">{{ (($payment->method_payment == '1') ? 'Deposito' : (($payment->method_payment == '3') ? 'Efectivo' : 'Tarjeta')) }}</td>
            </tr>
            <tr>
                <td class="receipt-kv-label"><strong>Monto</strong></td>
                <td class="receipt-kv-value receipt-total">{{ $payment->price_formatted }}</td>
            </tr>
            @if ($payment->no_voucher != '')
                <tr>
                    <td class="receipt-kv-label">Transferencia No.</td>
                    <td class="receipt-kv-value">{{ $payment->no_voucher }}</td>
                </tr>
            @endif
            @if ($payment->reportado_por != '')
                <tr>
                    <td class="receipt-kv-label">Reportado por</td>
                    <td class="receipt-kv-value">{{ $payment->reportado_por }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if ($ticketConfig->ticket_mostrar_observacion && $payment->observation != '')
        <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>
        <div class="receipt-block receipt-observation" style="{{ $conceptoStyle }}">
            <strong>Observacion:</strong> {{ $payment->observation }}
        </div>
    @endif

    @if ($ticketConfig->ticket_mensaje_final != '')
        <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>
        <div class="receipt-centered" style="{{ $pieStyle }}">
            <strong>{{ $ticketConfig->ticket_mensaje_final }}</strong>
        </div>
    @endif

    <div class="receipt-divider" style="border-top-style: {{ $dividerStyle }}; border-top-width: {{ $dividerWidth }};"></div>
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
