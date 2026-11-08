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
                                <th style="font-weight: bold;">Fecha</th>
                                <th style="font-weight: bold;">Nombre</th>
                                <th style="font-weight: bold;">Apellido</th>
                                <th style="font-weight: bold;">DPI</th>
                                <th style="font-weight: bold;">Correo</th>
                                <th style="font-weight: bold;">Telefono</th>
                                <th style="font-weight: bold;">Estado</th>
                                <th style="font-weight: bold;">Facturas Vencidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $client)
                        <?php $vencidas = $client->invoicesVencidas();
                                $activo = ($vencidas>0)?false:true; ?>
                                <tr>
                                   <td data-th="Fecha" >{{$client->created_at}}</td>
                                   <td data-th="Nombre" >{{$client->user->name}}</td>
                                    <td data-th="Apellido">{{$client->user->lastname}}</td>
                                    <td data-th="DPI">{{$client->user->dni}}</td>
                                    <td data-th="Correo">{{$client->user->email}}</td>
                                    <td data-th="Telefono">{{$client->user->phone}}</td>
                                    <td data-th="Estado">{!! $client->HtmlEstatus($activo,'html') !!}</td>
                                    <td data-th="Facturas">{{ $vencidas }}</td>
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
