<div>   
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            @can('categories.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Crear Subcategoria
            </button>
            @endcan
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list_subcategories as $subcategory)
                <tr>
                    <td>{{$subcategory->name}}</td>
                    <td>{{$subcategory->description}}</td>
                    <td style="width: 10px">
                        <div class="btn-group " role="group">
                            @can('categories.edit')
                                <a href="#!" wire:loading.attr="disabled" wire:click="edit({{$subcategory->id}})"  type="button" class="btn btn-sm btn-info btn-secondary" >
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
                                @can('categories.destroy')
                                <a onclick="EliminarSubcategory({{$subcategory->id}},'{{$subcategory->name}}')" class="dropdown-item" href="#!">Eliminar</a>
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
        {{ $list_subcategories->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Crear Subcategoria</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormSubcategory', 'method' => 'POST']) !!}
                @else
                {!! Form::model($subcategory,['id' => 'FormSubcategory', 'method' => 'PUT']) !!}
                @endif

                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre', 'autocomplete'=>'off']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <input type="hidden" wire:model.defer="category_id" value="{{$category_id}}"></div>

                    <div class="form-group">
                        {!! Form::label('description', 'Descripción'); !!}
                        {!! Form::textarea('description', null,['wire:model.defer'=> 'description','class' => 'form-control', 'placeholder'=>'Descripcion']) !!}
                    </div> 
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear subcategoria</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar subcategoria</button>
                @endif
            </div>
            </div>
        </div>
    </div>

</div>
