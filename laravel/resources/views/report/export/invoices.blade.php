<table>
<?php $data = empresaConfig() ?>
    <thead>
        @if ($data->nombre_empresa!='')
            <tr><th colspan="8" style="text-align: center;font-weight:bold;">{{ $data->nombre_empresa }}</th></tr>
        @endif
        @if ($data->slogan_empresa!='')
            <tr><th colspan="8" style="text-align: center;font-weight:bold;">{{ $data->slogan_empresa }}</th></tr>
        @endif
        @if ($data->direccion_empresa!='')
            <tr><th colspan="8" style="text-align: center;font-weight:bold;">{{ $data->direccion_empresa }}</th></tr>
        @endif
        @if ($data->nit_empresa!='')
            <tr><th colspan="8" style="text-align: center;font-weight:bold;">{{ $data->nit_empresa }}</th></tr>
        @endif
            <tr><th colspan="8" style="text-align: center;font-weight:bold;"></th></tr>
        <tr>
            <th style="width:110px;font-weight: bold;" >Fecha</th>
            <th style="width:110px;font-weight: bold;" >Fecha Pago</th>
            <th style="width:80px;font-weight: bold;" >Mes</th>
            <th style="width:210px;font-weight: bold;" >Cliente</th>
            <th style="width:120px;font-weight: bold;" >Plan</th>
            <th style="width:160px;font-weight: bold;" >Direccion</th>
            <th style="width:130px;font-weight: bold;" >Zona</th>
            <th style="width:100px;font-weight: bold;" >Monto</th>
            <th style="width:100px;font-weight: bold;" >Estado</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($registros as $invoice)
            <tr>
                <td data-th="Fecha" >{{$invoice->date}}</td>
                <td data-th="FechaPago" >{{(($invoice->status=='1')?$invoice->date_update:"---")}}</td>
                <td data-th="Mes" >{{$invoice->getMesInvoice()}}</td>
                <td data-th="Cliente">{{$invoice->name_client}}</td>
                <td data-th="Plan">{{$invoice->name_product}}</td>
                <td data-th="Direccion">{{$invoice->client_product->address}}</td>
                <td data-th="Zona">{{$invoice->client_product->node->zone->name}}</td>
                <td data-th="Monto">{{$invoice->price_formatted}}</td>
                <td data-th="Estado">{{ $invoice->statusVisualizacion()}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No hay resultados</td>
            </tr>
        @endforelse
    </tbody>
</table>
