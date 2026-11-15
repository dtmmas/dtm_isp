<div>
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por nombre o tipo de ruta">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            <button type="button" wire:click="resetInputFields" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#mapFiberModal">
                Nueva ruta
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Zona / Nodo</th>
                    <th>Color</th>
                    <th>Grosor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_routes as $route)
                    <tr>
                        <td>{{ $route->name }}</td>
                        <td>{{ strtoupper($route->route_type) }}</td>
                        <td>{{ optional($route->zone)->name ?: 'Sin zona' }} / {{ optional($route->node)->name ?: 'Sin nodo' }}</td>
                        <td><span style="display:inline-block;width:20px;height:20px;border-radius:4px;background:{{ $route->stroke_color ?: '#0ea5e9' }}"></span></td>
                        <td>{{ $route->stroke_weight }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" wire:click="edit({{ $route->id }})" class="btn btn-sm btn-primary" title="Editar ruta">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                                <button type="button" onclick="EliminarMapFiber({{ $route->id }}, '{{ $route->name }}')" class="btn btn-sm btn-danger" title="Eliminar ruta">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay rutas de fibra registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $list_routes->links() }}</div>

    <div wire:ignore.self class="modal fade" id="mapFiberModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title titulo_pagina">{{ $createMode ? 'Crear ruta de fibra' : 'Editar ruta de fibra' }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre de la ruta']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('route_type', 'Tipo'); !!}
                                <select wire:model.defer="route_type" class="form-control">
                                    <option value="fibra">Fibra</option>
                                    <option value="backbone">Backbone</option>
                                    <option value="drop">Drop</option>
                                </select>
                                @error('route_type') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('stroke_weight', 'Grosor'); !!}
                                {!! Form::number('stroke_weight', null, ['wire:model.defer'=> 'stroke_weight', 'class' => 'form-control', 'min' => 1, 'max' => 12]); !!}
                                @error('stroke_weight') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('stroke_color', 'Color'); !!}
                        {!! Form::text('stroke_color', null, ['wire:model.defer'=> 'stroke_color', 'class' => 'form-control', 'placeholder'=>'#0ea5e9']); !!}
                        @error('stroke_color') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('zone_id', 'Zona'); !!}
                                {!! Form::select('zone_id', $zones, null, ['wire:model.defer' => 'zone_id', 'class' => 'form-control', 'placeholder' => 'Sin zona']) !!}
                                @error('zone_id') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('node_id', 'Nodo'); !!}
                                {!! Form::select('node_id', $nodes, null, ['wire:model.defer' => 'node_id', 'class' => 'form-control', 'placeholder' => 'Sin nodo']) !!}
                                @error('node_id') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('path_points_json', 'Puntos de la ruta en JSON'); !!}
                        {!! Form::textarea('path_points_json', null, ['wire:model.defer'=> 'path_points_json', 'class' => 'form-control', 'rows' => 6, 'placeholder'=>'[{\"lat\":15.3132,\"lng\":-91.4779},{\"lat\":15.3144,\"lng\":-91.4789}]']); !!}
                        @error('path_points_json') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        {!! Form::label('description', 'Descripcion'); !!}
                        {!! Form::textarea('description', null, ['wire:model.defer'=> 'description', 'class' => 'form-control', 'rows' => 3, 'placeholder'=>'Detalle de la ruta']); !!}
                        @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    @if ($createMode)
                        <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear ruta</button>
                    @else
                        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Guardar cambios</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
