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
                    {!! Form::label('status', __('Estado') ); !!}
                    <select wire:model.defer="status" class="form-control">
                        <option value=''>** Seleccionar **</option>
                        <option {{(('0'==$status)?'selected':'')}}  value="0">Moroso</option>
                        <option {{(('1'==$status)?'selected':'')}}  value="1">Activo</option>
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-12">
                    <button class="btn btn-info BtnConsultar" >Consultar</button>
                </div>
            </div>
        </div>
    </div>

    @if(count($clients_by_zone) > 0)
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Clientes por zona</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Total de clientes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clients_by_zone as $zone)
                            <tr>
                                <td>{{ $zone->name }}</td>
                                <td>{{ $zone->total_clientes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(count($list_clients)>0)
    <div class="card">
        <div class="card-body">
        <button class="btn btn-success btn-sm text-white" wire:loading.attr="disabled" wire:click="exportExcel()"><i class="fas fa-file-excel"></i> Exportar Excel</button>
        <button class="btn btn-danger btn-sm text-white" wire:loading.attr="disabled" wire:click="exportPdf()"><i class="fas fa-file-pdf"></i> Exportar Pdf</button>
            <div class="table-responsive2 " id="divTable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>DPI</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Estado</th>
                            <th>Facturas Vencidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_clients as $client)
                        <?php $vencidas = $client->invoicesVencidas();
                                $activo = ($vencidas>0)?false:true; ?>
                            <tr>
                               <td data-th="Fecha" >{{$client->created_at}}</td>
                               <td data-th="Nombre" >{{$client->user->name}}</td>
                                <td data-th="Apellido">{{$client->user->lastname}}</td>
                                <td data-th="DPI">{{$client->user->dni}}</td>
                                <td data-th="Correo">{{$client->user->email}}</td>
                                <td data-th="Telefono">{{$client->user->phone}}</td>
                                <td data-th="Estado">{!! $client->HtmlEstatus($activo,'html') !!}</td>
                                <td data-th="Facturas">{{ $vencidas }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay resultados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                {{ $list_clients->links() }}
            </div>
        </div>
    </div>
    @endif 

    
    @if (count($list_clients)<=0 )
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
