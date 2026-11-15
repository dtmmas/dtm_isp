<div>   
    <div class="row">
        <div class=" mb-3 col-xs-12 col-sm-8">
            <div class="input-group">
                <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre o código">
                <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
            </div>
        </div>
        <div class=" mb-3 col-xs-12 col-sm-4">
            @can('zones.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Crear Zona
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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($list_zones as $zone)
                
                    <tr>
                        <td>{{$zone->id}}</td>
                        <td>{{$zone->name}}</td>
                        <td style="width: 10px">
                            <div class="btn-group " role="group">
                                @can('zones.edit')
                                    <a href="#!" wire:loading.attr="disabled" wire:click="edit({{$zone->id}})"  type="button" class="btn btn-sm btn-info btn-secondary" >
                                        Editar
                                    </a>
                                @else
                                    <a href="#!" type="button" class="btn btn-sm btn-info btn-secondary">
                                        Acciones
                                    </a>
                                @endcan
                                <button style="opacity: .8" type="button" class="btn btn-info btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                                    @can('zones.index')
                                    <a href="{{ route('zones.nodes', $zone->id) }}" class="dropdown-item" href="#!">Nodos</a>
                                    @endcan
                                    @can('zones.destroy')
                                    <a onclick="EliminarZone({{$zone->id}},'{{$zone->name}}')" class="dropdown-item" href="#!">Eliminar</a>
                                    @endcan
                                </div>
                            </div>
                        </td>
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
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Crear Zona</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormZone', 'method' => 'POST']) !!}
                @else
                {!! Form::model($zone,['id' => 'FormZone', 'method' => 'PUT']) !!}
                @endif
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control mayusculas', 'placeholder'=>'Nombre', 'autocomplete'=>'off']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear Zona</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar Zona</button>
                @endif
            </div>
            </div>
        </div>
    </div>

</div>
