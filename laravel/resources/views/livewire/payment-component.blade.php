<div>
    <style>
        .hidden{
            display: none;
        }

        .payment-filter-card,
        .payment-summary-card,
        .payment-table-shell {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .05);
        }

        .payment-filter-card {
            padding: 1rem 1rem .3rem;
            margin-bottom: 1rem;
        }

        .payment-filter-card .form-control,
        .payment-filter-card .input-group-text {
            border-color: #dbe2f0;
        }

        .payment-filter-card .input-group-text {
            background: #f8fafc;
            color: #64748b;
        }

        .payment-filter-card label {
            font-size: .74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-bottom: .35rem;
        }

        .payment-filter-actions {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-wrap: wrap;
            padding-top: 1.7rem;
        }

        .payment-summary-card {
            padding: .85rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .payment-total-label {
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-bottom: .18rem;
        }

        .payment-total-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }

        .payment-table-shell {
            overflow: hidden;
        }

        .payment-table-wrapper {
            overflow-x: auto;
        }

        .payment-table {
            width: max-content;
            min-width: 100%;
            margin-bottom: 0;
        }

        .payment-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .78rem;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            vertical-align: middle;
        }

        .payment-table tbody td {
            vertical-align: top;
            border-color: #eef2f7;
            color: #0f172a;
            font-size: .86rem;
        }

        .payment-col-number,
        .payment-col-date,
        .payment-col-month,
        .payment-col-amount,
        .payment-col-method,
        .payment-col-status {
            white-space: nowrap;
        }

        .payment-col-number { min-width: 7rem; }
        .payment-col-date { min-width: 10rem; }
        .payment-col-client { min-width: 12rem; }
        .payment-col-month { min-width: 8rem; }
        .payment-col-reported { min-width: 10rem; }
        .payment-col-plan { min-width: 11rem; }
        .payment-col-address { min-width: 15rem; max-width: 22rem; }
        .payment-col-voucher-image { min-width: 9rem; }
        .payment-col-voucher-number { min-width: 9rem; }
        .payment-col-amount { min-width: 7rem; }
        .payment-col-method { min-width: 8rem; }
        .payment-col-status { min-width: 7rem; }
        .payment-col-process { min-width: 10rem; }
        .payment-col-actions { min-width: 9rem; }

        .payment-voucher-thumb {
            display: inline-block;
            border-radius: .85rem;
            overflow: hidden;
            border: 1px solid #dbe2f0;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .07);
            background: #fff;
        }

        .payment-voucher-thumb img {
            display: block;
            width: 96px;
            height: 72px;
            object-fit: cover;
        }

        .payment-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .payment-mobile-row {
            display: none;
        }

        .payment-mobile-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
            padding: .85rem .9rem;
        }

        .payment-mobile-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: .65rem;
        }

        .payment-mobile-title {
            margin: 0;
            font-size: .92rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .payment-mobile-subtitle {
            font-size: .73rem;
            color: #64748b;
            margin-top: .12rem;
        }

        .payment-mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem .8rem;
            margin-bottom: .7rem;
        }

        .payment-mobile-label {
            display: block;
            font-size: .64rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .1rem;
        }

        .payment-mobile-value {
            display: block;
            font-size: .82rem;
            color: #0f172a;
            line-height: 1.25;
            word-break: break-word;
        }

        .payment-mobile-actions {
            border-top: 1px solid #e2e8f0;
            padding-top: .65rem;
        }

        @media (max-width: 767.98px) {
            .payment-filter-card {
                padding: .9rem .85rem .2rem;
            }

            .payment-summary-card {
                padding: .8rem .85rem;
            }

            .payment-filter-actions {
                padding-top: .35rem;
            }

            .payment-desktop-row {
                display: none;
            }

            .payment-mobile-row {
                display: table-row;
            }

            .payment-mobile-row td {
                padding: .45rem 0 !important;
                border-top: none !important;
            }

            .payment-mobile-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    @if ($mostrarCliente)
    <div class="payment-filter-card">
        <div class="row">
            <div class="mb-3 col-xs-12 col-sm-8">
                <div class="input-group col">
                    <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por cliente, telefono, referencia, direccion o No. Boleta">
                    <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-5">
                {!! Form::label('user_id', __('Usuario que reporto') ); !!}
                {!! Form::select('user_id', $users, null, ['wire:model.defer' => 'user_id', 'class' => 'form-control', 'placeholder' => 'Todos']) !!}
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-3">
                {!! Form::label('tipo_fecha', __('Filtrar por fecha') ); !!}
                <select wire:model="tipo_fecha" class="form-control">
                    <option value="rango">Rango de fechas</option>
                    <option value="dia">Dia</option>
                    <option value="mes">Mes</option>
                    <option value="anio">Año</option>
                </select>
            </div>

            @if ($tipo_fecha === 'dia')
            <div class="form-group col-md-5">
                {!! Form::label('fecha_dia', __('Dia') ); !!}
                <input wire:model.defer="fecha_dia" id="fecha_dia" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
                @error('fecha_dia') <span class="text-danger">{{ $message }}</span>@enderror
            </div>
            @elseif ($tipo_fecha === 'mes')
            <div class="form-group col-md-5">
                {!! Form::label('fecha_mes', __('Mes') ); !!}
                <input wire:model.defer="fecha_mes" id="fecha_mes" type="month" class="form-control" autocomplete="off" >
                @error('fecha_mes') <span class="text-danger">{{ $message }}</span>@enderror
            </div>
            @elseif ($tipo_fecha === 'anio')
            <div class="form-group col-md-5">
                {!! Form::label('fecha_anio', __('Año') ); !!}
                <input wire:model.defer="fecha_anio" id="fecha_anio" type="number" min="2000" max="2100" step="1" class="form-control" placeholder="2026" autocomplete="off" >
                @error('fecha_anio') <span class="text-danger">{{ $message }}</span>@enderror
            </div>
            @else
            <div class="form-group col-md-3">
                {!! Form::label('name', __('Desde') ); !!}
                <input wire:model.defer="desde" id="desde" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
                @error('desde') <span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group col-md-3">
                {!! Form::label('name', __('Hasta') ); !!}
                <input wire:model.defer="hasta" id="hasta" type="date" data-date-format="yyyy-mm-dd" class="form-control" autocomplete="off" >
                @error('hasta') <span class="text-danger">{{ $message }}</span>@enderror
            </div>
            @endif

            <div class="form-group col-md-2">
                {!! Form::label('method_payment', __('Metodo de pago') ); !!}
                <select wire:model.defer="method_payment" class="form-control">
                    <option value="">Todos</option>
                    <option value="1">Deposito</option>
                    <option value="2">Tarjeta</option>
                    <option value="3">Efectivo</option>
                </select>
            </div>

            <div class="form-group col-md-2">
                <div class="payment-filter-actions">
                    <button class="btn btn-info" wire:click="consultar">Consultar</button>
                    <button class="btn btn-secondary" wire:click="limpiarFiltrosFecha">Limpiar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="payment-summary-card">
        <div>
            <div class="payment-total-label">Total de pagos recibidos</div>
            <div class="payment-total-value">Q {{ number_format((float) $total_payments_received, 2) }}</div>
        </div>
        @if(count($list_payments) > 0)
        <div>
            <button class="btn btn-success btn-sm" wire:click="exportExcel" wire:loading.attr="disabled">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
        @endif
    </div>

    <div class="payment-table-shell">
        <div class="payment-table-wrapper">
            <table class="table table-striped payment-table">
                <thead>
                    <tr>
                        <th class="payment-col-number">No. Recibo</th>
                        <th class="payment-col-date">Fecha</th>
                        @if ($mostrarCliente)
                        <th class="payment-col-client">Cliente</th>
                        @endif
                        <th class="payment-col-month">Mes</th>
                        <th class="payment-col-reported">Reportado por</th>
                        @if ($mostrarPlan)
                        <th class="payment-col-plan">Plan</th>
                        <th class="payment-col-address">Direccion</th>
                        @endif
                        <th class="payment-col-voucher-image">Img Transferencia</th>
                        <th class="payment-col-voucher-number">Transferencia No.</th>
                        <th class="payment-col-amount">Monto</th>
                        <th class="payment-col-method">Metodo de pago</th>
                        @if (!$mostrarPendientes)
                        <th class="payment-col-status">Estado</th>
                        @endif
                        @if ($mostrarPendientes)
                            @can('payments.process')
                            <th class="payment-col-process">Procesar</th>
                            @endcan
                        @endif
                        @can('payments.destroy')
                        <th class="payment-col-actions">Acciones</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list_payments as $payment)
                        <tr class="payment-desktop-row">
                            <td class="payment-col-number">{{$payment->numero}}</td>
                            <td class="payment-col-date">{{$payment->created_at}}</td>
                            @if ($mostrarCliente)
                            <td class="payment-col-client">{{$payment->name_client}}</td>
                            @endif
                            <td class="payment-col-month">{{$payment->invoice->getMesInvoice()}}</td>
                            <td class="payment-col-reported">{{$payment->getReportadoPor()}}</td>
                            @if ($mostrarPlan)
                            <td class="payment-col-plan">{{$payment->name_product}}</td>
                            <td class="payment-col-address">{{$payment->address_plan}}</td>
                            @endif
                            <td class="payment-col-voucher-image">
                                @if ($payment->img_voucher)
                                <a class="payment-voucher-thumb" href="{{Storage::url($payment->img_voucher)}}" target="_blank">
                                    <img src="{{Storage::url($payment->img_voucher)}}" alt="Transferencia"/>
                                </a>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="payment-col-voucher-number">
                                {{$payment->no_voucher}}
                            </td>
                            <td class="payment-col-amount">{{$payment->price_formatted}}</td>
                            <td class="payment-col-method">{!!$payment->method_payment_html!!}</td>
                            @if (!$mostrarPendientes)
                            <td class="payment-col-status">{!!$payment->status_html!!}</td>
                            @endif
                            @if ($mostrarPendientes)
                                @can('payments.process')
                                <td class="payment-col-process">
                                    <div class="payment-action-group">
                                        <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','1')" class="btn-xs btn btn-success" href="#!">Aprobar</a>
                                        <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','0')" class="btn-xs btn btn-danger" href="#!">Rechazar</a>
                                    </div>
                                </td>
                                @endcan
                            @endif
                            @can('payments.destroy')
                            <td class="payment-col-actions">
                                <div class="payment-action-group">
                                    <a onclick="EliminarPago('{{$payment->id}}','{{$payment->no_voucher}}')" class="btn-xs btn btn-danger" href="#!">Eliminar pago</a>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        <tr class="payment-mobile-row">
                            <td colspan="15">
                                <div class="payment-mobile-card">
                                    <div class="payment-mobile-header">
                                        <div>
                                            <h6 class="payment-mobile-title">Recibo #{{$payment->numero}}</h6>
                                            <div class="payment-mobile-subtitle">{{$payment->created_at}}</div>
                                        </div>
                                        @if (!$mostrarPendientes)
                                        <div>{!! $payment->status_html !!}</div>
                                        @endif
                                    </div>

                                    <div class="payment-mobile-grid">
                                        @if ($mostrarCliente)
                                        <div>
                                            <span class="payment-mobile-label">Cliente</span>
                                            <span class="payment-mobile-value">{{$payment->name_client}}</span>
                                        </div>
                                        @endif
                                        <div>
                                            <span class="payment-mobile-label">Mes</span>
                                            <span class="payment-mobile-value">{{$payment->invoice->getMesInvoice()}}</span>
                                        </div>
                                        <div>
                                            <span class="payment-mobile-label">Reportado por</span>
                                            <span class="payment-mobile-value">{{$payment->getReportadoPor()}}</span>
                                        </div>
                                        <div>
                                            <span class="payment-mobile-label">Monto</span>
                                            <span class="payment-mobile-value">{{$payment->price_formatted}}</span>
                                        </div>
                                        <div>
                                            <span class="payment-mobile-label">Metodo</span>
                                            <span class="payment-mobile-value">{!!$payment->method_payment_html!!}</span>
                                        </div>
                                        <div>
                                            <span class="payment-mobile-label">Transferencia No.</span>
                                            <span class="payment-mobile-value">{{$payment->no_voucher ?: 'N/A'}}</span>
                                        </div>
                                        @if ($mostrarPlan)
                                        <div>
                                            <span class="payment-mobile-label">Plan</span>
                                            <span class="payment-mobile-value">{{$payment->name_product}}</span>
                                        </div>
                                        <div>
                                            <span class="payment-mobile-label">Direccion</span>
                                            <span class="payment-mobile-value">{{$payment->address_plan}}</span>
                                        </div>
                                        @endif
                                    </div>

                                    @if ($payment->img_voucher)
                                    <div class="mb-3">
                                        <span class="payment-mobile-label">Comprobante</span>
                                        <a class="payment-voucher-thumb" href="{{Storage::url($payment->img_voucher)}}" target="_blank">
                                            <img src="{{Storage::url($payment->img_voucher)}}" alt="Transferencia"/>
                                        </a>
                                    </div>
                                    @endif

                                    @if ($mostrarPendientes)
                                    <div class="payment-mobile-actions">
                                        <div class="payment-action-group">
                                            @can('payments.process')
                                                <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','1')" class="btn-xs btn btn-success" href="#!">Aprobar</a>
                                                <a onclick="ProcesarPago('{{$payment->id}}','{{$payment->no_voucher}}','0')" class="btn-xs btn btn-danger" href="#!">Rechazar</a>
                                            @endcan
                                            @can('payments.destroy')
                                                <a onclick="EliminarPago('{{$payment->id}}','{{$payment->no_voucher}}')" class="btn-xs btn btn-danger" href="#!">Eliminar pago</a>
                                            @endcan
                                        </div>
                                    </div>
                                    @else
                                        @can('payments.destroy')
                                        <div class="payment-mobile-actions">
                                            <div class="payment-action-group">
                                                <a onclick="EliminarPago('{{$payment->id}}','{{$payment->no_voucher}}')" class="btn-xs btn btn-danger" href="#!">Eliminar pago</a>
                                            </div>
                                        </div>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="15">{{ (($invoice_id=='9999999')?'Cargando...':'Sin reportar') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $list_payments->links() }}
    </div>
</div>
