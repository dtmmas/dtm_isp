<div>   
    <div class="row">
        <div class=" mb-3 col-xs-12 col-sm-8">
            <div class="input-group col">
                <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre o DPI">
                <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
            </div>
        </div>
        
        <div class="mb-3 col-xs-12 col-sm-4">
            @can('collectors.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Crear Cobrador
            </button>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th >Nombre</th>
                    <th>Apellido</th>
                    <th>DPI</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th >Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_collectors as $collector)
                    <tr>
                        <td data-th="Nombre">{{$collector->name}}</td>
                        <td data-th="Apellido">{{$collector->lastname}}</td>
                        <td data-th="DPI">{{$collector->dni}}</td>
                        <td data-th="Correo">{{$collector->email}}</td>
                        <td data-th="Telefono">{{$collector->phone}}</td>
                        <td data-th="Acciones"  style="width: 10px">
                            <div class="btn-group " zone="group">
                                <a href="{{ route('collectors.show', $collector) }}"  type="button" class="btn btn-sm btn-info btn-secondary" >
                                    Ver
                                </a>
                                <button style="opacity: .8" type="button" class="btn btn-info btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                                    @can('collectors.edit')
                                    <a wire:click="edit({{$collector->id}})" class="dropdown-item" href="#!">Editar</a>
                                    @endcan
                                    @can('collectors.edit')
                                    <a href="{{ route('collectors.zones', $collector) }}" class="dropdown-item">Zonas</a>
                                    @endcan
                                    @can('collectors.destroy')
                                    <a onclick="EliminarCollector({{$collector->id}},'{{$collector->name_complete}}')" class="dropdown-item" href="#!">Eliminar</a>
                                    @endcan
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
        {{ $list_collectors->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ (($createMode)?'Crear Cobrador':'Editar Cobrador')}}</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormCollector', 'method' => 'POST']) !!}
                @else
                {!! Form::model($collector,['id' => 'FormCollector', 'method' => 'PUT']) !!}
                @endif
                    @include('yield.input-user')
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear Cobrador</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar Cobrador</button>
                @endif
            </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="zoneModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zoneModalLabel">Actualizar rol usuario</h5>
            </div>
            <div class="modal-body">
                
                @if($editZoneMode)

                {!! Form::model($collector,['id' => 'FormCollector', 'method' => 'PUT']) !!}

                <h4>Listado de zones</h4>
                @forelse ($zonesAll  as $index => $rol)
                    <li class="list-group-item ">
                        <div class="custom-control custom-checkbox ">
                            {!! Form::checkbox('zones[]', $rol->id, null, ['wire:model.defer'=>'zonesCollector.'.$rol->id,'class'=>'custom-control-input','id'=>'zone'.$rol->id]) !!}
                            {!! Form::label('zone'.$rol->id, $rol->name.': '.$rol->description, ['class'=>'custom-control-label ']) !!}
                        </div>
                    </li>
                @empty
                @endforelse
                {!! Form::close() !!}
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:loading.attr="disabled" wire:click="updateZone()" class="btn btn-primary">Actualizar</button>
            </div>
            </div>
        </div>
    </div>
</div>
