<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Cliente: '.$user->name_complete) }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            <li>
                <a class="btn btn-sm btn-primary rounded-pill px-3" href="{{ route('clients.tickets', $client) }}">
                    <i class="fas fa-ticket-alt me-1" aria-hidden="true"></i> Tickets del cliente
                </a>
            </li>
        </ol>
    </x-slot>
    <style>
        .client-plan-table-shell {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .client-plan-table-shell .table {
            margin-bottom: 0;
        }

        .client-plan-table-shell thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .client-plan-table-shell tbody td {
            vertical-align: middle;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .client-plan-action-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .client-plan-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.45rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .client-plan-action-btn-details {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        .client-plan-action-btn-details:hover {
            background: #bae6fd;
            color: #075985;
        }

        .client-plan-action-btn-tickets {
            background: #ede9fe;
            border-color: #ddd6fe;
            color: #6d28d9;
        }

        .client-plan-action-btn-tickets:hover {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .client-plan-mobile-row {
            display: none;
        }

        .client-plan-mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
            padding: 0.95rem 1rem;
        }

        .client-plan-mobile-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.7rem;
        }

        .client-plan-mobile-title {
            margin: 0;
            font-size: 0.96rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .client-plan-mobile-subtitle {
            font-size: 0.72rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        .client-plan-mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem 0.8rem;
            margin-bottom: 0.75rem;
        }

        .client-plan-mobile-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.1rem;
        }

        .client-plan-mobile-value {
            display: block;
            font-size: 0.83rem;
            color: #0f172a;
            line-height: 1.28;
            word-break: break-word;
        }

        .client-plan-mobile-actions {
            border-top: 1px solid #e2e8f0;
            padding-top: 0.75rem;
        }

        @media (max-width: 767.98px) {
            .client-plan-desktop-row {
                display: none;
            }

            .client-plan-mobile-row {
                display: table-row;
            }

            .client-plan-mobile-row td {
                padding: 0.45rem 0 !important;
                border-top: none !important;
            }

            .client-plan-mobile-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
    @include('yield.resume-user')
    <h3>Planes Contratados</h3>
    <div class="table-responsive client-plan-table-shell">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Dirección</th>
                    <th>Referencia</th>
                    <th>Zona/Nodo</th>
                    <th>Fecha de Instalacion</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_products as $product)
                    <tr class="client-plan-desktop-row">
                        <td>{{$product->name}}</td>
                        <td>{{$product->pivot->address}}</td>
                         <td>{{$product->pivot->reference}}</td>
                        <td>{{$product->pivot->node->zone->name.'/'.$product->pivot->node->name}}</td>
                        <td>{{$product->pivot->date_instalation}}</td>
                        <td>{!! $product->pivot->status_html!!}</td> 
                        <td style="width: 10px">
                            <div class="client-plan-action-group">
                                <a class="client-plan-action-btn client-plan-action-btn-details" href="{{ url('clients/'.$client->id.'/products?modeResume=true&cp='.$product->pivot->id) }}">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                    <span>Detalles</span>
                                </a>
                                <a class="client-plan-action-btn client-plan-action-btn-tickets" href="{{ route('clients.tickets', ['client' => $client->id, 'client_product_id' => $product->pivot->id]) }}">
                                    <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                                    <span>Tickets</span>
                                </a>
                            </div>
                        </td>                          
                    </tr>
                    <tr class="client-plan-mobile-row">
                        <td colspan="7">
                            <div class="client-plan-mobile-card">
                                <div class="client-plan-mobile-header">
                                    <div>
                                        <h6 class="client-plan-mobile-title">{{ $product->name }}</h6>
                                        <div class="client-plan-mobile-subtitle">Referencia: {{ $product->pivot->reference }}</div>
                                    </div>
                                    <div>{!! $product->pivot->status_html !!}</div>
                                </div>

                                <div class="client-plan-mobile-grid">
                                    <div>
                                        <span class="client-plan-mobile-label">Direccion</span>
                                        <span class="client-plan-mobile-value">{{ $product->pivot->address }}</span>
                                    </div>
                                    <div>
                                        <span class="client-plan-mobile-label">Zona / Nodo</span>
                                        <span class="client-plan-mobile-value">{{ $product->pivot->node->zone->name.'/'.$product->pivot->node->name }}</span>
                                    </div>
                                    <div>
                                        <span class="client-plan-mobile-label">Fecha instalacion</span>
                                        <span class="client-plan-mobile-value">{{ $product->pivot->date_instalation }}</span>
                                    </div>
                                </div>

                                <div class="client-plan-mobile-actions">
                                    <div class="client-plan-action-group">
                                        <a class="client-plan-action-btn client-plan-action-btn-details" href="{{ url('clients/'.$client->id.'/products?modeResume=true&cp='.$product->pivot->id) }}">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                            <span>Detalles</span>
                                        </a>
                                        <a class="client-plan-action-btn client-plan-action-btn-tickets" href="{{ route('clients.tickets', ['client' => $client->id, 'client_product_id' => $product->pivot->id]) }}">
                                            <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                                            <span>Tickets</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay resultados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div>
        {{ $list_products->links() }}
    </div>
    
    <h3>Facturación Mensual</h3>
    @livewire('invoice-component', ['pivot'=>null,'client'=>$client])

    <x-slot name="script">
        <script type="text/javascript">
            function printPaymentReceipts(paymentId, options) {
                if (!paymentId) {
                    return Promise.resolve(null);
                }

                if (window.DTMPrinter && typeof window.DTMPrinter.printViaBrowser === 'function') {
                    return window.DTMPrinter.printViaBrowser(paymentId).catch(function () {
                        swal('No fue posible abrir la impresion del recibo. Verifica que el navegador permita ventanas emergentes.', {icon: 'warning'});
                        return null;
                    });
                }

                return Promise.resolve(null);
            }

            window.livewire.on('paymentUpdateStore', (message, id, paymentId) => {
                $('#ModalPagar'+id).modal('hide');
                $('#ModalPagarEfectivo'+id).modal('hide');
                swal(message, {icon: "success", buttons: false, timer: 1200});
                printPaymentReceipts(paymentId);
            });
            
        </script>
    </x-slot>
</x-app-layout>
