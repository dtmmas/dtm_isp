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
            <th style="width:115px;font-weight: bold;">Nombre</th>
            <th style="width:115px;font-weight: bold;">Apellido</th>
            <th style="width:115px;font-weight: bold;">DPI</th>
            <th style="width:160px;font-weight: bold;">Correo</th>
            <th style="width:115px;font-weight: bold;">Telefono</th>
            <th style="width:110px;font-weight: bold;">Estado</th>
            <th style="width:115px;font-weight: bold;">Facturas Vencidas</th>

        </tr>
    </thead>
    <tbody>
        @forelse ($registros as $client)
            <?php $vencidas = $client->invoicesVencidas();
                    $activo = ($vencidas>0)?false:true; ?>
            <tr>
               <td data-th="Fecha" >{{$client->created_at}}</td>
               <td data-th="Nombre" >{{$client->user->name}}</td>
                <td data-th="Apellido">{{$client->user->lastname}}</td>
                <td data-th="DPI">{{$client->user->dni}}</td>
                <td data-th="Correo">{{$client->user->email}}</td>
                <td data-th="Telefono">{{$client->user->phone}}</td>
                <td data-th="Estado">{{ $client->HtmlEstatus($activo) }}</td>
                <td data-th="Facturas">{{ $vencidas }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center">No hay resultados</td>
            </tr>
        @endforelse
    </tbody>
</table>