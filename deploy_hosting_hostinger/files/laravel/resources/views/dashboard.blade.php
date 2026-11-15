<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Dashboard') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
            {{-- <li><a href="#" class="fw-normal">Dashboard</a></li> --}}
        </ol>
    </x-slot>

    @if (Auth::user()->hasRole('Admin'))
        <x-slot name="widgets">
            <div class="col-lg-4 col-md-12">
                <x-widget title="Total Pagos Deposito" color="primary" :total="$total_deposito"/>
            </div>
            <div class="col-lg-4 col-md-12">
                <x-widget title="Total Pagos Tarjeta" color="info" :total="$total_tarjeta"/>
            </div>
            <div class="col-lg-4 col-md-12">
                <x-widget title="Total Pagos Efectivo" color="success" :total="$total_efectivo"/>
            </div>
            <div class="col-lg-4 col-md-12">
                <x-widget title="Total Clientes" color="success" :total="$total_clientes"/>
            </div>
            <div class="col-lg-4 col-md-12">                
                <x-widget title="Total Pagos Pendientes" color="warning" :total="$total_pagos_pendientes"/>
            </div>
        </x-slot>

        <div class="dashboard-action-grid">
            <div class="dashboard-action-card">
                <div class="dashboard-action-copy">
                    <span class="dashboard-action-title">Recordatorios por WhatsApp</span>
                    <span class="dashboard-action-text">Envía avisos masivos a clientes morosos desde el panel administrativo.</span>
                </div>
                <button onclick="EnviarRecordatorio()" class="btn btn-warning">Enviar recordatorios</button>
            </div>
            <div class="dashboard-action-card">
                <div class="dashboard-action-copy">
                    <span class="dashboard-action-title">Generación mensual de facturas</span>
                    <span class="dashboard-action-text">Ejecuta el proceso de facturación del periodo con una confirmación previa.</span>
                </div>
                <button onclick="GenerarFacturas()" class="btn btn-danger">Generar facturas</button>
            </div>
        </div>
        @php
            $totalPlanesActivos = $planes_activos_por_zona->sum('total_planes_activos');
            $maxPlanesZona = max(1, (int) $planes_activos_por_zona->max('total_planes_activos'));
        @endphp

        <div class="white-box dashboard-zone-panel">
            <div class="dashboard-zone-header">
                <div>
                    <h3 class="dashboard-zone-title">Planes activos por zona</h3>
                    <p class="dashboard-zone-subtitle">Resumen de planes con estado activo agrupados por zona.</p>
                </div>
                <span class="dashboard-zone-total">Total activo: {{ $totalPlanesActivos }}</span>
            </div>
            <div class="dashboard-zone-grid">
                @forelse ($planes_activos_por_zona as $zona)
                    @php
                        $percentOfMax = $maxPlanesZona > 0 ? round(($zona->total_planes_activos / $maxPlanesZona) * 100) : 0;
                        $percentOfTotal = $totalPlanesActivos > 0 ? number_format(($zona->total_planes_activos / $totalPlanesActivos) * 100, 1) : '0.0';
                    @endphp
                    <article class="dashboard-zone-card">
                        <div class="dashboard-zone-card-top">
                            <span class="dashboard-zone-rank">#{{ $loop->iteration }}</span>
                            <span class="dashboard-zone-count">{{ $zona->total_planes_activos }}</span>
                        </div>
                        <h4 class="dashboard-zone-name">{{ $zona->name }}</h4>
                        <div class="dashboard-zone-meta">
                            <span>Participacion</span>
                            <strong>{{ $percentOfTotal }}%</strong>
                        </div>
                        <div class="dashboard-zone-progress" aria-hidden="true">
                            <span style="width: {{ $percentOfMax }}%"></span>
                        </div>
                        <div class="dashboard-zone-foot">
                            <span>{{ $percentOfMax }}% respecto a la zona con mas planes</span>
                        </div>
                    </article>
                @empty
                    <div class="dashboard-zone-empty">
                        No hay zonas registradas.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list_logs as $log)
                        <tr>
                            <td>{{$log->created_at}}</td>
                            <td>{{$log->detalle}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No se ha ejecutado el cron</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>
            {{ $list_logs->links() }}
        </div>
    @endif

    <x-slot name="script">
        @if (Auth::user()->hasRole('Admin'))
        <script type="text/javascript">            
            function GenerarFacturas(){
                $.ajax({
                    type: "GET",
                    dataType: "json",
                    url: "{{ route('dashboard.billing.preview') }}",
                    success: function(result){
                        swal({
                            title: "¿Estas seguro?",
                            text: "Solo puedes generar facturas una sola vez al mes.\r\n****Fecha Actual: "+result.fecha_actual+"****",
                            icon: "warning",
                            buttons: {
                                cancel: "Cancelar",
                                catch: {
                                    text: "Si, Generar",
                                    closeModal: false,
                                    className: 'swal-button--danger'
                                },
                            },
                          })
                        .then((willDelete) => {
                            if (willDelete) {
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: "{{ route('dashboard.billing.generate') }}",
                                    data: {
                                        _token: "{{ csrf_token() }}"
                                    },
                                    success: function(result){
                                        swal({
                                            title: "Proceso finalizado",
                                            text: result.message,
                                            icon: "success",
                                        })
                                    },error: function(XMLHttpRequest, textStatus, errorThrown){ 
                                        let responseText = XMLHttpRequest.responseText;
                                        try {
                                            const parsed = JSON.parse(XMLHttpRequest.responseText);
                                            responseText = parsed.message ?? responseText;
                                        } catch (e) {
                                        }
                                        swal({
                                            title: "Ocurrio un error",
                                            text: responseText,
                                            icon: "error",
                                        })
                                    }
                                });
                            }
                        });
                    },error: function(XMLHttpRequest, textStatus, errorThrown){ 
                        swal({
                            title: "Error",
                            text: XMLHttpRequest.responseText,
                            icon: "warning",
                        })
                    }
                });
            }
            function EnviarRecordatorio() {
                swal({
                    title: "¿Estas seguro?",
                    text: "Esto enviara recordatorios por WhatsApp a los clientes morosos.",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Enviar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            type: "GET",
                            dataType: "json",
                            url: "{{ route('recordatorioMora') }}", 
                            success: function(result){
                                if(result.rps){
                                    swal({
                                        title: "Se enviaron "+result.total+" mensajes de WhatsApp",
                                        icon: "success",
                                    })
                                }else{
                                    swal({
                                        title: result.msj,
                                        icon: "warning",
                                    })
                                }
                            },error: function(XMLHttpRequest, textStatus, errorThrown){ 
                                swal({
                                    title: "Ocurrio un error",
                                    text: XMLHttpRequest.responseText,
                                    icon: "error",
                                })
                            }
                        });
                    }
                });
            }
        </script>
        @endif
        
    </x-slot>
</x-app-layout>
