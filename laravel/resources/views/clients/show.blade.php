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
    </style>
    @include('yield.resume-user')
    <h3>Planes Contratados</h3>
    <div  class="table-responsive">
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
                    <tr>
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
            function printPaymentReceipts(paymentId) {
                if (!paymentId) {
                    return;
                }

                if (window.DTMPrinter) {
                        return window.DTMPrinter.printPaymentReceipt(paymentId, {
                            allowBrowserFallback: false,
                        }).catch(function () {
                            swal('No fue posible reimprimir sin selector. Verifica que QZ Tray este activo en esta estacion.', {icon: 'warning'});
                        });
                }
            }

            function promptPaymentReceiptReprint(paymentId) {
                if (!paymentId) {
                    return;
                }

                setTimeout(function () {
                    swal({
                        title: "Ticket impreso",
                        text: "¿Desea reimprimir el ticket?",
                        icon: "info",
                        buttons: {
                            cancel: "No",
                            confirm: {
                                text: "Si, reimprimir",
                                value: true,
                                visible: true
                            },
                        },
                    }).then((shouldReprint) => {
                        if (shouldReprint) {
                            printPaymentReceipts(paymentId);
                        }
                    });
                }, 1200);
            }

            window.livewire.on('paymentUpdateStore', (message, id, paymentId) => {
                $('#ModalPagar'+id).modal('hide');
                $('#ModalPagarEfectivo'+id).modal('hide');
                swal(message, {icon: "success", buttons: false, timer: 1200});
                printPaymentReceipts(paymentId);
                promptPaymentReceiptReprint(paymentId);
            });
            
        </script>
    </x-slot>
</x-app-layout>
