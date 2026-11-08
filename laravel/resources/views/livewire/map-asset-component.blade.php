<div>
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por nombre, codigo o tipo">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            <button type="button" wire:click="resetInputFields" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#mapAssetModal">
                Nuevo activo
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Codigo</th>
                    <th>Zona / Nodo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_assets as $asset)
                    <tr>
                        <td>{{ $asset->name }}</td>
                        <td>{{ strtoupper($asset->asset_type) }}</td>
                        <td>{{ $asset->code ?: 'Sin codigo' }}</td>
                        <td>{{ optional($asset->zone)->name ?: 'Sin zona' }} / {{ optional($asset->node)->name ?: 'Sin nodo' }}</td>
                        <td>{{ ucfirst($asset->asset_status) }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" wire:click="edit({{ $asset->id }})" class="btn btn-sm btn-primary" title="Editar activo">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                                <button type="button" onclick="EliminarMapAsset({{ $asset->id }}, '{{ $asset->name }}')" class="btn btn-sm btn-danger" title="Eliminar activo">
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay activos de red registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $list_assets->links() }}</div>

    <div wire:ignore.self class="modal fade" id="mapAssetModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title titulo_pagina">{{ $createMode ? 'Crear activo de red' : 'Editar activo de red' }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre del activo']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('asset_type', 'Tipo'); !!}
                                <select wire:model.defer="asset_type" class="form-control">
                                    <option value="poste">Poste</option>
                                    <option value="nap">Caja NAP</option>
                                    <option value="olt">OLT</option>
                                    <option value="onu">ONU</option>
                                    <option value="antena">Antena/AP</option>
                                </select>
                                @error('asset_type') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('asset_status', 'Estado'); !!}
                                <select wire:model.defer="asset_status" class="form-control">
                                    <option value="activo">Activo</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                    <option value="reserva">Reserva</option>
                                    <option value="fuera_servicio">Fuera de servicio</option>
                                </select>
                                @error('asset_status') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', 'Codigo'); !!}
                        {!! Form::text('code', null, ['wire:model.defer'=> 'code', 'class' => 'form-control', 'placeholder'=>'Codigo interno']); !!}
                        @error('code') <span class="text-danger">{{ $message }}</span>@enderror
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
                                {!! Form::label('latitude', 'Latitud'); !!}
                                {!! Form::text('latitude', null, ['wire:model.defer'=> 'latitude', 'class' => 'form-control', 'placeholder'=>'15.3138000']); !!}
                                @error('latitude') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('longitude', 'Longitud'); !!}
                                {!! Form::text('longitude', null, ['wire:model.defer'=> 'longitude', 'class' => 'form-control', 'placeholder'=>'-91.4784000']); !!}
                                @error('longitude') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('description', 'Descripcion'); !!}
                        {!! Form::textarea('description', null, ['wire:model.defer'=> 'description', 'class' => 'form-control', 'rows' => 3, 'placeholder'=>'Detalle del activo']); !!}
                        @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        {!! Form::label('meta_json', 'Datos extra en JSON'); !!}
                        {!! Form::textarea('meta_json', null, ['wire:model.defer'=> 'meta_json', 'class' => 'form-control', 'rows' => 4, 'placeholder'=>'{\"modelo\":\"Rocket Prism\",\"sector\":\"120 grados\"}']); !!}
                        @error('meta_json') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    @if ($createMode)
                        <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear activo</button>
                    @else
                        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Guardar cambios</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
