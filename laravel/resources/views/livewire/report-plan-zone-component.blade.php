<div>
    <div class="card">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="form-group col-md-5">
                    {!! Form::label('zone_id', __('Zona') ); !!}
                    {!! Form::select('zone_id', $zones, null, ['wire:model.defer' => 'zone_id', 'class' => 'form-control', 'placeholder' => 'Todas las zonas']) !!}
                </div>
                <div class="form-group col-md-3">
                    {!! Form::label('status', __('Estado del plan') ); !!}
                    <select wire:model.defer="status" class="form-control">
                        <option value="1">Activos</option>
                        <option value="-1">Cancelados</option>
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <button type="button" wire:click="filtrar" wire:loading.attr="disabled" class="btn btn-info w-100">
                        Consultar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Resumen</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Total de planes</th>
                            <th>Suma de precios</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plans_summary as $summary)
                            <tr>
                                <td>{{ $summary->zone_name }}</td>
                                <td>{{ $summary->total_planes }}</td>
                                <td>Q {{ number_format((float) $summary->total_precios, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No hay resultados</td>
                            </tr>
                        @endforelse
                        @if (count($plans_summary) > 0)
                            <tr>
                                <td><strong>TOTAL</strong></td>
                                <td><strong>{{ $total_summary_planes }}</strong></td>
                                <td><strong>Q {{ number_format((float) $total_summary_prices, 2) }}</strong></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Detalle de planes</h5>
            @if (count($list_plans) > 0)
                <button class="text-white btn btn-success btn-sm mb-2" wire:loading.attr="disabled" wire:click="exportExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            @endif
            <div class="table-responsive2" id="divTable">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Nodo</th>
                            <th>Plan</th>
                            <th>Precio del plan</th>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Direccion</th>
                            <th>Referencia</th>
                            <th>Fecha Instalacion</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_plans as $plan)
                            <tr>
                                <td data-th="Zona">{{ $plan->zone_name ?? 'Sin zona' }}</td>
                                <td data-th="Nodo">{{ $plan->node_name ?? 'Sin nodo' }}</td>
                                <td data-th="Plan">{{ $plan->plan_name }}</td>
                                <td data-th="Precio">Q {{ number_format((float) $plan->plan_price, 2) }}</td>
                                <td data-th="Cliente">{{ trim($plan->name . ' ' . $plan->lastname) }}</td>
                                <td data-th="Correo">{{ $plan->email }}</td>
                                <td data-th="Telefono">{{ $plan->phone }}</td>
                                <td data-th="Direccion">{{ $plan->address }}</td>
                                <td data-th="Referencia">{{ $plan->reference }}</td>
                                <td data-th="Fecha">{{ $plan->date_instalation }}</td>
                                <td data-th="Estado">
                                    @if ($plan->status === '1')
                                        <span class="badge bg-success rounded">Activo</span>
                                    @elseif ($plan->status === '0')
                                        <span class="badge bg-warning rounded">Suspendido</span>
                                    @elseif ($plan->status === '-1')
                                        <span class="badge bg-danger rounded">Cancelado</span>
                                    @else
                                        <span class="badge bg-secondary rounded">Indefinido</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No hay resultados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $list_plans->links() }}
            </div>
        </div>
    </div>
</div>
