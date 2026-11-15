<div>
    <div class="row">
        <div class="mb-3 col-md-4">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por cliente, plan, direccion o ticket">
        </div>
        <div class="mb-3 col-md-3">
            <select wire:model="filter_status" class="form-control">
                <option value="">Todos los estados</option>
                <option value="abierto">Abierto</option>
                <option value="en_proceso">En proceso</option>
                <option value="cerrado">Cerrado</option>
            </select>
        </div>
        <div class="mb-3 col-md-2">
            <select wire:model="filter_priority" class="form-control">
                <option value="">Todas las prioridades</option>
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
        </div>
        <div class="mb-3 col-md-3">
            <select wire:model="filter_client_product_id" class="form-control">
                <option value="">Todos los planes</option>
                @foreach ($plan_options as $planId => $planLabel)
                    <option value="{{ $planId }}">{{ $planLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            @if ($client)
                <strong>Cliente:</strong> {{ $client->user->name_complete }}
            @else
                <strong>Listado general de tickets por plan</strong>
            @endif
        </div>
        <button type="button" wire:click="resetInputFields" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
            Nuevo ticket
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    @if (!$client)
                    <th>Cliente</th>
                    @endif
                    <th>Plan</th>
                    <th>Direccion</th>
                    <th>Titulo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Reportado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                        @if (!$client)
                        <td>{{ $ticket->client_name }}</td>
                        @endif
                        <td>{{ $ticket->plan_name }}</td>
                        <td>{{ $ticket->address_plan }}</td>
                        <td>
                            <strong>{{ $ticket->title }}</strong>
                            @if ($ticket->description)
                                <div class="text-muted small">{{ \Illuminate\Support\Str::limit($ticket->description, 90) }}</div>
                            @endif
                        </td>
                        <td>{!! $ticket->priority_html !!}</td>
                        <td>{!! $ticket->status_html !!}</td>
                        <td>{{ $ticket->reported_by }}</td>
                        <td>
                            <button type="button" wire:click="edit({{ $ticket->id }})" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                                Editar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $client ? 9 : 10 }}" class="text-center">No hay tickets registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $list_tickets->links() }}
    </div>

    <div wire:ignore.self class="modal fade" id="ticketModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketModalLabel">{{ $createMode ? 'Nuevo ticket' : 'Editar ticket' }}</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Plan asociado</label>
                        <select wire:model.defer="ticket_client_product_id" class="form-control">
                            <option value="">Selecciona el plan</option>
                            @foreach ($plan_options as $planId => $planLabel)
                                <option value="{{ $planId }}">{{ $planLabel }}</option>
                            @endforeach
                        </select>
                        @error('ticket_client_product_id') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Titulo</label>
                        <input wire:model.defer="title" type="text" class="form-control" placeholder="Ej. Cliente sin servicio">
                        @error('title') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label>Descripcion</label>
                        <textarea wire:model.defer="description" class="form-control" rows="4" placeholder="Describe el problema o la solicitud"></textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prioridad</label>
                                <select wire:model.defer="priority" class="form-control">
                                    <option value="alta">Alta</option>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                </select>
                                @error('priority') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado</label>
                                <select wire:model.defer="status" class="form-control">
                                    <option value="abierto">Abierto</option>
                                    <option value="en_proceso">En proceso</option>
                                    <option value="cerrado">Cerrado</option>
                                </select>
                                @error('status') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:click="save" class="btn btn-primary">
                        {{ $createMode ? 'Crear ticket' : 'Guardar cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
