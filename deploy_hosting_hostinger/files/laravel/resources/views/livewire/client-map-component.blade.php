<div>
    <div class="client-map-filter-shell">
        <div class="row">
            <div class="mb-3 col-lg-4 col-md-6">
                <label>Buscar cliente o direccion</label>
                <input wire:model.debounce.400ms="search" type="text" class="form-control" placeholder="Cliente, telefono, plan, zona o direccion">
            </div>
            <div class="mb-3 col-lg-3 col-md-6">
                <label>Zona</label>
                <select wire:model="filter_zone_id" class="form-control">
                    <option value="">Todas las zonas</option>
                    @foreach ($zone_options as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 col-lg-3 col-md-6">
                <label>Nodo</label>
                <select wire:model="filter_node_id" class="form-control">
                    <option value="">Todos los nodos</option>
                    @foreach ($node_options as $node)
                        <option value="{{ $node->id }}">{{ $node->name }}{{ $node->zone ? ' / '.$node->zone->name : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 col-lg-2 col-md-6">
                <label>Estado</label>
                <select wire:model="filter_status" class="form-control">
                    <option value="">Todos</option>
                    <option value="1">Activo</option>
                    <option value="0">Suspendido</option>
                    <option value="-1">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="client-map-summary-grid">
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Puntos visibles</span>
            <strong class="client-map-summary-value">{{ $summary['plans'] }}</strong>
        </div>
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Clientes visibles</span>
            <strong class="client-map-summary-value">{{ $summary['clients'] }}</strong>
        </div>
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Zonas visibles</span>
            <strong class="client-map-summary-value">{{ $summary['zones'] }}</strong>
        </div>
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Nodos visibles</span>
            <strong class="client-map-summary-value">{{ $summary['nodes'] }}</strong>
        </div>
    </div>

    <div class="client-map-legend">
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-success"></span> Activos: {{ $summary['active'] }}</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-warning"></span> Suspendidos: {{ $summary['suspended'] }}</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-danger"></span> Cancelados: {{ $summary['cancelled'] }}</span>
    </div>

    <div class="client-map-shell">
        <div class="plan-map-header client-map-toolbar-wrap">
            <div>
                <div class="plan-map-title">Mapa general de clientes</div>
                <div class="plan-map-toolbar text-muted small">
                    Visualiza clientes con coordenadas registradas y filtra por zona, nodo y estado del plan.
                </div>
            </div>
            <div class="plan-map-controls">
                <button type="button" id="clientMapModeSatellite" class="plan-map-chip active" onclick="switchClientMapMode('Satelital')">
                    Satelital
                </button>
                <button type="button" id="clientMapModeStreet" class="plan-map-chip" onclick="switchClientMapMode('Calles')">
                    Calles
                </button>
                <button type="button" id="clientMapLabelsToggle" class="plan-map-chip plan-map-chip-soft active" onclick="toggleClientMapLabels()">
                    Etiquetas
                </button>
                <button type="button" class="plan-map-icon-btn" onclick="resetClientMapView()" aria-label="Recentrar mapa">
                    <i class="fas fa-crosshairs"></i>
                </button>
            </div>
        </div>

        <div id="clientMapStatus" class="plan-map-status {{ $summary['plans'] > 0 ? 'plan-map-status-good' : 'plan-map-status-muted' }} mb-3">
            @if ($summary['plans'] > 0)
                Mostrando {{ $summary['plans'] }} puntos con coordenadas segun los filtros seleccionados.
            @else
                No hay clientes con coordenadas para los filtros actuales.
            @endif
        </div>

        <div class="client-map-layout">
            <div class="client-map-main">
                <div wire:ignore id="clientMap" class="client-map-canvas"></div>
                <script type="application/json" id="clientMapPayload">@json($map_payload)</script>
            </div>

            <aside class="client-map-side-panel">
                <div class="client-map-side-header">
                    <h4 class="client-map-side-title">Clientes visibles</h4>
                    <span class="client-map-side-total">{{ $summary['plans'] }}</span>
                </div>

                <div class="client-map-side-list">
                    @forelse ($map_points->take(12) as $plan)
                        @php
                            $statusTone = $plan->status == '1' ? 'success' : ($plan->status == '0' ? 'warning' : 'danger');
                            $statusLabel = $plan->status == '1' ? 'Activo' : ($plan->status == '0' ? 'Suspendido' : 'Cancelado');
                        @endphp
                        <a href="{{ route('clients.products', $plan->client_id) }}" class="client-map-side-item">
                            <div class="client-map-side-top">
                                <span class="client-map-status-badge client-map-status-badge-{{ $statusTone }}">{{ $statusLabel }}</span>
                                <span class="client-map-side-node">{{ optional($plan->node)->name ?: 'Sin nodo' }}</span>
                            </div>
                            <strong>{{ optional(optional($plan->client)->user)->name_complete ?: 'Cliente sin nombre' }}</strong>
                            <span>{{ optional($plan->product)->name ?: 'Plan no definido' }}</span>
                            <small>{{ $plan->address ?: 'Sin direccion' }}</small>
                        </a>
                    @empty
                        <div class="client-map-empty">
                            No hay clientes visibles para mostrar en el mapa.
                        </div>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</div>
