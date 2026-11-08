<div>

    <div class="card">
        <div class="card-body">
            <div class="row">

                <div class="form-group col-md-6">
                    {!! Form::label('name', __('Desde') ); !!}
                    <input wire:model.defer="desde" id="desde" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
                    @error('desde') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('name', __('Hasta') ); !!}
                    <input wire:model.defer="hasta" id="hasta" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
                    @error('hasta') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group col-md-6">
                    {!! Form::label('status', __('Estado') ); !!}
                    <select wire:model.defer="status" class="form-control">
                        <option value=''>** Seleccionar **</option>
                        <option {{(('0'==$status)?'selected':'')}}  value="0">Pendiente</option>
                        <option {{(('1'==$status)?'selected':'')}}  value="1">Pagada</option>
                        <option {{(('-1'==$status)?'selected':'')}}  value="-1">Vencidas</option>
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('zone_id', __('Zona') ); !!}
                    {!! Form::select('zone_id', $zones, null, ['wire:model.defer' => 'zone_id', 'class' => 'form-control', 'placeholder' => 'Todas las zonas']) !!}
                    @error('zone_id') <span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-12">
                    <button class="btn btn-info BtnConsultar" >Consultar***</button>
                </div>
            </div>
        </div>
    </div>

    @if(count($list_invoices)>0)
    <div class="card">
        <div class="card-body">
        <button class="text-white btn btn-success btn-sm" wire:loading.attr="disabled" wire:click="exportExcel()"><i class="fas fa-file-excel"></i> Exportar Excelll</button>
        <button class="text-white btn btn-danger btn-sm" wire:loading.attr="disabled" wire:click="exportPdf()"><i class="fas fa-file-pdf"></i> Exportar Pdf</button>
            <div class="table-responsive2"id="divTable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th >Fechaa</th>
                            <th >Fecha Pago</th>
                            <th >Mes</th>
                            <th>Cliente</th>
                            <th>Plan</th>
                            <th>Direccion</th>
                            <th>Zona</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_invoices as $invoice)
                            <tr>
                                <td data-th="Fecha" >{{$invoice->created_at}}</td>
                                <td data-th="FechaPago" >{{(($invoice->status=='1')?$invoice->updated_at:"---")}}</td>
                                <td data-th="Mes" >{{$invoice->getMesInvoice()}}</td>
                                <td data-th="Cliente">{{$invoice->name_client}}</td>
                                <td data-th="Plan">{{$invoice->name_product}}</td>
                                <td data-th="Direccion">{{$invoice->client_product->address}}</td>
                                <td data-th="Zona">{{$invoice->client_product->node->zone->name}}</td>
                                <td data-th="Monto">{{$invoice->price_formatted}}</td>
                                <td data-th="Estado">{!!$invoice->status_html!!}</td>
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
                {{ $list_invoices->links() }}
            </div>
        </div>
    </div>
    @endif


    @if (count($list_invoices)<=0 )
    <div class="card">
        <div class="card-body">
            <h3>No hay resultados para la busqueda {{$desde}} - {{$hasta}}</h3>
        </div>
    </div>
    @endif

    <x-slot name="script">
        <script>
            $(document).ready(function(){


                $('.BtnConsultar').on('click', function(){
                    $('.BtnConsultar').attr('disabled','disabled').text('Consultando...')
                    let desde = $('#desde').val()
                    let hasta = $('#hasta').val()

                    if(hasta!='' && hasta<desde){
                        swal('La fecha *Hasta* debe ser mayor a *Desde*', {icon: "warning",});
                    $('.BtnConsultar').removeAttr('disabled','disabled').text('Consultar')

                    }else{
                        window.livewire.emit('onFiltrar',desde,hasta)
                    }
                })
            })
        </script>
    </x-slot>
</div>
