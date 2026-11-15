<div>
    <div class="row mb-3">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por nombre del tipo">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
        <div class="col">
            <button type="button" wire:click="resetInputFields" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#ticketTypeCrudModal">
                Nuevo tipo
            </button>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tickets asociados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($list_ticket_types as $ticketType)
                <tr>
                    <td>{{ $ticketType->name }}</td>
                    <td>{{ $ticketType->tickets_count }}</td>
                    <td style="width: 10px">
                        <div class="d-flex gap-1">
                            <button type="button" wire:loading.attr="disabled" wire:click="edit({{ $ticketType->id }})" class="btn btn-sm btn-primary" title="Editar tipo">
                                <i class="fas fa-edit" aria-hidden="true"></i>
                            </button>
                            <button type="button" onclick="EliminarTicketType({{ $ticketType->id }}, '{{ $ticketType->name }}')" class="btn btn-sm btn-danger" title="Eliminar tipo">
                                <i class="fas fa-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No hay tipos de ticket registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $list_ticket_types->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="ticketTypeCrudModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ticketTypeCrudModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title titulo_pagina" id="ticketTypeCrudModalLabel">{{ $createMode ? 'Crear tipo de ticket' : 'Editar tipo de ticket' }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre'); !!}
                        {!! Form::text('name', null, ['wire:model.defer'=> 'name', 'class' => 'form-control', 'placeholder'=>'Nombre del tipo de ticket', 'autocomplete'=>'off']); !!}
                        @error('name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    @if ($createMode)
                        <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear tipo</button>
                    @else
                        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Guardar cambios</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
