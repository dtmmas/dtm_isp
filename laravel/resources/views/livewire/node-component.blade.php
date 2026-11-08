<div>   
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre o código">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            @can('zones.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Crear Nodo
            </button>
            @endcan
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list_nodes as $node)
                <tr>
                    <td>{{$node->id}}</td>
                    <td>{{$node->name}}</td>
                    <td style="width: 10px">
                        <div class="btn-group " role="group">
                            @can('zones.edit')
                                <a href="#!" wire:loading.attr="disabled" wire:click="edit({{$node->id}})"  type="button" class="btn btn-sm btn-info btn-secondary" >
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
                                @can('zones.destroy')
                                <a onclick="EliminarNode({{$node->id}},'{{$node->name}}')" class="dropdown-item" href="#!">Eliminar</a>
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
    <div>
        {{ $list_nodes->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Crear Nodo</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormNode', 'method' => 'POST']) !!}
                @else
                {!! Form::model($node,['id' => 'FormNode', 'method' => 'PUT']) !!}
                @endif

                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control mayusculas', 'placeholder'=>'Nombre', 'autocomplete'=>'off']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <input type="hidden" wire:model.defer="zone_id" value="{{$zone_id}}">

                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear Nodo</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar Nodo</button>
                @endif
            </div>
            </div>
        </div>
    </div>

</div>
