<div>   
    <style>
        .client-list-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .client-search-shell {
            flex: 1 1 22rem;
            max-width: 44rem;
        }

        .client-search-shell .input-group {
            background: #ffffff;
            border: 1px solid #dbe2f0;
            border-radius: .9rem;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .client-search-shell .form-control {
            border: none;
            padding-top: .8rem;
            padding-bottom: .8rem;
        }

        .client-search-shell .input-group-text {
            background: #ffffff;
            border: none;
            color: #64748b;
            padding-left: .85rem;
            padding-right: .95rem;
        }

        .client-toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .65rem;
            flex-wrap: wrap;
            flex: 0 1 auto;
        }

        .client-toolbar-actions .btn {
            border-radius: 999px;
            padding: .65rem 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            white-space: nowrap;
        }

        .client-toolbar-actions .btn {
            min-width: 2.85rem;
            min-height: 2.85rem;
            justify-content: center;
        }

        .client-toolbar-actions .btn i {
            margin-right: 0;
            font-size: 1rem;
            line-height: 1;
        }

        .client-toolbar-actions .client-toolbar-btn-import {
            background: #ecfdf5;
            border: 1px solid #86efac;
            color: #15803d;
        }

        .client-toolbar-actions .client-toolbar-btn-create {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1d4ed8;
        }

        .client-action-grid {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            min-width: 15rem;
        }

        .client-action-grid .btn {
            margin: 0;
            min-width: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .client-mobile-row {
            display: none;
        }

        .client-mobile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: .9rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            padding: .8rem .9rem;
        }

        .client-mobile-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .6rem;
        }

        .client-mobile-card-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.2;
        }

        .client-mobile-card-ref {
            font-size: .72rem;
            color: #64748b;
            line-height: 1.15;
            margin-top: .15rem;
        }

        .client-mobile-card-line {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem .75rem;
            padding: .35rem 0;
            border-top: 1px solid #f1f5f9;
        }

        .client-mobile-card-line:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .client-mobile-card-item {
            min-width: 0;
        }

        .client-mobile-card-label {
            display: block;
            font-size: .65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94a3b8;
            margin-bottom: .1rem;
        }

        .client-mobile-card-value {
            display: block;
            font-size: .8rem;
            color: #0f172a;
            line-height: 1.25;
            word-break: break-word;
        }

        .client-mobile-card-actions {
            margin-top: .65rem;
            padding-top: .6rem;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 767.98px) {
            .client-list-toolbar {
                gap: .75rem;
            }

            .client-search-shell,
            .client-toolbar-actions {
                flex-basis: 100%;
                max-width: 100%;
            }

            .client-toolbar-actions {
                justify-content: stretch;
            }

            .client-toolbar-actions .btn {
                flex: 1 1 auto;
                justify-content: center;
            }

            .client-desktop-row {
                display: none;
            }

            .client-mobile-row {
                display: table-row;
            }

            .client-mobile-row td {
                padding: .45rem 0 !important;
                border-top: none !important;
            }

            .client-mobile-card-line {
                grid-template-columns: 1fr 1fr;
            }

            .client-mobile-card-actions .client-action-grid {
                min-width: 0;
                justify-content: flex-start;
            }
        }
    </style>

    <div class="client-list-toolbar">
        <div class="client-search-shell">
            <div class="input-group">
                <input wire:model="search" type="text" class="form-control mayusculas" placeholder="Buscar por nombre, apellido, telefono, direccion o referencia">
                <span class="input-group-text"><i class="fa fa-search" wire:click="updatingSearch" aria-hidden="true"></i></span>
            </div>
        </div>

        <div class="client-toolbar-actions">
            @can('clients.importar')
            <a type="button" class="btn client-toolbar-btn-import" href="{{ route('clients.importar') }}" title="Importar clientes" aria-label="Importar clientes">
                <i class="fas fa-upload" aria-hidden="true"></i>
            </a>
            @endcan
            @can('clients.create')
            <button type="button" class="btn client-toolbar-btn-create btnCrearModal" data-bs-toggle="modal" data-bs-target="#exampleModal" title="Crear cliente" aria-label="Crear cliente">
                <i class="fas fa-plus" aria-hidden="true"></i>
            </button>
            @endcan
        </div>
    </div>

    <div class="table-responsive2">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>DPI</th>
                    <th>Correo</th>
                    <th>Telefono</th>
                    <th>Dirección</th>
                    <th>Referencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($list_clients as $client)
                    @if (!is_null($client))
                        <tr class="client-desktop-row">
                            <td data-th="Nombre" >{{$client->user->name}}</td>
                            <td data-th="Apellido">{{$client->user->lastname}}</td>
                            <td data-th="DPI">{{$client->user->dni}}</td>
                            <td data-th="Correo">{{$client->user->email}}</td>
                            <td data-th="Telefono">{{$client->user->phone}}</td>
                            <td data-th="Dirección">{{$client->user->address}}</td>
                            <td data-th="Referencia">{{$client->reference}}</td>
                            <td data-th="Acciones" >
                                <div class="client-action-grid">
                                    <a href="{{ route('clients.show', $client) }}" type="button" class="btn btn-sm btn-success" title="Ver cliente" aria-label="Ver cliente">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>
                                    @can('clients.index')
                                    <a href="{{ route('clients.products', $client) }}" class="btn btn-sm btn-primary" title="Ver planes" aria-label="Ver planes">
                                        <i class="fas fa-box-open" aria-hidden="true"></i>
                                    </a>
                                    @endcan
                                    @can('clients.edit')
                                    <a wire:click="edit({{$client->id}})" class="btn btn-sm btn-warning" href="#!" data-bs-toggle="modal" data-bs-target="#exampleModal" title="Editar cliente" aria-label="Editar cliente">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    @endcan
                                    @can('clients.destroy')
                                    <a onclick="EliminarClient({{$client->id}},'{{$client->user->name_complete}}')" class="btn btn-sm btn-danger" href="#!" title="Eliminar cliente" aria-label="Eliminar cliente">
                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                    </a>
                                    @endcan
                                    @can('clients.send.recordatorio')
                                    <a wire:click.prevent="cargarDataRecordatorio({{$client->id}})" class="btn btn-sm btn-secondary" href="#!" title="Enviar recordatorio" aria-label="Enviar recordatorio">
                                        <i class="fas fa-bell" aria-hidden="true"></i>
                                    </a>
                                    <a wire:click.prevent="cargarDataAvisoMora({{$client->id}})" class="btn btn-sm btn-dark" href="#!" title="Enviar aviso de mora" aria-label="Enviar aviso de mora">
                                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        <tr class="client-mobile-row">
                            <td colspan="8">
                                <div class="client-mobile-card">
                                    <div class="client-mobile-card-header">
                                        <div>
                                            <h6 class="client-mobile-card-title">{{$client->user->name_complete}}</h6>
                                            <div class="client-mobile-card-ref">Referencia: {{$client->reference}}</div>
                                        </div>
                                    </div>

                                    <div class="client-mobile-card-line">
                                        <div class="client-mobile-card-item">
                                            <span class="client-mobile-card-label">Telefono</span>
                                            <span class="client-mobile-card-value">{{$client->user->phone}}</span>
                                        </div>
                                        <div class="client-mobile-card-item">
                                            <span class="client-mobile-card-label">DPI</span>
                                            <span class="client-mobile-card-value">{{$client->user->dni}}</span>
                                        </div>
                                    </div>

                                    <div class="client-mobile-card-line">
                                        <div class="client-mobile-card-item">
                                            <span class="client-mobile-card-label">Correo</span>
                                            <span class="client-mobile-card-value">{{$client->user->email}}</span>
                                        </div>
                                        <div class="client-mobile-card-item">
                                            <span class="client-mobile-card-label">Direccion</span>
                                            <span class="client-mobile-card-value">{{$client->user->address}}</span>
                                        </div>
                                    </div>

                                    <div class="client-mobile-card-actions">
                                        <div class="client-action-grid">
                                            <a href="{{ route('clients.show', $client) }}" type="button" class="btn btn-sm btn-success" title="Ver cliente" aria-label="Ver cliente">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                            @can('clients.index')
                                            <a href="{{ route('clients.products', $client) }}" class="btn btn-sm btn-primary" title="Ver planes" aria-label="Ver planes">
                                                <i class="fas fa-box-open" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                            @can('clients.edit')
                                            <a wire:click="edit({{$client->id}})" class="btn btn-sm btn-warning" href="#!" data-bs-toggle="modal" data-bs-target="#exampleModal" title="Editar cliente" aria-label="Editar cliente">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                            @can('clients.destroy')
                                            <a onclick="EliminarClient({{$client->id}},'{{$client->user->name_complete}}')" class="btn btn-sm btn-danger" href="#!" title="Eliminar cliente" aria-label="Eliminar cliente">
                                                <i class="fas fa-trash" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                            @can('clients.send.recordatorio')
                                            <a wire:click.prevent="cargarDataRecordatorio({{$client->id}})" class="btn btn-sm btn-secondary" href="#!" title="Enviar recordatorio" aria-label="Enviar recordatorio">
                                                <i class="fas fa-bell" aria-hidden="true"></i>
                                            </a>
                                            <a wire:click.prevent="cargarDataAvisoMora({{$client->id}})" class="btn btn-sm btn-dark" href="#!" title="Enviar aviso de mora" aria-label="Enviar aviso de mora">
                                                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay resultados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        {{ $list_clients->links() }}
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ (($createMode)?'Crear Cliente':'Editar Cliente')}}</h5>
            </div>
            <div class="modal-body">
                
                @if($createMode)
                {!! Form::open(['id' => 'FormClient', 'method' => 'POST']) !!}
                @else
                {!! Form::model($client,['id' => 'FormClient', 'method' => 'PUT']) !!}
                @endif
                    @include('yield.input-user')
                    <div class="form-group">
                        {!! Form::label('ip', 'IP '); !!}
                        {!! Form::text('ip', null, ['wire:model.defer'=> 'ip', 'class' => 'form-control', 'placeholder'=>'IP ', 'autocomplete'=>'off']); !!}
                        @error('ip') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        {!! Form::label('clave_wifi', 'Clave Wifi '); !!}
                        {!! Form::text('clave_wifi', null, ['wire:model.defer'=> 'clave_wifi', 'class' => 'form-control', 'placeholder'=>'Clave Wifi ', 'autocomplete'=>'off']); !!}
                        @error('clave_wifi') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        {!! Form::label('reference', 'Referencia *'); !!}
                        {!! Form::text('reference', null, ['wire:model.defer'=> 'reference', 'class' => 'form-control mayusculas', 'placeholder'=>'Referencia ', 'autocomplete'=>'off']); !!}
                        @error('reference') <span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                @if ($createMode)
                <button type="button" wire:loading.attr="disabled" wire:click="store()" class="btn btn-primary">Crear Cliente</button>
                @else
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Editar Cliente</button>
                @endif
            </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="zoneModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="zoneModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="zoneModalLabel">Actualizar rol usuario</h5>
            </div>
            <div class="modal-body">
                
                @if($editZoneMode)

                {!! Form::model($client,['id' => 'FormClient', 'method' => 'PUT']) !!}

                <h4>Listado de zones</h4>
                @forelse ($zonesAll  as $index => $rol)
                    <li class="list-group-item ">
                        <div class="custom-control custom-checkbox ">
                            {!! Form::checkbox('zones[]', $rol->id, null, ['wire:model.defer'=>'zonesClient.'.$rol->id,'class'=>'custom-control-input','id'=>'zone'.$rol->id]) !!}
                            {!! Form::label('zone'.$rol->id, $rol->name.': '.$rol->description, ['class'=>'custom-control-label ']) !!}
                        </div>
                    </li>
                @empty
                @endforelse
                {!! Form::close() !!}
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:loading.attr="disabled" wire:click="updateZone()" class="btn btn-primary">Actualizar</button>
            </div>
            </div>
        </div>
    </div>

    <!-- ModalDataRecordatorio -->
    <div wire:ignore.self class="modal fade" id="ModalDataRecordatorio" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalDataRecordatorioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalDataRecordatorioLabel">Enviar Recordatorio de Vencimiento</h5>
                </div>
                <div class="modal-body">
                    @if( !is_null($clienteMora))
                        <div class="mb-3">
                            <b>Cliente seleccionado:</b> {{$clienteMora->user->name_complete}}<br>
                            <b>Telefono:</b> {{$clienteMora->user->phone}}
                        </div>
                        @forelse ($clienteMora->client_products as $plan_contratado)
                            <hr>
                            <b>Plan:</b> {{$plan_contratado->product->name}}<br>
                            <b>Referencia:</b> {{$plan_contratado->reference}}<br><br>
                            <?php 
                            $cellphone = $clienteMora->user->phone;
                            $clientName = $clienteMora->user->name;
                            $actual = $clienteMora->getFirtsAndLastMonthDeudaAndPrice($plan_contratado->id,['0']) ?>

                            @if($actual['Valor']>0)
                                <div class="mb-2">
                                    <b>Factura:</b> {{$actual['Meses']}}<br>
                                    <b>Monto:</b> {{ formatWhatsAppCurrency($actual['Valor']) }}<br>
                                    @if(!empty($actual['Vencimiento']))
                                        <b>Vence:</b> {{$actual['Vencimiento']}}<br>
                                    @endif
                                </div>
                                <button onclick="EnviarRecordatorioFacturaVencer(@js($clientName), @js($actual['Meses']), {{$actual['Valor']}}, {{$cellphone}}, @js($actual['Vencimiento']))" class="btn btn-info">Enviar Recordatorio</button>
                            @else
                                No tiene factura actual pendiente para enviar recordatorio.
                            @endif
                        @empty
                        El cliente no tiene planes
                        @endforelse
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ModalAvisoMora -->
    <div wire:ignore.self class="modal fade" id="ModalAvisoMora" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ModalAvisoMoraLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalAvisoMoraLabel">Enviar Aviso de Mora</h5>
                </div>
                <div class="modal-body">
                    @if( !is_null($clienteMora))
                        <div class="mb-3">
                            <b>Cliente seleccionado:</b> {{$clienteMora->user->name_complete}}<br>
                            <b>Telefono:</b> {{$clienteMora->user->phone}}
                        </div>
                        @forelse ($clienteMora->client_products as $plan_contratado)
                            <hr>
                            <b>Plan:</b> {{$plan_contratado->product->name}}<br>
                            <b>Referencia:</b> {{$plan_contratado->reference}}<br><br>
                            <?php
                            $cellphone = $clienteMora->user->phone;
                            $clientName = $clienteMora->user->name;
                            $vencido = $clienteMora->getFirtsAndLastMonthDeudaAndPrice($plan_contratado->id,['-1']) ?>

                            @if($vencido['Valor']>0)
                                <div class="mb-2">
                                    <b>Meses vencidos:</b> {{$vencido['Meses']}}<br>
                                    <b>Total deuda:</b> {{ formatWhatsAppCurrency($vencido['Valor']) }}<br>
                                </div>
                                <button onclick="EnviarRecordatorioMoroso(@js($clientName), @js($vencido['Meses']), {{$vencido['Valor']}}, {{$cellphone}})" class="btn btn-warning">Enviar aviso de mora</button>
                            @else
                                No tiene facturas vencidas para enviar aviso de mora.
                            @endif
                        @empty
                        El cliente no tiene planes
                        @endforelse
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
