<div>     
    <div class="card">
        <div class="card-body">
            <div class="row">
            
                <div class="form-group col-md-6">
                    {!! Form::label('name', __('Desde') ); !!}
                    <input wire:model.defer="desde" id="desde" type="date" data-date-format="yyyy-mm-dd" class="form-control " autocomplete="off" >
                    @error('desde') <span class="text-danger">{{ $message }}</span>@enderror
                </div>
                
                <div class="form-group col-md-6">
                    {!! Form::label('name', __('Hasta') ); !!}
                    <input wire:model.defer="hasta" id="hasta" type="date" data-date-format="yyyy-mm-dd" class="form-control " autocomplete="off" >
                    @error('hasta') <span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('zone_id', 'Seleccione la zona'); !!}
                    {!! Form::select('zone_id', $zones, null, ['wire:model'=>'zone_id','class' => 'form-control','placeholder' => 'Selecciona una zona']) !!}
                </div>
                <div class="col-md-12">
                    <button class="btn btn-info BtnConsultar" >Consultar</button>
                </div>
            </div>
        </div>
    </div>

    @if(count($list_client_products)>0)
    <div class="card">
        <div class="card-body">
        <button class="btn btn-success btn-sm text-white" wire:loading.attr="disabled" wire:click="exportExcel()"><i class="fas fa-file-excel"></i> Exportar Excel</button>
        <button class="btn btn-danger btn-sm text-white" wire:loading.attr="disabled" wire:click="exportPdf()"><i class="fas fa-file-pdf"></i> Exportar Pdf</button>
            <div class="table-responsive2 " id="divTable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>DPI</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Zona</th>
                            <th>Referencia del plan</th>
                            <th>MESES VENCIDOS</th>
                            <th>MESES</th>
                            <th>Cuota</th>
                            <th>Total deuda</th>
                            <th>Recordatorio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_client_products as $client_product)
                        <?php 
                            $total_meses = $client_product->invoices->count();
                            $invoicesByMonth = $client_product->invoices->groupBy(function ($invoice) {
                                return $invoice->getMesInvoice();
                            });
                            $clientName = $client_product->client->user->name;
                            $monthList = $invoicesByMonth->keys()->implode(' - ');
                            $total_deuda =$client_product->invoices->sum('price');
                            $cellphone = $client_product->client->user->phone;
                        ?>
                            <tr>
                               <td data-th="Fecha" >{{$client_product->invoices->first()->created_at}}</td>
                               <td data-th="Cliente" >{{$client_product->client->user->name_complete}}</td>
                                <td data-th="DPI">{{$client_product->client->user->dni}}</td>
                                <td data-th="Correo">{{$client_product->client->user->email}}</td>
                                <td data-th="Telefono">{{$cellphone}}</td>
                                <td data-th="Zona">{{$client_product->node->zone->name}}</td>
                                <td data-th="Referencia">{{$client_product->reference}}</td>
                                <td data-th="Meses">{{$total_meses}}</td>
                                <td data-th="Meses">{{$monthList}}</td>
                                <td data-th="Cuota">{{$client_product->product->price}}</td>
                                <td data-th="deuda">{{$total_deuda}}</td>
                                <td data-th="recordatorio">
                                <button onclick="EnviarRecordatorioMoroso(@js($clientName), @js($monthList), {{$total_deuda}}, {{$cellphone}})" class="btn btn-warning">Enviar WhatsApp</button>
                            </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay resultados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                {{ $list_client_products->links() }}
            </div>
        </div>
    </div>
    @endif 

    
    @if (count($list_client_products)<=0 )
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
