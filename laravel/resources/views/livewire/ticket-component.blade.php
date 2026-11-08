<div>
    <div class="row">
        <div class="mb-3 col-md-3">
            <input wire:model="search" type="text" class="form-control" placeholder="Buscar por cliente, tipo, telefono, referencia o plan">
        </div>
        <div class="mb-3 col-md-2">
            <select wire:model="filter_status" class="form-control">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="resuelto">Resuelto</option>
                <option value="cancelado">Cancelado</option>
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
        <div class="mb-3 col-md-2">
            <select wire:model="filter_ticket_type_id" class="form-control">
                <option value="">Todos los tipos</option>
                @foreach ($ticket_type_options as $ticketTypeId => $ticketTypeName)
                    <option value="{{ $ticketTypeId }}">{{ $ticketTypeName }}</option>
                @endforeach
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
                    <th>Tipo de ticket</th>
                    <th>Descripcion</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Cambiar estado</th>
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
                        <td>{{ $ticket->type_name ?: 'Sin tipo' }}</td>
                        <td>{{ $ticket->description ? \Illuminate\Support\Str::limit($ticket->description, 90) : 'Sin descripcion' }}</td>
                        <td>{!! $ticket->priority_html !!}</td>
                        <td>
                            <button
                                type="button"
                                wire:click="showStatusInfo({{ $ticket->id }})"
                                class="btn btn-link p-0 text-decoration-none text-start"
                            >
                                {!! $ticket->status_html !!}
                            </button>
                        </td>
                        <td>
                            @if (Auth::user()->hasRole('Tecnico') && $ticket->status === 'pendiente')
                                <select class="form-control form-control-sm" onchange="handleTicketStatusAction(this, {{ $ticket->id }})">
                                    <option value="">Seleccionar</option>
                                    <option value="resuelto">Resuelto</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            @else
                                <span class="text-muted small">Sin acciones</span>
                            @endif
                        </td>
                        <td>{{ $ticket->reported_by }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" wire:click="edit({{ $ticket->id }})" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $client ? 11 : 12 }}" class="text-center">No hay tickets registrados.</td>
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
                        <label>Tipo de ticket</label>
                        <div class="d-flex align-items-start gap-2">
                            <select wire:model.defer="ticket_type_id" class="form-control">
                                <option value="">Selecciona el tipo</option>
                                @foreach ($ticket_type_options as $ticketTypeId => $ticketTypeName)
                                    <option value="{{ $ticketTypeId }}">{{ $ticketTypeName }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="prepareTicketTypeCreate" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ticketTypeModal" title="Nuevo tipo de ticket">
                                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('ticket_type_id') <span class="text-danger d-block">{{ $message }}</span>@enderror
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
                                <label>{{ $createMode ? 'Estado inicial' : 'Estado actual' }}</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $createMode ? 'Pendiente' : ($status === 'cancelado' ? 'Cancelado' : ($status === 'resuelto' ? 'Resuelto' : 'Pendiente')) }}"
                                    readonly
                                >
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

    <div wire:ignore.self class="modal fade" id="ticketTypeModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ticketTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketTypeModalLabel">Nuevo tipo de ticket</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del tipo</label>
                        <input wire:model.defer="new_ticket_type_name" type="text" class="form-control" placeholder="Ej. Sin servicio, Cambio de router, Visita tecnica">
                        @error('new_ticket_type_name') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="hideTicketTypeForm" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:click="saveTicketType" class="btn btn-primary">
                        Guardar tipo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="ticketStatusModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ticketStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketStatusModalLabel">Actualizar estado del ticket</h5>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nuevo estado</label>
                        <input type="text" class="form-control" value="{{ $quick_status === 'cancelado' ? 'Cancelado' : 'Resuelto' }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>{{ $quick_status === 'cancelado' ? 'Motivo de cancelacion' : 'Observacion del resultado' }}</label>
                        <textarea wire:model.defer="quick_status_note" class="form-control" rows="4" placeholder="{{ $quick_status === 'cancelado' ? 'Escribe el motivo de cancelacion' : 'Escribe la observacion del resultado' }}"></textarea>
                        @error('quick_status_note') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetQuickStatusFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:click="updateQuickStatus" class="btn btn-primary">
                        Guardar estado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="ticketInfoModal" data-bs-focus="false" tabindex="-1" aria-labelledby="ticketInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketInfoModalLabel">Detalle del estado</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Tipo de ticket:</strong>
                        <div>{{ $status_info_title }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Estado:</strong>
                        <div class="mt-2">{!! $status_info_badge !!}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Observacion:</strong>
                        <div class="text-muted mt-1">{{ $status_info_note }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Reportado por:</strong>
                        <div class="text-muted mt-1">{{ $status_info_reported_by }}</div>
                    </div>
                    <div>
                        <strong>Ultima actualizacion:</strong>
                        <div class="text-muted mt-1">{{ $status_info_updated_at }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleTicketStatusAction(select, ticketId) {
            const status = select.value;
            if (!status) {
                return;
            }

            @this.call('prepareQuickStatus', ticketId, status);
            select.value = '';
        }
    </script>
</div>
