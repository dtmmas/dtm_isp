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
            <th style="width:150px;font-weight: bold;">Fecha</th>
            <th style="width:280px;font-weight: bold;">Cliente</th>
            <th style="width:150px;font-weight: bold;">DPI</th>
            <th style="width:280px;font-weight: bold;">Correo</th>
            <th style="width:100px;font-weight: bold;">Telefono</th>
            <th style="width:150px;font-weight: bold;">Zona</th>
            <th style="width:250px;font-weight: bold;">Referencia del plan</th>
            <th style="width:120px;font-weight: bold;">MESES VENCIDOS</th>
            <th style="width:250px;font-weight: bold;">MESES</th>
            <th style="width:80px;font-weight: bold;">Cuota</th>
            <th style="width:100px;font-weight: bold;">Total deuda</th>

        </tr>
    </thead>
    <tbody>
        @forelse ($list_client_products as $client_product)
            <?php 
                $total_meses = $client_product->invoices->count();
                $invoicesByMonth = $client_product->invoices->groupBy(function ($invoice) {
                    return $invoice->getMesInvoice();
                });
                $monthList = $invoicesByMonth->keys()->implode(' - ');
                $total_deuda =$client_product->invoices->sum('price');
                $cellphone = $client_product->client->user->phone;
            ?>
            <tr>
                <td data-th="Fecha" >{{$client_product->invoices->first()->created_at}}</td>
               <td data-th="Cliente" >{{$client_product->client->user->name_complete}}</td>
                <td data-th="DPI">{{$client_product->client->user->dni}}</td>
                <td data-th="Correo">{{$client_product->client->user->email}}</td>
                <td data-th="Telefono">{{$cellphone}}</td>
                <td data-th="Zona">{{$client_product->node->zone->name}}</td>
                <td data-th="Referencia">{{$client_product->reference}}</td>
                <td data-th="Meses">{{$total_meses}}</td>
                <td data-th="Meses">{{$monthList}}</td>
                <td data-th="Cuota">{{$client_product->product->price}}</td>
                <td data-th="deuda">{{$total_deuda}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center">No hay resultados</td>
            </tr>
        @endforelse
    </tbody>
</table>