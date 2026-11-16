<div>
    <style>
        .invoice-action-stack {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            align-items: flex-start;
        }

        .invoice-table {
            width: max-content;
            min-width: 100%;
            table-layout: auto;
        }

        .invoice-table thead th,
        .invoice-table tbody td {
            vertical-align: top;
            white-space: normal;
        }

        .invoice-table thead th {
            font-size: .8rem;
            font-weight: 700;
            color: #475569;
            border-bottom-width: 1px;
        }

        .invoice-col-date,
        .invoice-col-month,
        .invoice-col-amount,
        .invoice-col-status {
            white-space: nowrap;
        }

        .invoice-col-date {
            min-width: 7.25rem;
        }

        .invoice-col-month {
            min-width: 8rem;
        }

        .invoice-col-client,
        .invoice-col-plan {
            min-width: 12rem;
        }

        .invoice-col-address {
            min-width: 16rem;
            max-width: 24rem;
        }

        .invoice-col-amount {
            min-width: 7rem;
        }

        .invoice-col-status {
            min-width: 7.5rem;
        }

        .invoice-col-actions {
            min-width: 18rem;
            width: 1%;
        }

        .invoice-mobile-row {
            display: none;
        }

        .invoice-mobile-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
            padding: .9rem 1rem;
        }

        .invoice-mobile-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .65rem;
        }

        .invoice-mobile-card-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .invoice-mobile-card-subtitle {
            font-size: .72rem;
            color: #64748b;
            margin-top: .15rem;
        }

        .invoice-mobile-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem .8rem;
            margin-bottom: .75rem;
        }

        .invoice-mobile-card-item {
            min-width: 0;
        }

        .invoice-mobile-card-label {
            display: block;
            font-size: .65rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .1rem;
        }

        .invoice-mobile-card-value {
            display: block;
            font-size: .83rem;
            color: #0f172a;
            line-height: 1.28;
            word-break: break-word;
        }

        .invoice-mobile-card-actions {
            border-top: 1px solid #e2e8f0;
            padding-top: .7rem;
        }

        .invoice-currency-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            margin-right: .3rem;
            font-size: .82rem;
            font-weight: 700;
            line-height: 1;
        }

        @media (max-width: 767.98px) {
            .invoice-desktop-row {
                display: none;
            }

            .invoice-mobile-row {
                display: table-row;
            }

            .invoice-mobile-row td {
                padding: .45rem 0 !important;
                border-top: none !important;
            }

            .invoice-mobile-card-grid {
                grid-template-columns: 1fr 1fr;
            }

            .invoice-action-stack .btn {
                flex: 1 1 calc(50% - .45rem);
            }
        }

        @media (max-width: 1199.98px) {
            .invoice-col-client,
            .invoice-col-plan {
                min-width: 10rem;
            }

            .invoice-col-address {
                min-width: 13rem;
                max-width: 18rem;
            }

            .invoice-col-actions {
                min-width: 15rem;
            }
        }
    </style>
    <div class="row">
        <div class=" mb-3 col-xs-12 col-sm-8">
            @if ($mostrarCliente)
            <div class="input-group col">
                <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre, apellido, telefono, direccion o referencia">
                <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
            </div>
            @endif
        </div>
        
        <div class="mb-3 col-xs-12 col-sm-4">
            <select wire:model="mostrarFacturasPendientes" class="form-control">
                <option value='All'>Ver Todas</option>
                <option {{(('0'==$mostrarFacturasPendientes)?'selected':'')}}  value="0">Ver Pendiente</option>
                <option {{(('1'==$mostrarFacturasPendientes)?'selected':'')}}  value="1">Ver Pagada</option>
                <option {{(('-1'==$mostrarFacturasPendientes)?'selected':'')}}  value="-1">Ver Vencidas</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped invoice-table">
            <thead>
                <tr>
                    <th class="invoice-col-date">Fecha</th>
                    <th class="invoice-col-month">Mes</th>
                    @if ($mostrarCliente)
                    <th class="invoice-col-client">Cliente</th>
                    @endif
                    @if ($mostrarPlan)
                    <th class="invoice-col-plan">Plan</th>
                    <th class="invoice-col-address">Direccion</th>
                    @endif
                    <th class="invoice-col-amount">Monto</th>
                    <th class="invoice-col-status">Estado</th>
                    <th class="invoice-col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_invoices as $invoice)
                    @php($latestPayment = $invoice->payments->sortByDesc('id')->first())
                    <tr class="invoice-desktop-row">
                        <td data-th="Fecha" class="invoice-col-date">{{$invoice->date}}</td>
                        <td data-th="Mes" class="invoice-col-month">{{$invoice->getMesInvoice()}}</td>
                        @if ($mostrarCliente)
                        <td data-th="Cliente" class="invoice-col-client">{{$invoice->name_client}}</td>
                        @endif
                        @if ($mostrarPlan)
                        <td data-th="Plan" class="invoice-col-plan">{{$invoice->name_product}}</td>
                        <td data-th="Direccion" class="invoice-col-address">{{$invoice->client_product->address}}</td>
                        @endif
                        <td data-th="Monto" class="invoice-col-amount">{{$invoice->price_formatted}}</td>
                        <td data-th="Estado" class="invoice-col-status">{!!$invoice->status_html!!}</td>
                        <td data-th="Acciones" class="invoice-col-actions">
                                <div class="invoice-action-stack">
                                @if ($invoice->payments->count() <= 0 && $invoice->status=='1')
                                    <span class="label label-primary">
                                        Factura Previa
                                    </span>
                                @elseif ($invoice->status=='1' || $invoice->payments_pending)
                                @if ($latestPayment)
                                <a href="{{ route('payments.pdf', $latestPayment->id) }}" target="_blank" class="btn btn-sm btn-danger mb-1">
                                    <i class="fas fa-file-pdf" aria-hidden="true"></i>
                                    Ver PDF
                                </a>
                                <a href="{{ route('payments.print', $latestPayment->id) }}" target="_blank" class="btn btn-sm btn-secondary mb-1">
                                    <i class="fas fa-print" aria-hidden="true"></i>
                                    Reimprimir
                                </a>
                                @endif
                                <button wire:click="$set('invoice_id',{{$invoice->id}})" type="button" class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#ModalVerPago">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                    Ver pago
                                </button>
                                @elseif ($invoice->enablePayment())
                                @livewire('report-payment-component', ['invoice' => $invoice], key($invoice->id.uniqid()))
                                <button style="padding: 5px;" wire:click="$emit('btnPaypal',{{$invoice->priceConversion('USD')}},{{$invoice->id}},'{{$invoice->client_product->address}}')" type="button" class="btn btn-sm btn-info mt-2" data-bs-toggle="modal" data-bs-target="#ModalPagarPaypal">
                                    Paga con Tarjeta
                                </button>
                                
                                @elseif (!$invoice->enablePayment())
                                 <span title="Primero se debe procesar el pago de la factura de {{$invoice->mesInvoiceInicialSinPago()}}">No Disponible para pago.</span> 
                                @endif
                                </div>
                        </td>
                    </tr>
                    <tr class="invoice-mobile-row">
                        <td colspan="8">
                            <div class="invoice-mobile-card">
                                <div class="invoice-mobile-card-header">
                                    <div>
                                        <h6 class="invoice-mobile-card-title">{{ $invoice->getMesInvoice() }}</h6>
                                        <div class="invoice-mobile-card-subtitle">Fecha: {{ $invoice->date }}</div>
                                    </div>
                                    <div>{!! $invoice->status_html !!}</div>
                                </div>

                                <div class="invoice-mobile-card-grid">
                                    @if ($mostrarCliente)
                                    <div class="invoice-mobile-card-item">
                                        <span class="invoice-mobile-card-label">Cliente</span>
                                        <span class="invoice-mobile-card-value">{{ $invoice->name_client }}</span>
                                    </div>
                                    @endif
                                    @if ($mostrarPlan)
                                    <div class="invoice-mobile-card-item">
                                        <span class="invoice-mobile-card-label">Plan</span>
                                        <span class="invoice-mobile-card-value">{{ $invoice->name_product }}</span>
                                    </div>
                                    <div class="invoice-mobile-card-item">
                                        <span class="invoice-mobile-card-label">Direccion</span>
                                        <span class="invoice-mobile-card-value">{{ $invoice->client_product->address }}</span>
                                    </div>
                                    @endif
                                    <div class="invoice-mobile-card-item">
                                        <span class="invoice-mobile-card-label">Monto</span>
                                        <span class="invoice-mobile-card-value">{{ $invoice->price_formatted }}</span>
                                    </div>
                                </div>

                                <div class="invoice-mobile-card-actions">
                                    <div class="invoice-action-stack">
                                        @if ($invoice->payments->count() <= 0 && $invoice->status=='1')
                                            <span class="label label-primary">
                                                Factura Previa
                                            </span>
                                        @elseif ($invoice->status=='1' || $invoice->payments_pending)
                                            @if ($latestPayment)
                                            <a href="{{ route('payments.pdf', $latestPayment->id) }}" target="_blank" class="btn btn-sm btn-danger mb-1">
                                                <i class="fas fa-file-pdf" aria-hidden="true"></i>
                                                Ver PDF
                                            </a>
                                            <a href="{{ route('payments.print', $latestPayment->id) }}" target="_blank" class="btn btn-sm btn-secondary mb-1">
                                                <i class="fas fa-print" aria-hidden="true"></i>
                                                Reimprimir
                                            </a>
                                            @endif
                                            <button wire:click="$set('invoice_id',{{$invoice->id}})" type="button" class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#ModalVerPago">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                                Ver pago
                                            </button>
                                        @elseif ($invoice->enablePayment())
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#ModalPagar{{$invoice->id}}">
                                                <i class="fas fa-university" aria-hidden="true"></i>
                                                Depositos
                                            </button>
                                            @if (Auth::user()->hasRole('Cobrador') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Secretaria'))
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#ModalPagarEfectivo{{$invoice->id}}">
                                                <span class="invoice-currency-icon" aria-hidden="true">Q</span>
                                                Efectivo
                                            </button>
                                            @endif
                                            <button style="padding: 5px;" wire:click="$emit('btnPaypal',{{$invoice->priceConversion('USD')}},{{$invoice->id}},'{{$invoice->client_product->address}}')" type="button" class="btn btn-sm btn-info mt-2" data-bs-toggle="modal" data-bs-target="#ModalPagarPaypal">
                                                <i class="fas fa-credit-card" aria-hidden="true"></i>
                                                Tarjeta
                                            </button>
                                        @elseif (!$invoice->enablePayment())
                                            <span title="Primero se debe procesar el pago de la factura de {{$invoice->mesInvoiceInicialSinPago()}}">No Disponible para pago.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay resultados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        {{ $list_invoices->links() }}
    </div>

    <!-- Modal VER PAGO-->
    <div wire:ignore.self class="modal fade" id="ModalVerPago" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalVerPagoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalVerPagoLabel">Detalle Pagos</h5>
            </div>
            <div class="modal-body " >
                @if ($invoice_id=='9999999')
                    <p>Cargando...</p>
                @elseif ($invoice_id>0)
                @livewire('payment-component', ['invoice_id'=>$invoice_id, 'mostrarPendientes'=>false])
                @else
                <p>Cargando...</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="$set('invoice_id',9999999)"  class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
    </div>

    <!-- Modal PAGAR-->
    <div wire:ignore.self class="modal fade" id="ModalPagarPaypal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalPagarPaypalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalPagarLabel">Reportar Pago</h5>
            </div>
            <div class="modal-body">
                <div id="paypal-button-container"></div>           
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btncerrarModalPagarPaypal" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="ModalProcesando" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalProcesandoLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalPagarLabel">Procesando...</h5>
            </div>
            <div class="modal-body">
                <h3><b>Registrando el pago y procesando la factura, por favor espere...</b></h3>          
            </div>
            </div>
        </div>
    </div>
</div>
