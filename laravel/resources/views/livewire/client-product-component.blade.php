<div> 
    <style>
        .plan-action-inline {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .plan-action-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.42rem 0.82rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .plan-action-chip-info {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #075985;
        }

        .plan-action-chip-info:hover {
            background: #bae6fd;
            color: #0c4a6e;
        }

        .plan-action-chip-ticket {
            background: #ede9fe;
            border-color: #ddd6fe;
            color: #6d28d9;
        }

        .plan-action-chip-ticket:hover {
            background: #ddd6fe;
            color: #5b21b6;
        }

        .plan-action-chip-edit {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .plan-action-chip-edit:hover {
            background: #bfdbfe;
            color: #1e40af;
        }

        .plan-action-menu {
            min-width: 13rem;
            border-radius: 0.95rem;
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
            padding: 0.45rem;
        }

        .plan-action-menu .dropdown-item {
            border-radius: 0.75rem;
            padding: 0.65rem 0.8rem;
        }

        .plan-action-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .plan-action-toolbar .btn {
            border-radius: 999px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
    </style>
    @if (!$modeResume) 
        <div class="tabla"> 
            <div class="row">
                <div class=" mb-3 col-xs-12 col-sm-5">
                    <div class="input-group">
                        <input wire:model="search" type="text" class="form-control" placeholder="Buscar por dirección o código">
                        <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
                    </div>
                </div>
                <div class="mb-3 col-xs-12 col-sm-3">
                    {!! Form::select('filter_zone_id', $zones, null, ['wire:model' => 'filter_zone_id', 'class' => 'form-control', 'placeholder' => 'Todas las zonas']) !!}
                </div>
                <div class=" mb-3 col-xs-12 col-sm-4">
                    @can('clients.edit')
                    <button wire:click="resetInputFields" type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Contratar Plan
                    </button>
                    @endcan
                </div>
            </div>
            <div  class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Código Plan</th>
                            <th>Precio Plan</th>
                            <th>Dirección</th>
                            <th>Referencia</th>
                            <th>Fecha de Instalacion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_products as $product)
                            <tr>
                                <td>{{$product->id}}</td>
                                <td>{{$product->price_formatted}}</td>
                                <td>{{$product->pivot->address}}</td>
                                <td>{{$product->pivot->reference}}</td>
                                <td>{{$product->pivot->date_instalation}}</td>                        
                                <td>{!! $product->pivot->status_html!!}</td> 
                                <td style="width: 10px">
                                    <div class="plan-action-inline">
                                        {{--  href="{{ route('clients.products.details', $product->pivot) }}"  --}}
                                        <a wire:click="detail({{$product->pivot->id}})" type="button" class="plan-action-chip plan-action-chip-info">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                            <span>Detalles</span>
                                        </a>
                                        <a href="{{ route('clients.tickets', ['client' => $client->id, 'client_product_id' => $product->pivot->id]) }}" type="button" class="plan-action-chip plan-action-chip-ticket">
                                            <i class="fas fa-ticket-alt" aria-hidden="true"></i>
                                            <span>Tickets</span>
                                        </a>
                                        @can('clients.edit')
                                        @if ($product->pivot->status != '-1')
                                        <a wire:click="edit({{$product->pivot->id}})" data-bs-toggle="modal" data-bs-target="#exampleModal" type="button" class="plan-action-chip plan-action-chip-edit">
                                            <i class="fas fa-pen" aria-hidden="true"></i>
                                            <span>Editar</span>
                                        </a>
                                        @endif
                                        @endcan
                                        @can('clients.edit')
                                        <button style="opacity: .9" type="button" class="btn btn-info btn-sm btn-secondary dropdown-toggle dropdown-toggle-split rounded-pill px-3" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        @endcan
                                        <div class="dropdown-menu plan-action-menu" aria-labelledby="dropdownMenuReference">
                                            @can('clients.edit')
                                                <a wire:click="edit({{$product->pivot->id}})" data-bs-toggle="modal" data-bs-target="#exampleModal" class="dropdown-item" href="#!"> Editar</a>
                                                @if ($product->pivot->status == '1')
                                                    <a onclick="SuspenderPlan({{$product->pivot->id}},'{{$product->name}}')" class="dropdown-item" href="#!"> Suspender</a>
                                                @elseif ($product->pivot->status == '0')
                                                    <a onclick="ActivarPlan({{$product->pivot->id}},'{{$product->name}}')" class="dropdown-item" href="#!"> Activar</a>
                                                @endif
                                                @if ($product->pivot->status != '-1')
                                                    <a onclick="CancelarPlan({{$product->pivot->id}},'{{$product->name}}')" class="dropdown-item" href="#!"> Cancelar</a>
                                                @endif
                                                <a onclick="EliminarPlan({{$product->pivot->id}},'{{$product->name}}')" class="dropdown-item text-danger" href="#!"> Eliminar</a>
                                            @endcan
                                        </div>
                                    </div>
                                </td>                          
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay resultados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div>
                {{ $list_products->links() }}
            </div>

        </div>
    @endif
    @if ($modeResume)
        <div class="row resume">
            <div class="">
                <div class="plan-action-toolbar">
                @can('payments.advancement')
                @if ($pivot->status!='-1')
                    <button type="button"  data-bs-toggle="modal" data-bs-target="#modalAdvancement" class="btn btn-sm btn-warning" > <i class="fas fa-plus" aria-hidden="true"></i> Agregar pagos</button>
                @endif
                @endcan

                @can('invoices.create')
                @if ($pivot->status!='-1')
                    <button type="button"  data-bs-toggle="modal" data-bs-target="#modalCreateInvoice" class="btn btn-sm btn-success" > <i class="fas fa-plus" aria-hidden="true"></i> Agregar factura</button>
                @endif
                @endcan
                @can('clients.index')
                    <a wire:click="updateMode(false)" class="btn btn-sm btn-info" href="#!"> <i class="fas fa-undo" aria-hidden="true"></i> Ver planes contratados</a>
                @endcan
                <a class="btn btn-sm btn-secondary" href="{{ route('clients.tickets', ['client' => $client->id, 'client_product_id' => $cp]) }}">
                    <i class="fas fa-ticket-alt" aria-hidden="true"></i> Tickets del plan
                </a>
                @can('clients.edit')
                @if ($pivot->status!='-1')
                    <a wire:click="edit({{$cp}})" data-bs-toggle="modal" data-bs-target="#exampleModal" class="btn btn-sm btn-primary" href="#!">
                        <i class="fas fa-edit" aria-hidden="true"></i> Editar plan
                    </a>
                @endif
                @endcan
                @can('clients.edit')
                @if ($pivot->status=='1')
                    <a onclick="SuspenderPlan({{$cp}},'{{$product->name}}')" class="btn btn-sm btn-warning" href="#!"><i class="fas fa-pause" aria-hidden="true"></i> Suspender plan</a>
                @elseif ($pivot->status=='0')
                    <a onclick="ActivarPlan({{$cp}},'{{$product->name}}')" class="btn btn-sm btn-success" href="#!"><i class="fas fa-check" aria-hidden="true"></i> Activar plan</a>
                @endif
                @endcan
                @can('clients.edit')
                @if ($pivot->status!='-1')
                    <a onclick="CancelarPlan({{$cp}},'{{$product->name}}')" class="btn btn-sm btn-secondary" href="#!"><i class="fas fa-ban" aria-hidden="true"></i> Cancelar plan</a>
                @endif
                @endcan
                @can('clients.edit')
                @if ($pivot->status!='-1')
                    <a onclick="EliminarPlan({{$cp}},'{{$product->name}}')" class="btn btn-sm btn-danger" href="#!"><i class="fas fa-trash" aria-hidden="true"></i> Eliminar plan</a>
                @endif
                @endcan
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 fw-bold">Datos del plan</h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Código: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $product->id}}
                            </div>
                        </div>
                        <hr>
        
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Nombre: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $product->name}}
                            </div>
                        </div>
                        <hr>
        
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Velocidad: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $product->velocidad}}
                            </div>
                        </div>
                        <hr>
        
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Precio: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $product->price_formatted}}
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 fw-bold">Datos de contratación</h5>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Dirección: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->address}}
                            </div>
                        </div>
                        <hr>
        
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Referencia: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->reference}}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Coordenadas: </h6>
                            </div>
                            <div class="col-sm-9">
                                @if ($pivot->latitude && $pivot->longitude)
                                    <div>{{ $pivot->latitude }}, {{ $pivot->longitude }}</div>
                                    <div class="plan-nav-actions mt-2">
                                        <a class="plan-nav-btn plan-nav-btn-waze" href="https://waze.com/ul?ll={{ $pivot->latitude }},{{ $pivot->longitude }}&navigate=yes" target="_blank" rel="noopener">
                                            <span class="plan-nav-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 4.5c-4.42 0-8 2.93-8 6.55 0 1.7.79 3.24 2.08 4.4l-.64 2.5 2.68-.93c1.09.43 2.29.68 3.58.68 4.42 0 8-2.93 8-6.55S16.42 4.5 12 4.5Z" fill="white" fill-opacity="0.95"/>
                                                    <circle cx="9.2" cy="11.2" r="1.15" fill="#0284c7"/>
                                                    <circle cx="14.4" cy="11.2" r="1.15" fill="#0284c7"/>
                                                    <path d="M9.2 14.1c1.35 1.18 3.29 1.18 4.64 0" stroke="#0284c7" stroke-width="1.4" stroke-linecap="round"/>
                                                    <circle cx="7.1" cy="16.9" r="1.2" fill="white"/>
                                                    <circle cx="16.9" cy="16.9" r="1.2" fill="white"/>
                                                </svg>
                                            </span>
                                            <span>Abrir en Waze</span>
                                        </a>
                                        <a class="plan-nav-btn plan-nav-btn-google" href="https://www.google.com/maps/dir/?api=1&destination={{ $pivot->latitude }},{{ $pivot->longitude }}" target="_blank" rel="noopener">
                                            <span class="plan-nav-icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 2.8c-3.83 0-6.94 3.02-6.94 6.75 0 4.93 6.94 11.67 6.94 11.67s6.94-6.74 6.94-11.67c0-3.73-3.11-6.75-6.94-6.75Z" fill="#ea4335"/>
                                                    <path d="M12 2.8c3.83 0 6.94 3.02 6.94 6.75 0 1.85-.98 4.03-2.33 6.05L12 9.55V2.8Z" fill="#fbbc04"/>
                                                    <path d="M12 21.22s-6.94-6.74-6.94-11.67c0-2.03.92-3.85 2.37-5.08L12 9.55v11.67Z" fill="#34a853"/>
                                                    <circle cx="12" cy="9.55" r="3.1" fill="white"/>
                                                    <circle cx="12" cy="9.55" r="1.55" fill="#4285f4"/>
                                                </svg>
                                            </span>
                                            <span>Abrir en Google Maps</span>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted">No registradas</span>
                                @endif
                            </div>
                        </div>
                        <hr>
        
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Zona: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->node->zone->id.' / '.$pivot->node->zone->name}}
                            </div>
                        </div>
        
                        <hr><div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Nodo: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->node->id.' / '.$pivot->node->name}}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Fecha de Instalación: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->date_instalation }}
                            </div>
                        </div>
                        <hr><div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Pago Instalacion: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->amount_installation_formatted }}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Pago Anticipado: </h6>
                            </div>
                            <div class="col-sm-9">
                                {!! $pivot->advance_html !!}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Estado: </h6>
                            </div>
                            <div class="col-sm-9">
                                {!! $pivot->status_html !!}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Meses anticipados que le quedan: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->payments_advancement }}
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Fotos: </h6>
                            </div>
                            <div class="col-sm-9">
                                @php($storedPhotos = $pivot->installation_photo_urls)
                                @if (!empty($storedPhotos))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($storedPhotos as $photoUrl)
                                            <a href="{{ $photoUrl }}" target="_blank" rel="noopener">
                                                <img src="{{ $photoUrl }}" alt="Foto de instalacion" style="width: 88px; height: 88px; object-fit: cover; border-radius: 12px; border: 1px solid #dbe2f0;">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No hay fotos adjuntas</span>
                                @endif
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h4><b>Facturación mensual</b></h4>
        @livewire('invoice-component', ['pivot'=>$pivot,'client'=>null])

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="modalCreateInvoice" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCreateInvoiceLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCreateInvoiceLabel">Crear Factura</h5>
                </div>
                <div class="modal-body">
                    {!! Form::open(['id' => 'FormClient', 'method' => 'POST']) !!}
                        <div class="form-group">
                            {!! Form::label('fecha_factura', 'Fecha '); !!}
                            <input wire:model="fecha_factura" type="month" class="form-control">
                            @error('fecha_factura') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            {!! Form::label('estado_factura', 'Estado Factura'); !!}
                            <select wire:model.defer="estado_factura" class="form-control">
                                <option value=''>** Seleccionar **</option>
                                <option {{(('0'==$estado_factura)?'selected':'')}}  value="0">Pendiente</option>
                                <option {{(('1'==$estado_factura)?'selected':'')}}  value="1">Pagada</option>
                                <option {{(('-1'==$estado_factura)?'selected':'')}}  value="-1">Vencida</option>
                            </select>
                            @error('estado_factura') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    {!! Form::close() !!}
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFieldsInvoice" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:click="storeInvoice()" class="btn btn-primary">Crear Factura</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div wire:ignore.self class="modal fade" id="modalAdvancement" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalAdvancementLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdvancementLabel">Agregar pagos adelantados</h5>
                </div>
                <div class="modal-body">
                    {!! Form::open(['id' => 'FormClient', 'method' => 'POST']) !!}
                        <div class="form-group">
                            {!! Form::label('monto_advancement', 'Monto'); !!}
                            <input wire:model="monto_advancement" type="number" class="form-control" >
                            @error('monto_advancement') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            {!! Form::label('meses_advancement', 'Meses de adelanto'); !!}
                            <input wire:model="meses_advancement" type="text" min="1" class="form-control" >
                            @error('meses_advancement') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    {!! Form::close() !!}
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="resetInputFieldsAdvancement" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" wire:loading.attr="disabled" wire:click="storePaymentAdvancement()" class="btn btn-primary">Agregar pago</button>
                </div>
                </div>
            </div>
        </div>

    @endif

    <!-- Modal (crear/editar) disponible en listado y detalles -->
    <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_pagina" id="exampleModalLabel">{{ ($createMode ? 'Contratar Plan' : 'Editar Plan') }}</h5>
            </div>
            <div class="modal-body">
                {!! Form::open(['id' => 'FormZone', 'method' => 'PUT']) !!}
                <div class="form-group">
                    {!! Form::label('product_id', 'Seleccione el plan a contratar'); !!}
                    {!! Form::select('product_id', $products, null, ['wire:model.defer'=>'product_id','class' => 'form-control','placeholder' => 'Selecciona el plan']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('address', 'Direccion *'); !!}
                    {!! Form::text('address', null, ['wire:model.defer'=> 'address', 'class' => 'form-control', 'placeholder'=>'Direccion del usuario', 'autocomplete'=>'off']); !!}
                    @error('address') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    {!! Form::label('reference', 'Referencia *'); !!}
                    {!! Form::text('reference', null, ['wire:model.defer'=> 'reference', 'class' => 'form-control', 'placeholder'=>'Referencia ', 'autocomplete'=>'off']); !!}
                    @error('reference') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('latitude', 'Latitud'); !!}
                            {!! Form::text('latitude', null, ['wire:model.defer'=> 'latitude', 'class' => 'form-control', 'placeholder'=>'Ej. 15.0345678', 'autocomplete'=>'off', 'id' => 'planLatitudeInput']); !!}
                            @error('latitude') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('longitude', 'Longitud'); !!}
                            {!! Form::text('longitude', null, ['wire:model.defer'=> 'longitude', 'class' => 'form-control', 'placeholder'=>'Ej. -91.4356789', 'autocomplete'=>'off', 'id' => 'planLongitudeInput']); !!}
                            @error('longitude') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="obtenerCoordenadasPlan()">
                            Usar ubicacion actual
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="centrarMapaEnCoordenadasActuales()">
                            Centrar mapa
                        </button>
                    </div>
                    <small class="d-block text-muted mt-2">Use el GPS del telefono y luego ajuste el punto en el mapa satelital para mejorar la precision.</small>
                    <div class="plan-nav-actions mt-3">
                        <button type="button" class="plan-nav-btn plan-nav-btn-waze" onclick="openPlanNavigation('waze')">
                            <span class="plan-nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 4.5c-4.42 0-8 2.93-8 6.55 0 1.7.79 3.24 2.08 4.4l-.64 2.5 2.68-.93c1.09.43 2.29.68 3.58.68 4.42 0 8-2.93 8-6.55S16.42 4.5 12 4.5Z" fill="white" fill-opacity="0.95"/>
                                    <circle cx="9.2" cy="11.2" r="1.15" fill="#0284c7"/>
                                    <circle cx="14.4" cy="11.2" r="1.15" fill="#0284c7"/>
                                    <path d="M9.2 14.1c1.35 1.18 3.29 1.18 4.64 0" stroke="#0284c7" stroke-width="1.4" stroke-linecap="round"/>
                                    <circle cx="7.1" cy="16.9" r="1.2" fill="white"/>
                                    <circle cx="16.9" cy="16.9" r="1.2" fill="white"/>
                                </svg>
                            </span>
                            <span>Ir con Waze</span>
                        </button>
                        <button type="button" class="plan-nav-btn plan-nav-btn-google" onclick="openPlanNavigation('google')">
                            <span class="plan-nav-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2.8c-3.83 0-6.94 3.02-6.94 6.75 0 4.93 6.94 11.67 6.94 11.67s6.94-6.74 6.94-11.67c0-3.73-3.11-6.75-6.94-6.75Z" fill="#ea4335"/>
                                    <path d="M12 2.8c3.83 0 6.94 3.02 6.94 6.75 0 1.85-.98 4.03-2.33 6.05L12 9.55V2.8Z" fill="#fbbc04"/>
                                    <path d="M12 21.22s-6.94-6.74-6.94-11.67c0-2.03.92-3.85 2.37-5.08L12 9.55v11.67Z" fill="#34a853"/>
                                    <circle cx="12" cy="9.55" r="3.1" fill="white"/>
                                    <circle cx="12" cy="9.55" r="1.55" fill="#4285f4"/>
                                </svg>
                            </span>
                            <span>Ir con Google Maps</span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mapa de ubicacion</label>
                    <div class="plan-map-shell">
                        <div class="plan-map-header">
                            <div>
                                <div class="plan-map-title">Ubicacion precisa del cliente</div>
                                <div class="plan-map-toolbar text-muted small">
                                    Puede arrastrar el marcador o tocar el mapa para fijar la ubicacion exacta del cliente.
                                </div>
                            </div>
                            <div class="plan-map-controls">
                                <button type="button" id="planMapModeSatellite" class="plan-map-chip active" onclick="switchPlanMapMode('Satelital')">
                                    Satelital
                                </button>
                                <button type="button" id="planMapModeStreet" class="plan-map-chip" onclick="switchPlanMapMode('Calles')">
                                    Calles
                                </button>
                                <button type="button" id="planMapLabelsToggle" class="plan-map-chip plan-map-chip-soft" onclick="togglePlanMapLabels()">
                                    Etiquetas
                                </button>
                                <button type="button" class="plan-map-icon-btn" onclick="zoomPlanMap(1)" aria-label="Acercar mapa">
                                    +
                                </button>
                                <button type="button" class="plan-map-icon-btn" onclick="zoomPlanMap(-1)" aria-label="Alejar mapa">
                                    -
                                </button>
                            </div>
                        </div>
                        <div id="planCoordinateSummary" class="plan-coordinate-summary mb-2">
                            Aun no se han definido coordenadas para este plan.
                        </div>
                        <div id="planPrecisionStatus" class="plan-map-status plan-map-status-muted mb-2">
                            Use el GPS del telefono y luego ajuste el punto manualmente si hace falta.
                        </div>
                        <div wire:ignore id="planMap" class="plan-map-canvas"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('zone_id', 'Seleccione la zona'); !!}
                    {!! Form::select('zone_id', $zones, null, ['wire:model'=>'zone_id','class' => 'form-control','placeholder' => 'Selecciona una zona']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('node_id', 'Seleccione el nodo'); !!}
                    @if (empty($nodes))
                    {!! Form::select('node_id', [], null, ['wire:model.defer'=>'node_id','class' => 'form-control','placeholder' => 'Selecciona primero la zona']) !!}
                    @else
                    {!! Form::select('node_id', $nodes, null, ['wire:model.defer'=>'node_id','class' => 'form-control','placeholder' => 'Selecciona el nodo']) !!}
                    @endif
                </div>

                <div class="form-group">
                    {!! Form::label('date_instalation', 'Fecha de Instalación *'); !!}
                    {!! Form::date('date_instalation', null, ['wire:model.defer'=> 'date_instalation', 'class' => 'form-control',  'autocomplete'=>'off']); !!}
                    @error('date_instalation') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    {!! Form::label('amount_installation', 'Pago Instalación'); !!}
                    {!! Form::text('amount_installation', null, ['wire:model.defer'=> 'amount_installation', 'class' => 'form-control', 'placeholder'=>'0.00 ', 'autocomplete'=>'off']); !!}
                    @error('amount_installation') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    {!! Form::label('advance', 'Pago Anticipado'); !!}
                    <div class="form-check form-check-inline">
                        <input wire:model.defer='advance'  class="form-check-input" type="radio" name="advance" id="inlineRadio1" value="1">
                        <label class="form-check-label" for="inlineRadio1">Si</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input wire:model.defer='advance' checked class="form-check-input" type="radio" name="advance" id="inlineRadio2" value="0">
                        <label class="form-check-label" for="inlineRadio2">No</label>
                    </div>
                    @error('advance') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    {!! Form::label('plan_photos', 'Fotos de instalacion'); !!}
                    <input wire:model="plan_photos" type="file" class="form-control" accept="image/*" capture="environment" multiple>
                    <small class="d-block text-muted mt-2">Puede tomar fotos con la camara del telefono o seleccionar archivos existentes.</small>
                    @error('plan_photos.*') <span class="text-danger">{{ $message }}</span>@enderror
                </div>

                @if (!empty($existing_plan_photos))
                    <div class="form-group">
                        <label>Fotos guardadas</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($existing_plan_photos as $photoPath)
                                <a href="{{ asset('laravel/storage/app/public/'.ltrim($photoPath, '/')) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('laravel/storage/app/public/'.ltrim($photoPath, '/')) }}" alt="Foto guardada" style="width: 84px; height: 84px; object-fit: cover; border-radius: 12px; border: 1px solid #dbe2f0;">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($plan_photos))
                    <div class="form-group">
                        <label>Fotos nuevas</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($plan_photos as $photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="Vista previa" style="width: 84px; height: 84px; object-fit: cover; border-radius: 12px; border: 1px solid #dbe2f0;">
                            @endforeach
                        </div>
                    </div>
                @endif

                {!! Form::close() !!}

            </div>
            <div class="modal-footer">
                <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">{{ ($createMode ? 'Contratar Plan' : 'Guardar Cambios') }}</button>
            </div>
            </div>
        </div>
    </div>
</div>
