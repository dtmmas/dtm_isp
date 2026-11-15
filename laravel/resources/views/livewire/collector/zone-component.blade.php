<div>   
    <div class="row">
        <div class=" mb-3 col-xs-12 col-sm-8">
            <div class="input-group">
                <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre">
                <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class=" mb-3 col-xs-12 col-sm-4">
            @can('collectors.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Asociar Zona
            </button>
            @endcan
        </div>
    </div>
    <div  class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    @can('collectors.edit')
                        <th>Acciones</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @foreach ($list_zones as $zone)
                
                    <tr>
                        <td>{{$zone->id}}</td>
                        <td>{{$zone->name}}</td>
                        
                        @can('collectors.edit')
                        <td style="width: 10px">
                            <a onclick="EliminarZone({{$zone->id}},'{{$zone->name}}')" class="bnt btn-xs btn-danger" href="#!">Quitar</a>
                        </td>
                        @endcan
                        
                    </tr>
                {{--  @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay resultados</td>
                    </tr>  --}}
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div>
        {{ $list_zones->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Asociar Zona</h5>
            </div>
            <div class="modal-body">
                {!! Form::open(['id' => 'FormZone', 'method' => 'PUT']) !!}
                <div class="form-group">
                    {!! Form::label('zone_id', 'Seleccione la zona a asociar'); !!}
                    {!! Form::select('zone_id', $zones, null, ['wire:model.defer'=>'zone_id','class' => 'form-control','placeholder' => 'Selecciona una zona']) !!}
                </div>
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Asociar Zona</button>
            </div>
            </div>
        </div>
    </div>

</div>
