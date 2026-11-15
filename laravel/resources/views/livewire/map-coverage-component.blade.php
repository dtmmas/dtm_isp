<div>
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por nombre o tecnologia">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            <button type="button" wire:click="resetInputFields" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#mapCoverageModal">
                Nueva cobertura
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Tecnologia</th>
                    <th>Zona / Nodo</th>
                    <th>Centro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_coverages as $coverage)
                    <tr>
                        <td>{{ $coverage->name }}</td>
                        <td>{{ strtoupper($coverage->coverage_type) }}</td>
                        <td>{{ $coverage->technology ?: 'Sin tecnologia' }}</td>
                        <td>{{ optional($coverage->zone)->name ?: 'Sin zona' }} / {{ optional($coverage->node)->name ?: 'Sin nodo' }}</td>
                        <td>{{ $coverage->center_latitude }}, {{ $coverage->center_longitude }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" wire:click="edit({{ $coverage->id }})" class="btn btn-sm btn-primary" title="Editar cobertura">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                                <button type="button" onclick="EliminarMapCoverage({{ $coverage->id }}, '{{ $coverage->name }}')" class="btn btn-sm btn-danger" title="Eliminar cobertura">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay coberturas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $list_coverages->links() }}</div>

    <div wire:ignore.self class="modal fade" id="mapCoverageModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title titulo_pagina">{{ $createMode ? 'Crear cobertura' : 'Editar cobertura' }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre de la cobertura']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('coverage_type', 'Tipo'); !!}
                                <select wire:model.defer="coverage_type" class="form-control">
                                    <option value="circle">Circulo</option>
                                    <option value="polygon">Poligono</option>
                                </select>
                                @error('coverage_type') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('technology', 'Tecnologia'); !!}
                                {!! Form::text('technology', null, ['wire:model.defer'=> 'technology', 'class' => 'form-control', 'placeholder'=>'Wireless 5 GHz, GPON, etc.']); !!}
                                @error('technology') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('center_latitude', 'Latitud centro'); !!}
                                {!! Form::text('center_latitude', null, ['wire:model.defer'=> 'center_latitude', 'class' => 'form-control', 'placeholder'=>'15.3138000']); !!}
                                @error('center_latitude') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('center_longitude', 'Longitud centro'); !!}
                                {!! Form::text('center_longitude', null, ['wire:model.defer'=> 'center_longitude', 'class' => 'form-control', 'placeholder'=>'-91.4784000']); !!}
                                @error('center_longitude') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('radius_meters', 'Radio en metros'); !!}
                        {!! Form::number('radius_meters', null, ['wire:model.defer'=> 'radius_meters', 'class' => 'form-control', 'min' => 1]); !!}
                        @error('radius_meters') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        {!! Form::label('polygon_points_json', 'Puntos del poligono en JSON'); !!}
                        {!! Form::textarea('polygon_points_json', null, ['wire:model.defer'=> 'polygon_points_json', 'class' => 'form-control', 'rows' => 6, 'placeholder'=>'[{\"lat\":15.3132,\"lng\":-91.4779},{\"lat\":15.3144,\"lng\":-91.4789}]']); !!}
                        @error('polygon_points_json') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('fill_color', 'Color relleno'); !!}
                                {!! Form::text('fill_color', null, ['wire:model.defer'=> 'fill_color', 'class' => 'form-control', 'placeholder'=>'#22c55e']); !!}
                                @error('fill_color') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('stroke_color', 'Color borde'); !!}
                                {!! Form::text('stroke_color', null, ['wire:model.defer'=> 'stroke_color', 'class' => 'form-control', 'placeholder'=>'#15803d']); !!}
                                @error('stroke_color') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('description', 'Descripcion'); !!}
                        {!! Form::textarea('description', null, ['wire:model.defer'=> 'description', 'class' => 'form-control', 'rows' => 3, 'placeholder'=>'Detalle de la cobertura']); !!}
                        @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    @if ($createMode)
                        <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear cobertura</button>
                    @else
                        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Guardar cambios</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
