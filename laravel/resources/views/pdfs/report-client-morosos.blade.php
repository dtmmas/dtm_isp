<!DOCTYPE html>
<html lang="en">
<head>
  <title>PDF reporte clientes morosos</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <style>
      body{
          font-size: 12px
      }
    .table td, .table th {
        padding: 0.5rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }
  </style>
</head>
<body>
<?php $data = empresaConfig() ?>
<div class="">
    <div class="row justify-content-center">
        <div class="col-12">
            <h3 class="text-center font-weight-bold mb-1">{{ $data->nombre_empresa }}</h3>
            @if ( $data->slogan_empresa!='')
            <p class="text-center  mb-0">{{ $data->slogan_empresa }}</p>
            @endif
            @if ($data->direccion_empresa!='')
            <p class="text-center  mb-0">Dir: {{ $data->direccion_empresa }}</p>
            @endif
            @if ($data->nit_empresa!='')
            <p class="text-center  mb-0">NIT: {{ $data->nit_empresa }}</p>
            @endif
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">***************************************</p> 
                    <p class="mb-0">Fecha: {{ fecha_actual() }}</p>	
                    <p class="mb-0">***************************************</p>	
                </div>
            </div>
           
            <div class="row">     
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="font-weight: bold;">Fecha</th>
                                <th style="font-weight: bold;">Cliente</th>
                                <th style="font-weight: bold;">DPI</th>
                                <th style="font-weight: bold;">Correo</th>
                                <th style="font-weight: bold;">Telefono</th>
                                <th style="font-weight: bold;">Zona</th>
                                <th style="font-weight: bold;">Referencia del plan</th>
                                <th style="font-weight: bold;">MESES VENCIDOS</th>
                                <th style="font-weight: bold;">MESES</th>
                                <th style="font-weight: bold;">Cuota</th>
                                <th style="font-weight: bold;">Total deuda</th>
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
            </div>
        </div>
    </div>
 
</div>

</body>
</html>
