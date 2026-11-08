<div>
    <style>
        .hidden{
            display: none;
        }
    </style>

    @if ($mostrarCliente)
    <div class="mb-3 col-xs-12 col-sm-8">
        <div class="input-group col">
            <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por cliente o No. Boleta o Transferencia No.">
            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-5">
            {!! Form::label('user_id', __('Cobrador') ); !!}
            {!! Form::select('user_id', $users, null, ['wire:model.defer' => 'user_id', 'class' => 'form-control', 'placeholder' => 'Todos']) !!}
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-5">
            {!! Form::label('name', __('Desde') ); !!}
            <input wire:model.defer="desde" id="desde" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
            @error('desde') <span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group col-md-3">
            {!! Form::label('name', __('Hasta') ); !!}
            <input wire:model.defer="hasta" id="hasta" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
            @error('hasta') <span class="text-danger">{{ $message }}</span>@enderror
        </div>

        <div class="form-group col-md-2">
            {!! Form::label('method_payment', __('Metodo de pago') ); !!}
            <select wire:model.defer="method_payment" class="form-control">
                <option value="">Todos</option>
                <option value="1">Deposito</option>
                <option value="2">Tarjeta</option>
                <option value="3">Efectivo</option>
            </select>
        </div>

        <div class="form-group col-md-2"><br>
            <button class="btn btn-info" wire:click="consultar">Consultar</button>
        </div>
    </div>
    @endif

    <div class="mb-3">
        <strong>Total de pagos recibidos:</strong> Q {{ number_format((float) $total_payments_received, 2) }}
    </div>

    <!-- Botón para exportar Excel -->
    @if(count($list_payments) > 0)
    <div class="mb-3">
        <button class="btn btn-success btn-sm" wire:click="exportExcel" wire:loading.attr="disabled">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </button>
    </div>
    @endif

    <div class="relative">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No. Recibo</th>
                    <th>Fecha</th>
                    @if ($mostrarCliente)
                    <th>Cliente</th>
                    @endif
                    <th>Mes</th>
                    <th>Reportado por</th>
                    @if ($mostrarPlan)
                    <th>Plan</th>
                    <th>Direccion</th>
                    @endif
                    <th>Img Transferencia</th>
                    <th>Transferencia No.</th>
                    <th>Monto</th>
                    <th>Metodo de pago</th>
                    @if (!$mostrarPendientes)
                    <th>Estado</th>
                    @endif
                    @if ($mostrarPendientes)
                        @can('payments.process')
                        <th>Procesar</th>
                        @endcan
                    @endif
                    @can('payments.process')
                    <th>Acciones</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse ($list_payments as $payment)
                    <tr>
                        <td>{{$payment->numero}}</td>
                        <td>{{$payment->created_at}}</td>
                        @if ($mostrarCliente)
                        <td>{{$payment->name_client}}</td>
                        @endif
                        <td>{{$payment->invoice->getMesInvoice()}}</td>
                        <td>{{$payment->getReportadoPor()}}</td>
                        @if ($mostrarPlan)
                        <td>{{$payment->name_product}}</td>
                        <td>{{$payment->address_plan}}</td>
                        @endif
                        <td>
                            @if ($payment->img_voucher)
                            <a href="{{Storage::url($payment->img_voucher)}}" target="_blank">
                                <img style="max-width: 150px;" src="{{Storage::url($payment->img_voucher)}}" alt=""/>
                            </a>
                            @else
                            N/A
                            @endif
                        </td>
                        <td>
                            {{$payment->no_voucher}}<br>
                            <a href="{{ route('payments.pdf', $payment->id) }}" target="_blank">Ver PDF</a><br>
                            <a href="{{ route('payments.print', $payment->id) }}" target="_blank">Imprimir 2 copias</a>
                        </td>
                        <td>{{$payment->price_formatted}}</td>
                        <td>{!!$payment->method_payment_html!!}</td>
                        @if (!$mostrarPendientes)
                        <td>{!!$payment->status_html!!}</td>
                        @endif
                        @if ($mostrarPendientes)
                            @can('payments.process')
                            <td>
                                <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','1')" class="btn-xs btn btn-success" href="#!">Aprobar</a>
                                <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','0')" class="btn-xs btn btn-danger" href="#!">Rechazar</a>
                            </td>
                            @endcan
                        @endif
                        @can('payments.process')
                        <td>
                            <a onclick="EliminarPago('{{$payment->id}}','{{$payment->no_voucher}}')" class="btn-xs btn btn-danger" href="#!">Eliminar pago</a>
                        </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="13">{{ (($invoice_id=='9999999')?'Cargando...':'Sin reportar') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $list_payments->links() }}
    </div>
</div>
