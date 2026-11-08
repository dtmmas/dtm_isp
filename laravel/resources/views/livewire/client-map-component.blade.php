<div>
    <div class="client-map-filter-shell">
        <div class="row">
            <div class="mb-3 col-lg-3 col-md-6">
                <label>Buscar cliente o direccion</label>
                <input wire:model.debounce.400ms="search" type="text" class="form-control" placeholder="Cliente, tipo de activo, telefono, plan, zona o direccion">
            </div>
            <div class="mb-3 col-lg-2 col-md-6">
                <label>Zona</label>
                <select wire:model="filter_zone_id" class="form-control">
                    <option value="">Todas las zonas</option>
                    @foreach ($zone_options as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 col-lg-2 col-md-6">
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
                    <option value="deuda">Con deuda</option>
                </select>
            </div>
            @if ($canManageAssets)
            <div class="mb-3 col-lg-2 col-md-6">
                <label>Activo de red</label>
                <select wire:model="filter_asset_type" class="form-control">
                    <option value="">Todos</option>
                    <option value="poste">Postes</option>
                    <option value="nap">Cajas NAP</option>
                    <option value="olt">OLT</option>
                    <option value="onu">ONU</option>
                    <option value="antena">Antenas/AP</option>
                </select>
            </div>
            @endif
            <div class="mb-3 col-lg-3 col-md-6">
                <label>Tickets</label>
                <select wire:model="filter_tickets" class="form-control">
                    <option value="">Todos</option>
                    <option value="open">Con tickets abiertos</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="mb-3 col-lg-3 col-md-6">
                <label class="d-block">Capas visibles</label>
                <div class="form-check form-switch">
                    <input wire:model="show_clients" class="form-check-input" type="checkbox" id="showClientsLayer">
                    <label class="form-check-label" for="showClientsLayer">Clientes</label>
                </div>
                @if ($canManageAssets)
                <div class="form-check form-switch">
                    <input wire:model="show_assets" class="form-check-input" type="checkbox" id="showAssetsLayer">
                    <label class="form-check-label" for="showAssetsLayer">Activos de red</label>
                </div>
                @endif
            </div>
            <div class="mb-3 col-lg-3 col-md-6">
                <label class="d-block">&nbsp;</label>
                @if ($canManageFiber)
                <div class="form-check form-switch">
                    <input wire:model="show_fiber" class="form-check-input" type="checkbox" id="showFiberLayer">
                    <label class="form-check-label" for="showFiberLayer">Rutas de fibra</label>
                </div>
                @endif
                @if ($canManageCoverage)
                <div class="form-check form-switch">
                    <input wire:model="show_coverage" class="form-check-input" type="checkbox" id="showCoverageLayer">
                    <label class="form-check-label" for="showCoverageLayer">Cobertura</label>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="client-map-summary-grid">
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Clientes activos</span>
            <strong class="client-map-summary-value">{{ $summary['active'] }}</strong>
        </div>
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Suspendidos</span>
            <strong class="client-map-summary-value">{{ $summary['suspended'] }}</strong>
        </div>
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Con deuda</span>
            <strong class="client-map-summary-value">{{ $summary['debt'] }}</strong>
        </div>
        @if ($canManageAssets)
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Activos de red</span>
            <strong class="client-map-summary-value">{{ $summary['assets'] }}</strong>
        </div>
        @endif
        @if ($canManageFiber)
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Rutas de fibra</span>
            <strong class="client-map-summary-value">{{ $summary['fiber_routes'] }}</strong>
        </div>
        @endif
        @if ($canManageCoverage)
        <div class="client-map-summary-card">
            <span class="client-map-summary-label">Coberturas</span>
            <strong class="client-map-summary-value">{{ $summary['coverages'] }}</strong>
        </div>
        @endif
    </div>

    <div class="client-map-legend">
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-success"></span> Activos: {{ $summary['active'] }}</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-warning"></span> Suspendidos: {{ $summary['suspended'] }}</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-danger"></span> Con deuda: {{ $summary['debt'] }}</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot client-map-legend-dot-primary"></span> Con tickets abiertos: {{ $summary['open_tickets'] }}</span>
        @if ($canManageAssets)
        <span class="client-map-legend-item"><span class="client-map-legend-dot" style="background:#92400e;"></span> Postes</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot" style="background:#7c3aed;"></span> Cajas NAP</span>
        <span class="client-map-legend-item"><span class="client-map-legend-dot" style="background:#2563eb;"></span> OLT/ONU</span>
        @endif
        @if ($canManageCoverage)
        <span class="client-map-legend-item"><span class="client-map-legend-dot" style="background:#16a34a;"></span> Antenas y cobertura</span>
        @endif
    </div>

    <div class="client-map-shell">
        <div class="plan-map-header client-map-toolbar-wrap">
            <div>
                <div class="plan-map-title">Mapa general ISP</div>
                <div class="plan-map-toolbar text-muted small">
                    Visualiza clientes, postes, cajas NAP, OLT/ONU, antenas, rutas de fibra y coberturas desde una sola vista operativa.
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
            @if (($summary['plans'] + $summary['assets'] + $summary['fiber_routes'] + $summary['coverages']) > 0)
                {{ ($canManageAssets || $canManageFiber || $canManageCoverage) ? 'Mostrando clientes e infraestructura segun los filtros seleccionados.' : 'Mostrando clientes asignados segun los filtros seleccionados.' }}
            @else
                No hay elementos para los filtros actuales.
            @endif
        </div>

        <div class="client-map-layout">
            <div class="client-map-main">
                <div wire:ignore id="clientMap" class="client-map-canvas"></div>
                <script type="application/json" id="clientMapPayload">@json($map_payload)</script>
            </div>

            <aside class="client-map-side-panel">
                <div class="client-map-side-header">
                    <h4 class="client-map-side-title">Elementos visibles</h4>
                    <span class="client-map-side-total">{{ $summary['plans'] + $summary['assets'] }}</span>
                </div>

                <div class="client-map-side-list">
                    @forelse ($map_points->take(12) as $plan)
                        @php
                            $statusTone = $plan->billing_status === 'deuda' ? 'danger' : ($plan->status == '0' ? 'warning' : 'success');
                            $statusLabel = $plan->billing_status === 'deuda' ? 'Con deuda' : ($plan->status == '0' ? 'Suspendido' : 'Activo');
                        @endphp
                        <a href="{{ Auth::user()->can('clients.index') ? route('clients.products', $plan->client_id) : route('clients.tickets', ['client' => $plan->client_id, 'client_product_id' => $plan->id]) }}" class="client-map-side-item">
                            <div class="client-map-side-top">
                                <span class="client-map-status-badge client-map-status-badge-{{ $statusTone }}">{{ $statusLabel }}</span>
                                <span class="client-map-side-node">{{ optional($plan->node)->name ?: 'Sin nodo' }}</span>
                            </div>
                            <strong>{{ optional(optional($plan->client)->user)->name_complete ?: 'Cliente sin nombre' }}</strong>
                            <span>{{ optional($plan->product)->name ?: 'Plan no definido' }}</span>
                            @if (($plan->debt_months ?? 0) > 0)
                                <small class="text-danger">Facturas pendientes: {{ $plan->debt_months }}</small>
                            @endif
                            @if (($plan->open_tickets_count ?? 0) > 0)
                                <small class="text-primary">Tickets abiertos: {{ $plan->open_tickets_count }}</small>
                            @endif
                            <small>{{ $plan->address ?: 'Sin direccion' }}</small>
                        </a>
                    @empty
                        <div class="client-map-empty">
                            No hay clientes visibles para mostrar en el mapa.
                        </div>
                    @endforelse

                    @if ($canManageAssets)
                    @foreach ($side_assets as $asset)
                        <div class="client-map-side-item">
                            <div class="client-map-side-top">
                                <span class="client-map-status-badge client-map-status-badge-primary">{{ strtoupper($asset->asset_type) }}</span>
                                <span class="client-map-side-node">{{ optional($asset->node)->name ?: 'Sin nodo' }}</span>
                            </div>
                            <strong>{{ $asset->name }}</strong>
                            <span>{{ $asset->code ?: 'Sin codigo' }}</span>
                            <small>{{ $asset->description ?: 'Activo sin descripcion' }}</small>
                        </div>
                    @endforeach
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
