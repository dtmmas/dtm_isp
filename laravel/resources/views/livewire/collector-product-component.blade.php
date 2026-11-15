<div> 
    @if (!$modeResume) 
        <div class="tabla"> 
            <div class="row">
                <div class=" mb-3 col-xs-12 col-sm-8">
                    <div class="input-group">
                        <input wire:model="search" type="text mayusculas" class="form-control" placeholder="Buscar por dirección o código">
                        <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
                    </div>
                </div>
                <div class=" mb-3 col-xs-12 col-sm-4">
                    @can('collectors.create')
                    <button type="button" class="btn btn-primary btnCrearModal float-end" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                            <th>Fecha de Instalacion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list_products as $product)
                            <tr>
                                {{dd($product)}}
                                <td>{{$product->code}}</td>
                                <td>{{$product->price_formatted}}</td>
                                <td>{{$product->pivot->address}}</td>
                                <td>{{$product->pivot->date_instalation}}</td>                        
                                <td>{!! $product->pivot->status_html!!}</td> 
                                <td style="width: 10px">
                                    <div class="btn-group " zone="group">
                                        {{--  href="{{ route('clients.products.details', $product->pivot) }}"  --}}
                                        <a wire:click="detail({{$product->pivot->id}})"  type="button" class="btn btn-sm btn-info btn-secondary" >
                                            Detalles
                                        </a>
                                        @can('collectors.edit')
                                        <button style="opacity: .8" type="button" class="btn btn-info btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        @endcan
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuReference">
                                            @can('collectors.edit')
                                                <a onclick="DesactivarPlan({{$product->pivot->id}},'{{$product->name}}')" class="dropdown-item" href="#!"> Cancelar</a>
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

            <!-- Modal -->
            <div wire:ignore.self class="modal fade" id="exampleModal" data-bs-focus="false" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title titulo_pagina" id="exampleModalLabel">Asociar Zona</h5>
                    </div>
                    <div class="modal-body">
                        {!! Form::open(['id' => 'FormZone', 'method' => 'PUT']) !!}
                        <div class="form-group">
                            {!! Form::label('product_id', 'Seleccione el plan a contratar'); !!}
                            {!! Form::select('product_id', $products, null, ['wire:model.defer'=>'product_id','class' => 'form-control','placeholder' => 'Selecciona el plan']) !!}
                        </div>

                        <div class="form-group">
                            {!! Form::label('address', 'Direccion *'); !!}
                            {!! Form::text('address', null, ['wire:model.defer'=> 'address', 'class' => 'form-control mayusculas', 'placeholder'=>'Direccion del usuario', 'autocomplete'=>'off']); !!}
                            @error('address') <span class="text-danger">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            {!! Form::label('reference', 'Referencia *'); !!}
                            {!! Form::text('reference', null, ['wire:model.defer'=> 'reference', 'class' => 'form-control mayusculas', 'placeholder'=>'Referencia ', 'autocomplete'=>'off']); !!}
                            @error('reference') <span class="text-danger">{{ $message }}</span>@enderror
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

                        {!! Form::close() !!}

                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="resetInputFields" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" wire:loading.attr="disabled" wire:click="update()" class="btn btn-primary">Contratar Plan</button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if ($modeResume)
        <div class="row resume">
            <div class="">
                @can('collectors.index')
                    <a wire:click="updateMode(false)" class="float-end btn btn-sm btn-info" href="#!"> ver planes contratados</a>
                @endcan
                @can('collectors.edit')
                @if ($pivot->status!='-1')
                    <a onclick="DesactivarPlan({{$cp}},'{{$product->name}}')" class="float-end btn btn-sm btn-danger me-3" href="#!"> Cancelar plan</a>
                @endif
                @endcan
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
                                {{ $product->code}}
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
                                <h5 class="mb-0 fw-bold">Datos del contratación</h5>
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
                            <h6 class="mb-0 fw-bold">Zona: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->node_id}}
                            </div>
                        </div>
        
                        <hr><div class="row">
                            <div class="col-sm-3">
                            <h6 class="mb-0 fw-bold">Nodo: </h6>
                            </div>
                            <div class="col-sm-9">
                                {{ $pivot->node_id}}
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
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h4><b>Facturación mensual</b></h4>
        @livewire('invoice-component', ['pivot'=>$pivot,'client'=>null])

    @endif
</div>
