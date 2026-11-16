<div>
    <div class="card">
        <div class="card-body wire:ignore.self">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item"><a href="#navpills-1" class="nav-link {{(($tab=='empresa')?'active show':'')}}" data-toggle="tab" aria-expanded="false">Informacion Empresa</a>
                </li>
                </li>
            </ul>
            <div class="tab-content br-n pn">
                <div id="navpills-1" class="tab-pane {{(($tab=='empresa')?'active show':'')}}">
                    @if (session('success_restore'))
                        <div class="alert alert-success">
                            {{ session('success_restore') }}
                        </div>
                    @endif

                    {!! Form::open(['id' => 'FormEmpresa', 'method' => 'POST', 'files' => true]) !!}
                        <div class="form-row">
                           
                            <div class="form-group col-md-12">
                                {!! Form::label('nombre_empresa', 'Nombre de la empresa'); !!}
                                {!! Form::text('nombre_empresa', null, ['wire:model'=> 'nombre_empresa',  'class' => 'form-control']); !!}
                                @error('nombre_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('direccion_empresa', 'Direccion'); !!}
                                {!! Form::text('direccion_empresa', null, ['wire:model'=> 'direccion_empresa',  'class' => 'form-control', 'placeholder'=>'']); !!}
                                @error('direccion_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('telefono_empresa', 'Telefono'); !!}
                                {!! Form::text('telefono_empresa', null, ['wire:model'=> 'telefono_empresa', 'class' => 'form-control ', 'placeholder'=>'']); !!}
                                @error('telefono_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('nit_empresa', 'NIT'); !!}
                                {!! Form::text('nit_empresa', null, ['wire:model'=> 'nit_empresa',  'class' => 'form-control numerico', 'placeholder'=>'']); !!}
                                @error('nit_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                {!! Form::label('slogan_empresa', 'Slogan'); !!}
                                {!! Form::text('slogan_empresa', null, ['wire:model'=> 'slogan_empresa',  'class' => 'form-control', 'placeholder'=>'']); !!}
                                @error('slogan_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group col-md-12">
                                {!! Form::label('logo_empresa', 'Cargar Logo'); !!}
                                {!! Form::file('logo_empresa',  ['wire:model'=> 'logo_empresa', 'class' => 'form-control', 'placeholder'=>'Cargar Logo', 'accept'=>'image/*']); !!}
                                @error('logo_empresa') <span class="text-danger">{{ $message }}</span>@enderror
                                <span wire:loading wire:target="logo_empresa" class="text-danger" > Cargando...</span>
                            </div>
                            
                            <div class="form-group col-md-12 text-center">
                                <button type="button" wire:loading.attr="disabled"  wire:target="logo_empresa,guardarEmpresa"  wire:click="guardarEmpresa()" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    {!! Form::close() !!}

                    <hr>

                    <div class="card border mt-4">
                        <div class="card-body">
                            @php
                                $fontFamilyOptions = [
                                    'sans' => 'Sans',
                                    'serif' => 'Serif',
                                    'mono' => 'Mono',
                                ];
                                $fontSizeOptions = [10, 11, 12, 14, 16, 18, 20, 24];
                                $fontWeightOptions = [
                                    'normal' => 'Normal',
                                    'medium' => 'Mediana',
                                    'full' => 'Full',
                                ];
                                $styleSectionLabels = [
                                    'encabezado' => 'Encabezado',
                                    'logo' => 'Logo / nombre principal',
                                    'titulo' => 'Titulo',
                                    'cliente' => 'Datos del cliente',
                                    'concepto' => 'Concepto',
                                    'pie' => 'Pie de pagina',
                                ];
                                $ticketPreviewDividerStyle = 'border-top-style: '.$ticket_separador_estilo.'; border-top-width: '.ticketDividerWidthCss($ticket_separador_grosor).'; border-top-color: #000000;';
                            @endphp
                            <h4 class="mb-2">Configuracion de ticket o recibo</h4>
                            <p class="text-muted mb-3">
                                Ajusta como se imprimen los recibos de pago: titulo del documento, cantidad de copias, mensaje final y los datos que se mostraran.
                            </p>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    {!! Form::label('ticket_documento_titulo', 'Titulo del documento'); !!}
                                    {!! Form::text('ticket_documento_titulo', null, ['wire:model'=> 'ticket_documento_titulo', 'class' => 'form-control', 'placeholder' => 'RECIBO']); !!}
                                    @error('ticket_documento_titulo') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    {!! Form::label('ticket_copias', 'Cantidad de copias'); !!}
                                    <select wire:model="ticket_copias" class="form-control">
                                        <option value="1">1 copia</option>
                                        <option value="2">2 copias</option>
                                        <option value="3">3 copias</option>
                                    </select>
                                    @error('ticket_copias') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_rollo_ancho_mm">Rollo de impresion</label>
                                    <select wire:model="ticket_rollo_ancho_mm" id="ticket_rollo_ancho_mm" class="form-control">
                                        <option value="58">58 mm</option>
                                        <option value="72">72 mm</option>
                                        <option value="80">80 mm</option>
                                        <option value="110">110 mm</option>
                                        <option value="112">112 mm</option>
                                    </select>
                                    @error('ticket_rollo_ancho_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_margen_interno_mm">Margen interno</label>
                                    <select wire:model="ticket_margen_interno_mm" id="ticket_margen_interno_mm" class="form-control">
                                        <option value="2">2 mm</option>
                                        <option value="4">4 mm</option>
                                        <option value="6">6 mm</option>
                                    </select>
                                    @error('ticket_margen_interno_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_margen_superior_mm">Margen superior</label>
                                    <select wire:model="ticket_margen_superior_mm" id="ticket_margen_superior_mm" class="form-control">
                                        <option value="2">2 mm</option>
                                        <option value="4">4 mm</option>
                                        <option value="6">6 mm</option>
                                        <option value="8">8 mm</option>
                                    </select>
                                    @error('ticket_margen_superior_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_margen_derecho_mm">Margen derecho</label>
                                    <select wire:model="ticket_margen_derecho_mm" id="ticket_margen_derecho_mm" class="form-control">
                                        <option value="2">2 mm</option>
                                        <option value="4">4 mm</option>
                                        <option value="6">6 mm</option>
                                        <option value="8">8 mm</option>
                                    </select>
                                    @error('ticket_margen_derecho_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_margen_inferior_mm">Margen inferior</label>
                                    <select wire:model="ticket_margen_inferior_mm" id="ticket_margen_inferior_mm" class="form-control">
                                        <option value="2">2 mm</option>
                                        <option value="4">4 mm</option>
                                        <option value="6">6 mm</option>
                                        <option value="8">8 mm</option>
                                    </select>
                                    @error('ticket_margen_inferior_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_margen_izquierdo_mm">Margen izquierdo</label>
                                    <select wire:model="ticket_margen_izquierdo_mm" id="ticket_margen_izquierdo_mm" class="form-control">
                                        <option value="2">2 mm</option>
                                        <option value="4">4 mm</option>
                                        <option value="6">6 mm</option>
                                        <option value="8">8 mm</option>
                                    </select>
                                    @error('ticket_margen_izquierdo_mm') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_separador_estilo">Separador</label>
                                    <select wire:model="ticket_separador_estilo" id="ticket_separador_estilo" class="form-control">
                                        <option value="dashed">Guiones</option>
                                        <option value="solid">Linea solida</option>
                                        <option value="dotted">Punteado</option>
                                    </select>
                                    @error('ticket_separador_estilo') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="ticket_separador_grosor">Grosor del separador</label>
                                    <select wire:model="ticket_separador_grosor" id="ticket_separador_grosor" class="form-control">
                                        <option value="normal">Normal</option>
                                        <option value="medium">Mediana</option>
                                        <option value="high">Alta</option>
                                        <option value="full">Full</option>
                                    </select>
                                    @error('ticket_separador_grosor') <span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                                <div class="form-group col-md-12">
                                    {!! Form::label('ticket_mensaje_final', 'Mensaje final del ticket'); !!}
                                    {!! Form::text('ticket_mensaje_final', null, ['wire:model'=> 'ticket_mensaje_final', 'class' => 'form-control', 'placeholder' => 'Gracias por su pago']); !!}
                                    @error('ticket_mensaje_final') <span class="text-danger">{{ $message }}</span>@enderror
                                    <small class="text-muted">Este mensaje aparecera al final del recibo impreso.</small>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_mostrar_logo" type="checkbox" class="form-check-input" id="ticket_mostrar_logo">
                                        <label class="form-check-label" for="ticket_mostrar_logo">Mostrar logo de la empresa</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_mostrar_linea_corte" type="checkbox" class="form-check-input" id="ticket_mostrar_linea_corte">
                                        <label class="form-check-label" for="ticket_mostrar_linea_corte">Mostrar linea de corte entre copias</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_mostrar_dpi" type="checkbox" class="form-check-input" id="ticket_mostrar_dpi">
                                        <label class="form-check-label" for="ticket_mostrar_dpi">Mostrar DPI del cliente</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_mostrar_direccion_plan" type="checkbox" class="form-check-input" id="ticket_mostrar_direccion_plan">
                                        <label class="form-check-label" for="ticket_mostrar_direccion_plan">Mostrar direccion del plan</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="ticket_logo_ancho" class="mb-1">Tamano del logo</label>
                                        <select wire:model="ticket_logo_ancho" id="ticket_logo_ancho" class="form-control form-control-sm">
                                            <option value="50">Pequeno</option>
                                            <option value="80">Mediano</option>
                                            <option value="110">Grande</option>
                                            <option value="140">Extra grande</option>
                                        </select>
                                        @error('ticket_logo_ancho') <span class="text-danger">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_mostrar_observacion" type="checkbox" class="form-check-input" id="ticket_mostrar_observacion">
                                        <label class="form-check-label" for="ticket_mostrar_observacion">Mostrar observaciones del pago</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input wire:model="ticket_auto_imprimir" type="checkbox" class="form-check-input" id="ticket_auto_imprimir">
                                        <label class="form-check-label" for="ticket_auto_imprimir">Imprimir automaticamente al abrir el recibo</label>
                                    </div>
                                </div>
                            </div>

                            <div class="ticket-style-grid mt-4">
                                @foreach ($styleSectionLabels as $sectionKey => $sectionLabel)
                                    <div class="ticket-style-card">
                                        <h6>{{ $sectionLabel }}</h6>
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Tipo de letra</label>
                                            <select wire:model="ticket_estilos.{{ $sectionKey }}.familia" class="form-control form-control-sm">
                                                @foreach ($fontFamilyOptions as $fontValue => $fontLabel)
                                                    <option value="{{ $fontValue }}">{{ $fontLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Tamano</label>
                                            <select wire:model="ticket_estilos.{{ $sectionKey }}.tamano" class="form-control form-control-sm">
                                                @foreach ($fontSizeOptions as $fontSize)
                                                    <option value="{{ $fontSize }}">{{ $fontSize }} px</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-1">Negrita</label>
                                            <select wire:model="ticket_estilos.{{ $sectionKey }}.peso" class="form-control form-control-sm">
                                                @foreach ($fontWeightOptions as $weightValue => $weightLabel)
                                                    <option value="{{ $weightValue }}">{{ $weightLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="ticket-preview-shell mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Vista previa</h5>
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="badge badge-info">Se imprimiran {{ (int) $ticket_copias }} {{ (int) $ticket_copias === 1 ? 'copia' : 'copias' }}</span>
                                        <button type="button" id="ticket-preview-print-button" class="btn btn-sm btn-outline-primary">
                                            Imprimir vista previa
                                        </button>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Ejemplo aproximado de como saldra el ticket al imprimir.</p>

                                <div id="ticket-preview-print-area" class="ticket-preview-paper" style="max-width: {{ (int) $ticket_rollo_ancho_mm }}mm; padding: {{ (int) $ticket_margen_superior_mm }}mm {{ (int) $ticket_margen_derecho_mm }}mm {{ (int) $ticket_margen_inferior_mm }}mm {{ (int) $ticket_margen_izquierdo_mm }}mm;">
                                    <div class="ticket-preview-company">
                                        @if ($ticket_mostrar_logo)
                                            <div class="ticket-preview-logo">
                                                <img src="{{ asset(logoEmpresaConfig()) }}" alt="{{ $nombre_empresa ?: 'Logo empresa' }}" style="width: {{ (int) $ticket_logo_ancho }}px;">
                                            </div>
                                        @endif
                                        <strong style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'logo') }}">{{ $nombre_empresa ?: 'Nombre de la empresa' }}</strong>
                                        @if (!empty($slogan_empresa))
                                            <span style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'encabezado') }}">{{ $slogan_empresa }}</span>
                                        @endif
                                        @if (!empty($nit_empresa))
                                            <span style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'encabezado') }}">NIT: {{ $nit_empresa }}</span>
                                        @endif
                                        @if (!empty($direccion_empresa))
                                            <span style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'encabezado') }}">Dir: {{ $direccion_empresa }}</span>
                                        @endif
                                        @if (!empty($telefono_empresa))
                                            <span style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'encabezado') }}">Tel: {{ $telefono_empresa }}</span>
