<table>
<?php $data = empresaConfig() ?>
    <thead>
        @if ($data->nombre_empresa!='')
            <tr><th colspan="10" style="text-align: center;font-weight:bold;">{{ $data->nombre_empresa }}</th></tr>
        @endif
        @if ($data->slogan_empresa!='')
            <tr><th colspan="10" style="text-align: center;font-weight:bold;">{{ $data->slogan_empresa }}</th></tr>
        @endif
        @if ($data->direccion_empresa!='')
            <tr><th colspan="10" style="text-align: center;font-weight:bold;">{{ $data->direccion_empresa }}</th></tr>
        @endif
        @if ($data->nit_empresa!='')
            <tr><th colspan="10" style="text-align: center;font-weight:bold;">{{ $data->nit_empresa }}</th></tr>
        @endif
        <tr><th colspan="11" style="text-align: center;font-weight:bold;"></th></tr>
        <tr>
            <th style="width:140px;font-weight: bold;">Zona</th>
            <th style="width:140px;font-weight: bold;">Nodo</th>
            <th style="width:140px;font-weight: bold;">Plan</th>
            <th style="width:110px;font-weight: bold;">Precio del plan</th>
            <th style="width:190px;font-weight: bold;">Cliente</th>
            <th style="width:180px;font-weight: bold;">Correo</th>
            <th style="width:120px;font-weight: bold;">Telefono</th>
            <th style="width:180px;font-weight: bold;">Direccion</th>
            <th style="width:160px;font-weight: bold;">Referencia</th>
            <th style="width:120px;font-weight: bold;">Fecha Instalacion</th>
            <th style="width:110px;font-weight: bold;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($registros as $plan)
            <tr>
                <td>{{ $plan->zone_name ?? 'Sin zona' }}</td>
                <td>{{ $plan->node_name ?? 'Sin nodo' }}</td>
                <td>{{ $plan->plan_name }}</td>
                <td>{{ number_format((float) $plan->plan_price, 2) }}</td>
                <td>{{ trim($plan->name . ' ' . $plan->lastname) }}</td>
                <td>{{ $plan->email }}</td>
                <td>{{ $plan->phone }}</td>
                <td>{{ $plan->address }}</td>
                <td>{{ $plan->reference }}</td>
                <td>{{ $plan->date_instalation }}</td>
                <td>
                    @if ($plan->status === '1')
                        Activo
                    @elseif ($plan->status === '0')
                        Suspendido
                    @elseif ($plan->status === '-1')
                        Cancelado
                    @else
                        Indefinido
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center">No hay resultados</td>
            </tr>
        @endforelse
        @if (count($registros) > 0)
            <tr>
                <td colspan="3"><strong>TOTAL PRECIOS</strong></td>
                <td><strong>{{ number_format((float) $registros->sum('plan_price'), 2) }}</strong></td>
                <td colspan="7"></td>
            </tr>
        @endif
    </tbody>
</table>
