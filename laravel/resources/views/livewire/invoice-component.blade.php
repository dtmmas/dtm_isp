<div>
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
        <table style="width: 100%" class="table table-striped">
            <thead>
                <tr>
                    <th >Fecha</th>
                    <th >Mes</th>
                    @if ($mostrarCliente)
                    <th>Cliente</th>
                    @endif
                    @if ($mostrarPlan)
                    <th>Plan</th>
                    <th>Direccion</th>
                    @endif
                    <th>Monto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_invoices as $invoice)
                    <tr>
                        <td data-th="Fecha" >{{$invoice->date}}</td>
                        <td data-th="Mes" >{{$invoice->getMesInvoice()}}</td>
                        @if ($mostrarCliente)
                        <td data-th="Cliente">{{$invoice->name_client}}</td>
                        @endif
                        @if ($mostrarPlan)
                        <td data-th="Plan">{{$invoice->name_product}}</td>
                        <td data-th="Direccion">{{$invoice->client_product->address}}</td>
                        @endif
                        <td data-th="Monto">{{$invoice->price_formatted}}</td>
                        <td data-th="Estado">{!!$invoice->status_html!!}</td>
                        <td data-th="Acciones">
                                @if ($invoice->payments()->count() <= 0 && $invoice->status=='1')
                                    <span class="label label-primary">
                                        Factura Previa
                                    </span>
                                @elseif ($invoice->status=='1' || $invoice->payments_pending)
                                <button wire:click="$set('invoice_id',{{$invoice->id}})" type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ModalVerPago">
                                    Ver pago
                                </button>
                                @elseif ($invoice->enablePayment())
                                @livewire('report-payment-component', ['invoice' => $invoice], key($invoice->id.uniqid()))
                                <button style="    padding: 5px;" wire:click="$emit('btnPaypal',{{$invoice->priceConversion('USD')}},{{$invoice->id}},'{{$invoice->client_product->address}}')" type="button" class="btn btn-sm btn-info mt-2" data-bs-toggle="modal" data-bs-target="#ModalPagarPaypal">
                                    Paga con Tarjeta
                                </button>
                                
                                @elseif (!$invoice->enablePayment())
                                 <span title="Primero se debe procesar el pago de la factura de {{$invoice->mesInvoiceInicialSinPago()}}">No Disponible para pago.</span> 
                                @endif
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