@endif

                                    <div class="ticket-preview-divider" style="{{ $ticketPreviewDividerStyle }}"></div>
                                    <div class="text-center" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'titulo') }}">
                                        <strong>{{ $ticket_documento_titulo ?: 'RECIBO' }} No. 000123</strong><br>
                                        <small>{{ now()->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <div class="ticket-preview-divider" style="{{ $ticketPreviewDividerStyle }}"></div>

                                    <div class="ticket-preview-block" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'cliente') }}">
                                        <div class="ticket-preview-row ticket-preview-row-client">
                                            <span><strong>Cliente:</strong></span>
                                            <span class="ticket-preview-row-value">Juan Perez</span>
                                        </div>
                                        @if ($ticket_mostrar_dpi)
                                            <div class="ticket-preview-row ticket-preview-row-client">
                                                <span><strong>DPI:</strong></span>
                                                <span class="ticket-preview-row-value">1234 56789 0101</span>
                                            </div>
                                        @endif
                                        <div class="ticket-preview-row ticket-preview-row-client">
                                            <span><strong>Plan:</strong></span>
                                            <span class="ticket-preview-row-value">Internet residencial 20MB</span>
                                        </div>
                                        @if ($ticket_mostrar_direccion_plan)
                                            <div class="ticket-preview-row ticket-preview-row-client">
                                                <span><strong>Direccion:</strong></span>
                                                <span class="ticket-preview-row-value">Zona centro, sector ejemplo</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ticket-preview-divider" style="{{ $ticketPreviewDividerStyle }}"></div>
                                    <div class="ticket-preview-concept-pill" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'concepto') }}">
                                        <span class="ticket-preview-concept-pill-label">Concepto:</span>
                                        <span class="ticket-preview-row-value ticket-preview-row-value-strong">Pago del mes</span>
                                    </div>
                                    <div class="ticket-preview-row" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'concepto') }}">
                                        <span>Forma de pago:</span>
                                        <span>Efectivo</span>
                                    </div>
                                    <div class="ticket-preview-total-pill" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'concepto') }}">
                                        <span class="ticket-preview-total-pill-label">Monto:</span>
                                        <strong class="ticket-preview-amount-value">Q 250.00</strong>
                                    </div>

                                    @if ($ticket_mostrar_observacion)
                                        <div class="ticket-preview-block mt-2" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'concepto') }}">
                                            <strong>Observacion:</strong> Pago recibido sin novedad.
                                        </div>
                                    @endif

                                    @if (!empty($ticket_mensaje_final))
                                        <div class="ticket-preview-divider" style="{{ $ticketPreviewDividerStyle }}"></div>
                                        <div class="text-center" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'pie') }}">
                                            <strong>{{ $ticket_mensaje_final }}</strong>
                                        </div>
                                    @endif

                                    <div class="ticket-preview-divider" style="{{ $ticketPreviewDividerStyle }}"></div>
                                    <div class="text-center text-muted small" style="{{ ticketSectionStyleCss((object) ['styles' => $ticket_estilos], 'pie') }}">
                                        {{ (int) $ticket_copias === 1 ? 'ORIGINAL' : 'COPIA CLIENTE' }}
                                        @if ($ticket_auto_imprimir)
                                            <br><span>Apertura con impresion automatica activa</span>
                                        @endif
                                    </div>

                                    @if ((int) $ticket_copias > 1 && $ticket_mostrar_linea_corte)
                                        <div class="ticket-preview-cutline">
                                            CORTE AQUI
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group col-md-12 text-center mt-3 mb-0">
                                <button type="button" wire:loading.attr="disabled" wire:target="guardarTicket" wire:click="guardarTicket()" class="btn btn-primary">
                                    Guardar configuracion del ticket
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="card border mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Auditoria de meses en facturacion mensual</h4>
                            <p class="text-muted mb-3">
                                Revisa los planes activos y detecta meses faltantes en la facturacion mensual. Si un plan tiene huecos entre el mes de inicio y los meses ya facturados, aqui se listaran y podras crear las facturas faltantes.
                            </p>

                            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                <button type="button" wire:click="auditarMesesFacturacion" wire:loading.attr="disabled" wire:target="auditarMesesFacturacion,crearMesesFaltantes,crearTodosLosMesesFaltantes" class="btn btn-info">
                                    Auditar meses faltantes
                                </button>
                                <button type="button" wire:click="crearTodosLosMesesFaltantes" wire:loading.attr="disabled" wire:target="auditarMesesFacturacion,crearMesesFaltantes,crearTodosLosMesesFaltantes" class="btn btn-warning" @if (empty($invoice_audit_results)) disabled @endif>
                                    Crear todos los meses faltantes
                                </button>
                                <span wire:loading wire:target="auditarMesesFacturacion,crearMesesFaltantes,crearTodosLosMesesFaltantes" class="text-primary">
                                    Procesando auditoria...
                                </span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Planes revisados</small>
                                        <strong>{{ (int) ($invoice_audit_summary['plans_checked'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Planes con problema</small>
                                        <strong>{{ (int) ($invoice_audit_summary['plans_with_issues'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Meses faltantes</small>
                                        <strong>{{ (int) ($invoice_audit_summary['missing_months'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Ultima auditoria</small>
                                        <strong>{{ $invoice_audit_summary['last_run_at'] ?? 'Sin ejecutar' }}</strong>
                                    </div>
                                </div>
                            </div>

                            @if (!empty($invoice_audit_results))
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Plan</th>
                                                <th>Inicio auditoria</th>
                                                <th>Meses registrados</th>
                                                <th>Meses faltantes</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice_audit_results as $auditItem)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $auditItem['client_name'] }}</strong>
                                                        @if (!empty($auditItem['address']))
                                                            <br><small class="text-muted">{{ $auditItem['address'] }}</small>
                                                        @elseif (!empty($auditItem['reference']))
                                                            <br><small class="text-muted">{{ $auditItem['reference'] }}</small>
                                                        @endif
                                                        @if (!empty($auditItem['reactivated_at_label']))
                                                            <br><small class="text-info">Reanudado: {{ $auditItem['reactivated_at_label'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $auditItem['plan_name'] }}</td>
                                                    <td>{{ $auditItem['start_month_label'] }}</td>
                                                    <td>
                                                        @foreach ($auditItem['registered_months'] as $registeredMonth)
                                                            <span class="badge border mb-1" style="background:#f8fafc;color:#0f172a;border-color:#cbd5e1;">{{ $registeredMonth }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @foreach ($auditItem['missing_months'] as $missingMonth)
                                                            <span class="badge mb-1" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">{{ $missingMonth }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ $auditItem['plan_url'] }}" class="btn btn-sm btn-outline-info mb-1">
                                                            Ver
                                                        </a>
                                                        <br>
                                                        <button type="button" wire:click="crearMesesFaltantes({{ $auditItem['client_product_id'] }})" wire:loading.attr="disabled" wire:target="auditarMesesFacturacion,crearMesesFaltantes,crearTodosLosMesesFaltantes" class="btn btn-sm btn-outline-warning">
                                                            Crear faltantes
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light border mb-0">
                                    Ejecuta la auditoria para ver clientes y planes con meses faltantes en la facturacion mensual.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Auditoria de meses facturados pero no pagados</h4>
                            <p class="text-muted mb-3">
                                Revisa los planes activos y detecta meses donde ya existe factura creada, pero sigue pendiente o vencida y queda en medio de meses ya pagados. Por ejemplo, si agosto esta pagado, septiembre pendiente y octubre pagado, aqui se mostrara septiembre; si noviembre sigue pendiente y no tiene un mes pagado despues, no se listara.
                            </p>

                            <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 10px;">
                                <button type="button" wire:click="auditarMesesSinPago" wire:loading.attr="disabled" wire:target="auditarMesesSinPago" class="btn btn-outline-danger">
                                    Auditar meses sin pagar
                                </button>
                                <span wire:loading wire:target="auditarMesesSinPago" class="text-primary">
                                    Procesando auditoria...
                                </span>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Planes revisados</small>
                                        <strong>{{ (int) ($invoice_unpaid_audit_summary['plans_checked'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Planes con problema</small>
                                        <strong>{{ (int) ($invoice_unpaid_audit_summary['plans_with_issues'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Meses sin pagar</small>
                                        <strong>{{ (int) ($invoice_unpaid_audit_summary['unpaid_months'] ?? 0) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <div class="border rounded p-2 bg-light">
                                        <small class="text-muted d-block">Ultima auditoria</small>
                                        <strong>{{ $invoice_unpaid_audit_summary['last_run_at'] ?? 'Sin ejecutar' }}</strong>
                                    </div>
                                </div>
                            </div>

                            @if (!empty($invoice_unpaid_audit_results))
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Plan</th>
                                                <th>Inicio auditoria</th>
                                                <th>Meses pagados</th>
                                                <th>Meses sin pagar</th>
                                                <th>Accion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice_unpaid_audit_results as $auditItem)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $auditItem['client_name'] }}</strong>
                                                        @if (!empty($auditItem['address']))
                                                            <br><small class="text-muted">{{ $auditItem['address'] }}</small>
                                                        @elseif (!empty($auditItem['reference']))
                                                            <br><small class="text-muted">{{ $auditItem['reference'] }}</small>
                                                        @endif
                                                        @if (!empty($auditItem['reactivated_at_label']))
                                                            <br><small class="text-info">Reanudado: {{ $auditItem['reactivated_at_label'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $auditItem['plan_name'] }}</td>
                                                    <td>{{ $auditItem['start_month_label'] }}</td>
                                                    <td>
                                                        @foreach ($auditItem['paid_months'] as $paidMonth)
                                                            <span class="badge border mb-1" style="background:#ecfdf5;color:#166534;border-color:#bbf7d0;">{{ $paidMonth }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        @foreach ($auditItem['unpaid_months'] as $unpaidMonth)
                                                            <span class="badge mb-1" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">{{ $unpaidMonth }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ $auditItem['plan_url'] }}" class="btn btn-sm btn-outline-info mb-1">
                                                            Ver
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-light border mb-0">
                                    Ejecuta la auditoria para ver clientes y planes que ya tienen factura creada pero aun no aparece pagada.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card border mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Respaldo completo del sistema</h4>
                            <p class="text-muted mb-3">
                                Genera un archivo ZIP con la base de datos en SQL, configuracion principal, codigo fuente y archivos necesarios para restaurar el sistema en otro equipo o servidor.
                            </p>
                            <ul class="mb-3">
                                <li>Incluye respaldo SQL completo.</li>
                                <li>Incluye archivo <code>.env</code> y configuraciones clave.</li>
                                <li>Incluye archivos publicos, assets y almacenamiento util.</li>
                            </ul>

                            <div id="backup-progress-wrapper" class="mb-3 d-none">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted" id="backup-progress-label">Preparando respaldo...</small>
                                    <small class="text-muted" id="backup-progress-percent">0%</small>
                                </div>
                                <div class="progress mt-2" style="height: 18px;">
                                    <div id="backup-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="backup-feedback" class="alert d-none" role="alert"></div>

                            <button type="button" id="backup-download-button" class="btn btn-success" data-download-url="{{ route('settings.backup.download') }}">
                                Descargar respaldo completo
                            </button>
                        </div>
                    </div>

                    <div class="card border border-warning mt-4">
                        <div class="card-body">
                            <h4 class="mb-2">Restaurar respaldo del sistema</h4>
                            <p class="text-muted mb-3">
                                Sube un ZIP generado por este sistema para restaurar la base de datos y los archivos incluidos en el respaldo. Esta accion reemplaza la informacion actual.
                            </p>

                            @if ($errors->has('restore_archive') || $errors->has('confirm_restore'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('restore_archive') ?: $errors->first('confirm_restore') }}
                                </div>
                            @endif

                            <div id="restore-progress-wrapper" class="mb-3 d-none">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted" id="restore-progress-label">Esperando archivo...</small>
                                    <small class="text-muted" id="restore-progress-percent">0%</small>
                                </div>
                                <div class="progress mt-2" style="height: 18px;">
                                    <div id="restore-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="restore-feedback" class="alert d-none" role="alert"></div>

                            <form id="restore-backup-form" action="{{ route('settings.backup.restore') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="restore_archive">Archivo ZIP del respaldo</label>
                                    <input type="file" name="restore_archive" id="restore_archive" class="form-control" accept=".zip" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_restore">Escribe <code>RESTAURAR</code> para confirmar</label>
                                    <input type="text" name="confirm_restore" id="confirm_restore" class="form-control" placeholder="RESTAURAR" required>
                                </div>
                                <button type="submit" id="restore-backup-button" class="btn btn-warning">
                                    Restaurar respaldo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <style>
        .ticket-preview-shell {
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }

        .ticket-preview-paper {
            max-width: 360px;
            margin: 0 auto;
            padding: 16px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            font-size: 12px;
            color: #0f172a;
        }

        .ticket-style-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .ticket-style-card {
            padding: 12px;
            border: 1px solid #dbe4f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .ticket-style-card h6 {
            margin-bottom: 10px;
            font-weight: 700;
        }

        .ticket-preview-company {
            text-align: center;
        }

        .ticket-preview-logo {
            margin-bottom: 8px;
        }

        .ticket-preview-logo img {
            display: inline-block;
            height: auto;
            object-fit: contain;
        }

        .ticket-preview-company span {
            display: block;
            margin-top: 2px;
        }

        .ticket-preview-divider {
            border-top: 1px dashed #000000;
            margin: 10px 0;
        }

        .ticket-preview-cutline {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px dashed #000000;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #000000;
        }

        .ticket-preview-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }

        .ticket-preview-row-concept,
        .ticket-preview-row-amount {
            margin-top: 6px;
        }

        .ticket-preview-concept-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 8px;
            padding: 7px 10px;
            border: 1px solid #000000;
            border-radius: 10px;
            background: #f3f4f6;
        }

        .ticket-preview-concept-pill-label {
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.02em;
        }

        .ticket-preview-row-concept span,
        .ticket-preview-row-amount span,
        .ticket-preview-row-value-strong {
            font-weight: 700;
            color: #000000;
        }

        .ticket-preview-amount-value {
            font-size: 1.18em;
            font-weight: 900;
            color: #000000;
            letter-spacing: 0.01em;
        }

        .ticket-preview-total-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 8px;
            padding: 7px 10px;
            border: 1px solid #000000;
            border-radius: 10px;
            background: #f3f4f6;
        }

        .ticket-preview-total-pill-label {
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.02em;
        }

        .ticket-preview-row-client {
            align-items: flex-start;
        }

        .ticket-preview-row-value {
            text-align: right;
            max-width: 68%;
            word-break: break-word;
        }

        .ticket-preview-block {
            line-height: 1.5;
        }
    </style>
@endonce

@once
    <script>
        (function () {
            function printTicketPreview() {
                var preview = document.getElementById('ticket-preview-print-area');

                if (!preview) {
                    return;
                }

                var printWindow = window.open('', '_blank', 'width=420,height=780');

                if (!printWindow) {
                    window.alert('No se pudo abrir la vista de impresion.');
                    return;
                }

                printWindow.document.open();
                printWindow.document.write(
                    '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Vista previa del ticket</title><style>' +
                    'body{margin:0;padding:16px;background:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#0f172a;}' +
                    '.ticket-preview-paper{max-width:360px;margin:0 auto;padding:16px;background:#fff;border:1px solid #dbe4f0;border-radius:14px;font-size:12px;color:#0f172a;}' +
                    '.ticket-preview-company{text-align:center;}.ticket-preview-company span{display:block;margin-top:2px;}' +
                    '.ticket-preview-divider{border-top:1px dashed #000000;margin:10px 0;}' +
                    '.ticket-preview-row{display:flex;justify-content:space-between;gap:12px;margin-bottom:4px;}' +
                    '.ticket-preview-block{line-height:1.5;}' +
                    '@media print{body{padding:0;}.ticket-preview-paper{border:0;border-radius:0;box-shadow:none;max-width:none;}}' +
                    '</style></head><body>' + preview.outerHTML + '</body></html>'
                );
                printWindow.document.close();
                printWindow.focus();
                printWindow.onload = function () {
                    printWindow.print();
                };
            }

            function setProgressState(config) {
                config.wrapper.classList.remove('d-none');
                config.bar.style.width = config.percent + '%';
                config.bar.setAttribute('aria-valuenow', config.percent);
                config.label.textContent = config.labelText;
                config.percentNode.textContent = config.percent + '%';
            }

            function setFeedback(node, type, message) {
                node.className = 'alert alert-' + type;
                node.textContent = message;
                node.classList.remove('d-none');
            }

            function clearFeedback(node) {
                node.className = 'alert d-none';
                node.textContent = '';
            }

            function parseJsonSafe(text) {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    return {};
                }
            }

            function extractResponseMessage(payload, rawText, fallbackMessage) {
                if (payload && payload.message) {
                    return payload.message;
                }

                if (rawText) {
                    var trimmedText = rawText.trim();

                    if (trimmedText && trimmedText.charAt(0) !== '<') {
                        return trimmedText;
                    }
                }

                return fallbackMessage;
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('click', function (event) {
                    var previewPrintButton = event.target.closest('#ticket-preview-print-button');

                    if (previewPrintButton) {
                        event.preventDefault();
                        printTicketPreview();
                    }
                });

                var backupButton = document.getElementById('backup-download-button');
                var backupWrapper = document.getElementById('backup-progress-wrapper');
                var backupBar = document.getElementById('backup-progress-bar');
                var backupLabel = document.getElementById('backup-progress-label');
                var backupPercent = document.getElementById('backup-progress-percent');
                var backupFeedback = document.getElementById('backup-feedback');

                if (backupButton) {
                    backupButton.addEventListener('click', function () {
                        var backupUrl = backupButton.getAttribute('data-download-url');
                        var progressValue = 8;
                        var timer = null;

                        clearFeedback(backupFeedback);
                        backupButton.disabled = true;
                        backupBar.classList.remove('bg-danger');
                        backupBar.classList.add('bg-success');
                        setProgressState({
                            wrapper: backupWrapper,
                            bar: backupBar,
                            label: backupLabel,
                            percentNode: backupPercent,
                            percent: progressValue,
                            labelText: 'Preparando respaldo...'
                        });

                        timer = window.setInterval(function () {
                            if (progressValue < 90) {
                                progressValue += progressValue < 50 ? 8 : 4;
                            }

                            setProgressState({
                                wrapper: backupWrapper,
                                bar: backupBar,
                                label: backupLabel,
                                percentNode: backupPercent,
                                percent: Math.min(progressValue, 90),
                                labelText: progressValue < 65 ? 'Empaquetando archivos...' : 'Finalizando descarga...'
                            });
                        }, 450);

                        fetch(backupUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                            .then(function (response) {
                                if (!response.ok) {
                                    return response.text().then(function (text) {
                                        var payload = parseJsonSafe(text);
                                        throw new Error(payload.message || text || 'No se pudo generar el respaldo.');
                                    });
                                }

                                return response.json();
                            })
                            .then(function (payload) {
                                window.clearInterval(timer);
                                setProgressState({
                                    wrapper: backupWrapper,
                                    bar: backupBar,
                                    label: backupLabel,
                                    percentNode: backupPercent,
                                    percent: 100,
                                    labelText: 'Respaldo listo. Iniciando descarga...'
                                });
                                setFeedback(backupFeedback, 'success', payload.message || 'Respaldo listo para descargar.');
                                window.setTimeout(function () {
                                    window.location.href = payload.download_url || backupUrl;
                                    backupButton.disabled = false;
                                }, 500);
                            })
                            .catch(function (error) {
                                window.clearInterval(timer);
                                backupButton.disabled = false;
                                setProgressState({
                                    wrapper: backupWrapper,
                                    bar: backupBar,
                                    label: backupLabel,
                                    percentNode: backupPercent,
                                    percent: 100,
                                    labelText: 'No se pudo completar el respaldo.'
                                });
                                backupBar.classList.remove('bg-success');
                                backupBar.classList.add('bg-danger');
                                setFeedback(backupFeedback, 'danger', error.message || 'No se pudo generar el respaldo.');
                            });
                    });
                }

                var restoreForm = document.getElementById('restore-backup-form');
                var restoreButton = document.getElementById('restore-backup-button');
                var restoreWrapper = document.getElementById('restore-progress-wrapper');
                var restoreBar = document.getElementById('restore-progress-bar');
                var restoreLabel = document.getElementById('restore-progress-label');
                var restorePercent = document.getElementById('restore-progress-percent');
                var restoreFeedback = document.getElementById('restore-feedback');

                if (restoreForm) {
                    restoreForm.addEventListener('submit', function (event) {
                        event.preventDefault();

                        if (!window.confirm('Se restaurara la base de datos y los archivos del sistema. Deseas continuar?')) {
                            return;
                        }

                        var formData = new FormData(restoreForm);
                        var restoreRequest = new XMLHttpRequest();
                        var processingPercent = 70;
                        var processingTimer = null;

                        clearFeedback(restoreFeedback);
                        restoreButton.disabled = true;
                        restoreBar.classList.remove('bg-danger', 'bg-success');
                        restoreBar.classList.add('bg-warning');
                        setProgressState({
                            wrapper: restoreWrapper,
                            bar: restoreBar,
                            label: restoreLabel,
                            percentNode: restorePercent,
                            percent: 0,
                            labelText: 'Preparando restauracion...'
                        });

                        restoreRequest.open('POST', restoreForm.getAttribute('action'), true);
                        restoreRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        restoreRequest.setRequestHeader('Accept', 'application/json');

                        restoreRequest.upload.addEventListener('progress', function (progressEvent) {
                            if (!progressEvent.lengthComputable) {
                                return;
                            }

                            var uploadPercent = Math.round((progressEvent.loaded / progressEvent.total) * 70);
                            processingPercent = Math.max(uploadPercent, 10);

                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: processingPercent,
                                labelText: 'Subiendo respaldo al servidor...'
                            });
                        });

                        restoreRequest.upload.addEventListener('load', function () {
                            processingTimer = window.setInterval(function () {
                                if (processingPercent < 95) {
                                    processingPercent += 3;
                                }

                                setProgressState({
                                    wrapper: restoreWrapper,
                                    bar: restoreBar,
                                    label: restoreLabel,
                                    percentNode: restorePercent,
                                    percent: Math.min(processingPercent, 95),
                                    labelText: 'Aplicando restauracion...'
                                });
                            }, 500);
                        });

                        restoreRequest.onload = function () {
                            var payload = parseJsonSafe(restoreRequest.responseText);
                            window.clearInterval(processingTimer);
                            restoreButton.disabled = false;

                            if (restoreRequest.status >= 200 && restoreRequest.status < 300) {
                                restoreBar.classList.remove('bg-warning');
                                restoreBar.classList.add('bg-success');
                                setProgressState({
                                    wrapper: restoreWrapper,
                                    bar: restoreBar,
                                    label: restoreLabel,
                                    percentNode: restorePercent,
                                    percent: 100,
                                    labelText: 'Restauracion completada.'
                                });
                                setFeedback(restoreFeedback, 'success', payload.message || 'Respaldo restaurado correctamente.');
                                window.setTimeout(function () {
                                    window.location.reload();
                                }, 1200);
                                return;
                            }

                            restoreBar.classList.remove('bg-warning');
                            restoreBar.classList.add('bg-danger');
                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: 100,
                                labelText: 'No se pudo completar la restauracion.'
                            });
                            setFeedback(
                                restoreFeedback,
                                'danger',
                                extractResponseMessage(payload, restoreRequest.responseText, 'No se pudo restaurar el respaldo.')
                            );
                        };

                        restoreRequest.onerror = function () {
                            window.clearInterval(processingTimer);
                            restoreButton.disabled = false;
                            restoreBar.classList.remove('bg-warning');
                            restoreBar.classList.add('bg-danger');
                            setProgressState({
                                wrapper: restoreWrapper,
                                bar: restoreBar,
                                label: restoreLabel,
                                percentNode: restorePercent,
                                percent: 100,
                                labelText: 'La conexion fallo durante la restauracion.'
                            });
                            setFeedback(restoreFeedback, 'danger', 'La conexion fallo durante la restauracion.');
                        };

                        restoreRequest.send(formData);
                    });
                }
            });
        })();
    </script>
@endonce
