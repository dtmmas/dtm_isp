<div>   
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por nombre">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            @can('categories.create')
            <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                Crear Categoria
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
            @forelse ($list_categories as $category)
                <tr>
                    <td>{{$category->name}}</td>
                    <td>{{$category->description}}</td>
                    <td style="width: 10px">
                        <div class="btn-group " role="group">
                            @can('categories.edit')
                                <a href="#!" wire:loading.attr="disabled" wire:click="edit({{$category->id}})"  type="button" class="btn btn-sm btn-info btn-secondary" >
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
                                @can('categories.index')
                                <a href="{{ route('subcategories.index', $category->id) }}" class="dropdown-item" href="#!">Subcategorias</a>
                                @endcan
                                @can('categories.destroy')
                                <a onclick="EliminarCategory({{$category->id}},'{{$category->name}}')" class="dropdown-item" href="#!">Eliminar</a>
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
        {{ $list_categories->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Crear Categoria</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormCategory', 'method' => 'POST']) !!}
                @else
                {!! Form::model($category,['id' => 'FormCategory', 'method' => 'PUT']) !!}
                @endif

                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre', 'autocomplete'=>'off']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        {!! Form::label('description', 'Descripción'); !!}
                        {!! Form::textarea('description', null,['wire:model.defer'=> 'description','class' => 'form-control', 'placeholder'=>'Descripcion']) !!}
                    </div> 
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear Categoria</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar Categoria</button>
                @endif
            </div>
            </div>
        </div>
    </div>

</div>
