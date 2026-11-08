<!DOCTYPE html>
<html lang="en">
<head>
  <title>PDF reporte clientes</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
  <style>
      body{
          font-size: 13px
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
                                <th style="font-weight: bold;" >Fecha</th>
                                <th style="font-weight: bold;" >Fecha Pago</th>
                                <th style="font-weight: bold;" >Mes</th>
                                <th style="font-weight: bold;">Cliente</th>
                                <th style="font-weight: bold;">Plan</th>
                                <th style="font-weight: bold;">Direccion</th>
                                <th style="font-weight: bold;">Zona</th>
                                <th style="font-weight: bold;">Monto</th>
                                <th style="font-weight: bold;">Estado</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $invoice)
                                <tr>
                                   <td data-th="Fecha" >{{$invoice->created_at}}</td>
                                   <td data-th="FechaPago" >{{(($invoice->status=='1')?$invoice->updated_at:"---")}}</td>
                                    <td data-th="Mes" >{{$invoice->getMesInvoice()}}</td>
                                    <td data-th="Cliente">{{$invoice->name_client}}</td>
                                    <td data-th="Plan">{{$invoice->name_product}}</td>
                                    <td data-th="Direccion">{{$invoice->client_product->address}}</td>
                                    <td data-th="Zona">{{$invoice->client_product->node->zone->name}}</td>
                                    <td data-th="Monto">{{$invoice->price_formatted}}</td>
                                    <td data-th="Estado">{!!$invoice->status_html!!}</td>
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
