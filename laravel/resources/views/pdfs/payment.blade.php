<!DOCTYPE html>
<html lang="en">
<head>
  <title>PDF orden</title>
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
            <h6 class="text-center font-weight-bold mb-1">{{ $data->nombre_empresa }}</h6>
            @if ( $data->slogan_empresa!='')
            <p class="text-center  mb-0">{{ $data->slogan_empresa }}</p>
            @endif
            @if ($data->nit_empresa!='')
            <p class="text-center  mb-0">NIT: {{ $data->nit_empresa }}</p>
            @endif
            @if ($data->direccion_empresa!='')
            <p class="text-center  mb-0">Dir: {{ $data->direccion_empresa }}</p>
            @endif
            @if ($data->telefono_empresa!='')
            <p class="text-center  mb-0">Tel: {{ $data->telefono_empresa }}</p>
            @endif
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0">Fecha: {{$payment->created_at}}</p>	
                    <p class="mb-0">***************************************</p>	
                    <p class="mb-0 mt-0 font-weight-bold">
                        No. Recibo. {{ $payment->numero }}
                    </p>
                    <p class="mb-0">***************************************</p>	
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-12 text-left">
                    <p class="mb-0"><strong>Nombre Cliente:</strong> {{$payment->name_client}}</p>
                    <p  class="mb-1"><strong>DPI</strong>: {{$payment->invoice->client_product->client->user->dni}}</p>
                    {{-- <p  class="mb-0"><strong>Email</strong>: {{$payment->invoice->client_product->client->user->email}}</p>
                    <p  class="mb-0"><strong>Telefono</strong>: {{$payment->invoice->client_product->client->user->phone}}</p>
                    <p  class="mb-0"><strong>Dirección</strong>: {{$payment->address_plan}}</p> --}}
                </div>
            </div>
            <div class="row">     
                <div class="col-md-12 text-left">
                    <table class="">
                        <thead>
                            <tr><td colspan="2">----------------------------------------------</td></tr>
                            <tr>
                                <th>Descripcion</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px dashed black;">
                                <td>
                                    <p class="mb-0">{{ ($payment->invoice->payments_advancement>0)?$payment->observation:'Pago del mes de '.$mes }}</p>
                                    <p class="mb-0">Plan: {{ $payment->name_product }}</p>
                                    <p class="mb-1">Dirección: {{ $payment->address_plan}} </p>
                                </td>
                                <td>
                                    {{ $payment->price_formatted}}
                                </td>
                            </tr>
                            <tr><td colspan="2">----------------------------------------------</td></tr>
                            <tr >
                                <th>
                                    <p class="mb-0">Forma de Pago: {{ (($payment->method_payment=='1')?'Deposito':(($payment->method_payment=='3')?'Efectivo':'Tarjeta'))}}</p>    
                                </th>
                            </tr>
                            @if ($payment->no_voucher !="")
                            <tr><th colspan="2">Transferencia No. {{ $payment->no_voucher }}</th></tr>
                            @endif
                            @if ($payment->reportado_por!='')
                            <tr><th colspan="2">Reportado por: {{$payment->reportado_por}}</th></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
 
</div>

</body>
</html>
