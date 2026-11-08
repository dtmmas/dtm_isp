<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ nombreEmpresaConfig() }}</title>
    <link rel="canonical" href="https://www.wrappixel.com/templates/ample-admin-lite/" />
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="plugins/images/favicon.png">
    <!-- Custom CSS -->
   <link href="{{ asset('css/style.min.css?3') }}" rel="stylesheet">
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
   <link href="{{ asset('css/modern-theme.css?v=6') }}" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file: -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
   <link href="{{ asset('plugins/stacktable.js/css/basictable.css') }}" rel="stylesheet">

    <![endif]-->
    @livewireStyles
</head>
<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header text-center" data-logobg="skin6">
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a style="display: inline-block" class="navbar-brand" href="{{route('dashboard')}}">
                        <!-- Logo icon -->
                        {{--  <b class="logo-icon">
                            <img src="plugins/images/logo-icon.png" alt="homepage" />
                        </b>  --}}
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span class="logo-text">
                            <!-- dark Logo text -->
                            <img style="max-height: 70px;max-width: 110px; margin:2px auto; border-radius: 15px;" src="{{ asset(logoEmpresaConfig()) }}" alt="homepage" />
                        </span>
                    </a>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none"
                        href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav d-none d-md-block d-lg-none">
                        <li class="nav-item">
                            <a class="nav-toggler nav-link waves-effect waves-light text-white"
                                href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav ms-auto d-flex align-items-center">

                        <!-- ============================================================== -->
                        <!-- Search -->
                        <!-- ============================================================== -->
                        {{--  <li class=" in">
                            <form role="search" class="app-search d-none d-md-block me-3">
                                <input type="text" placeholder="Search..." class="form-control mt-0">
                                <a href="" class="active">
                                    <i class="fa fa-search"></i>
                                </a>
                            </form>
                        </li>  --}}
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li>
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <a class="profile-pic" href="{{ route('myprofile') }}">
                                    <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" width="36"
                                    class="img-circle"><span class="text-white font-medium">{{ Auth::user()->name }}</span>
                                </a>
                            @else
                            <a class="profile-pic" href="{{ route('myprofile') }}">
                                <span class="text-white font-medium">{{ Auth::user()->name }}</span>
                            </a>
                            @endif
                            
                        </li>
                        <li>
                            {{-- <a class="profile-pic" href="#">
                                <span class="text-white font-medium">Logout</span></a> --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-jet-dropdown-link class="profile-pic" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Logout') }}
                                </x-jet-dropdown-link>
                            </form>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <!-- Sidebar scroll-->
            @livewire('navigation-menu')
            <!-- End Sidebar scroll-->
        </aside>

        <div class="page-wrapper modern-shell" style="min-height: 250px;">
            <div class="page-breadcrumb">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-xs-12">
                        <h4 class="page-title">{{ $titulo_pagina }}</h4>
                    </div>
                    <div class="col-lg-4 col-xs-12">
                        <div class="d-md-flex">
                            {{ $opciones_nav }}
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-12 -->
            </div>

            <div class="container-fluid">
                @isset($widgets)
                <div class="row  justify-content-center">
                    {{ $widgets }}
                </div>
                @endisset
                
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="white-box modern-content-box">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
     </div>
        <!-- ============================================================== -->
        <!-- End Wrapper -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- All Jquery -->
        <!-- ============================================================== -->
        <script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}"></script>
        <!-- Bootstrap tether Core JavaScript -->
        <script src="{{ asset('bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('js/app-style-switcher.js') }}"></script>
        <!--Wave Effects -->
        <script src="{{ asset('js/waves.js') }}"></script>
        <!--Menu sidebar -->
        <script src="{{ asset('js/sidebarmenu.js') }}"></script>
        <!--Custom JavaScript -->
        <script src="{{ asset('js/custom.js') }}"></script>
        <script src="{{ asset('plugins/stacktable.js/js/jquery.basictable.js') }}"></script>
        <script src="{{ asset('plugins/stacktable.js/js/basictable.js') }}"></script>
        <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
        @if (config('printing.thermal.enabled'))
        <script src="{{ config('printing.thermal.qz.script_url') }}"></script>
        @endif
        <script src="https://www.paypal.com/sdk/js?client-id={{ config('payment.paypal.client_id') }}"></script>
        @livewireScripts
        <script type="text/javascript">
            window.DTMPrinter = (function () {
                const settings = {
                    enabled: {{ config('printing.thermal.enabled') ? 'true' : 'false' }},
                    forceRaw: {{ config('printing.thermal.force_raw') ? 'true' : 'false' }},
                    browserFallback: {{ config('printing.thermal.fallback_browser') ? 'true' : 'false' }},
                    printer: @json(config('printing.thermal.printer')),
                    receiptUrlTemplate: @json(route('payments.thermal', ['payment' => '__PAYMENT__'])),
                    previewUrlTemplate: @json(route('payments.print', ['payment_id' => '__PAYMENT__'])),
                    certificateUrl: @json(route('printing.qz.certificate')),
                    signUrl: @json(route('printing.qz.sign')),
                    signatureAlgorithm: @json(config('printing.thermal.qz.signature_algorithm', 'SHA512')),
                };

                let connectPromise = null;

                function hasQzTray() {
                    return typeof window.qz !== 'undefined';
                }

                function configureQzSecurity() {
                    if (!hasQzTray() || window.__dtmQzConfigured) {
                        return;
                    }

                    if (qz.websocket && typeof qz.websocket.setUsingSurf === 'function') {
                        qz.websocket.setUsingSurf(false);
                    }

                    qz.security.setCertificatePromise(function(resolve) {
                        fetch(settings.certificateUrl, {
                            cache: 'no-store',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'text/plain' }
                        })
                        .then(function(response) {
                            if (!response.ok) {
                                resolve('');
                                return;
                            }

                            response.text().then(resolve).catch(function() {
                                resolve('');
                            });
                        })
                        .catch(function() {
                            resolve('');
                        });
                    });

                    qz.security.setSignatureAlgorithm(settings.signatureAlgorithm);
                    qz.security.setSignaturePromise(function(toSign) {
                        return function(resolve) {
                            fetch(settings.signUrl + '?request=' + encodeURIComponent(toSign), {
                                cache: 'no-store',
                                credentials: 'same-origin',
                                headers: { 'Content-Type': 'text/plain' }
                            })
                            .then(function(response) {
                                if (!response.ok) {
                                    resolve('');
                                    return;
                                }

                                response.text().then(resolve).catch(function() {
                                    resolve('');
                                });
                            })
                            .catch(function() {
                                resolve('');
                            });
                        };
                    });

                    window.__dtmQzConfigured = true;
                }

                function printViaBrowser(paymentId, payload) {
                    const url = payload && payload.fallback_url
                        ? payload.fallback_url
                        : settings.previewUrlTemplate.replace('__PAYMENT__', paymentId);
                    const iframe = document.createElement('iframe');

                    iframe.style.position = 'fixed';
                    iframe.style.right = '0';
                    iframe.style.bottom = '0';
                    iframe.style.width = '0';
                    iframe.style.height = '0';
                    iframe.style.border = '0';
                    iframe.src = url;
                    iframe.onload = function () {
                        setTimeout(function () {
                            if (iframe.parentNode) {
                                iframe.parentNode.removeChild(iframe);
                            }
                        }, 60000);
                    };

                    document.body.appendChild(iframe);

                    return Promise.resolve({ mode: 'browser' });
                }

                function ensureConnection() {
                    if (!hasQzTray()) {
                        return Promise.reject(new Error('QZ Tray no esta disponible en esta estacion.'));
                    }

                    configureQzSecurity();

                    if (qz.websocket.isActive()) {
                        return Promise.resolve();
                    }

                    if (!connectPromise) {
                        connectPromise = qz.websocket.connect({
                            retries: 2,
                            delay: 0.5,
                        }).catch(function(error) {
                            connectPromise = null;
                            throw error;
                        });
                    }

                    return connectPromise;
                }

                function fetchReceiptPayload(paymentId) {
                    return fetch(settings.receiptUrlTemplate.replace('__PAYMENT__', paymentId), {
                        cache: 'no-store',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    }).then(function(response) {
                        if (!response.ok) {
                            throw new Error('No fue posible obtener el ticket termico.');
                        }

                        return response.json();
                    });
                }

                function resolvePrinter(payload) {
                    if (payload && payload.printer) {
                        return Promise.resolve(payload.printer);
                    }

                    if (settings.printer) {
                        return Promise.resolve(settings.printer);
                    }

                    if (qz.printers && typeof qz.printers.getDefault === 'function') {
                        return qz.printers.getDefault();
                    }

                    return Promise.reject(new Error('No fue posible resolver la impresora predeterminada.'));
                }

                function buildQzData(payload) {
                    return [{
                        type: 'raw',
                        format: 'command',
                        flavor: 'plain',
                        data: window.atob(payload.content),
                    }];
                }

                function printPaymentReceipt(paymentId, options) {
                    const printOptions = Object.assign({
                        allowBrowserFallback: settings.browserFallback,
                    }, options || {});

                    if (!settings.enabled) {
                        if (printOptions.allowBrowserFallback) {
                            return printViaBrowser(paymentId);
                        }

                        return Promise.reject(new Error('La impresion silenciosa no esta habilitada en esta estacion.'));
                    }

                    let cachedPayload = null;

                    return ensureConnection()
                        .then(function() {
                            return fetchReceiptPayload(paymentId);
                        })
                        .then(function(payload) {
                            cachedPayload = payload;
                            return resolvePrinter(payload).then(function(printerName) {
                                const config = qz.configs.create(printerName, {
                                    forceRaw: settings.forceRaw,
                                    jobName: 'DTM Ticket ' + paymentId,
                                });

                                return qz.print(config, buildQzData(payload)).then(function() {
                                    return {
                                        mode: 'qz',
                                        printer: printerName,
                                    };
                                });
                            });
                        })
                        .catch(function(error) {
                            console.error('DTMPrinter', error);

                            if (printOptions.allowBrowserFallback) {
                                return printViaBrowser(paymentId, cachedPayload);
                            }

                            throw error;
                        });
                }

                return {
                    enabled: settings.enabled,
                    hasQzTray: hasQzTray,
                    printPaymentReceipt: printPaymentReceipt,
                    printViaBrowser: printViaBrowser,
                };
            })();

            $(document).ready(function(){
                let mitable = $(".table-responsive2 .table").width();
                let anchoDiv = $(".table-responsive2").width();
                if (mitable > anchoDiv) {
                  $(".table-responsive2").addClass('iconoscroll')
                }

                $('.table').basictable({breakpoint: 769});

                $('.mayusculas').keyup(function () {
                    $(this).val($(this).val().toUpperCase());
                })
                $('.minuscula').keyup(function () {
                    $(this).val($(this).val().toLowerCase());
                })
            })
            
            window.livewire.on('rederr', () => {
                $('.table').basictable({breakpoint: 1769});
                $('.table').basictable({breakpoint: 769});
            })

            window.livewire.on('btnPaypal', (monto,id,address) => {
                paypal.Buttons({
                style: {
                    layout:  'vertical',
                    color:   'blue',
                    shape:   'pill',
                    label:   'paypal'
                },
                    createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                        reference_id: id,
                        description: 'Pago factura {{ nombreEmpresaConfig() }} DIR: '+address+' ',
                        soft_descriptor: 'DTM',
                        amount: {
                            value: monto
                        }
                        }]
                    });
                    },
                    onApprove: function(data, actions) {
                    $('#ModalPagarPaypal').modal('hide');
                    $('#ModalProcesando').modal('show');
                    return actions.order.capture().then(function(details) {
                        $('#paypal-button-container').html('Procesando, por favor espere...');
                        window.livewire.emit('onPagoPaypal',details.purchase_units,id)
                        swal('Pago procesado', {icon: "success",});
                        $('#ModalProcesando').modal('hide');
                        location.reload();
                    });
                    },
                    onError: function (err) {
                        swal('No fue posible procesar el pago', {icon: "error",});
                        console.log(err);
                    }
                }).render('#paypal-button-container');
            });
            
            $('.btncerrarModalPagarPaypal').on('click', function(){
            $('#paypal-button-container').html('');
            })


            function EnviarRecordatorioMoroso($name, $monthList, $total_deuda, $cellphone) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Esto enviara un recordatorio por WhatsApp al cliente.",
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
                            type: "POST",
                            dataType: "json",
                            url: "{{ route('recordatorioMoraMoroso') }}", 
                            data: {
                                name: $name,
                                monthList: $monthList,
                                total_deuda: $total_deuda,
                                cellphone: $cellphone,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result){
                                if(result.respuesta){
                                    swal({
                                        title: "Solicitud aceptada por WhatsApp",
                                        text: "Numero: " + (result.wa_id || $cellphone) + "\nId: " + (result.message_id || 'sin id') + "\nLa entrega final depende del estado que reporte Meta.",
                                        icon: "success",
                                    })
                                }else{
                                    swal({
                                        title: (result.error && result.error.message) ? result.error.message : 'No fue posible enviar el mensaje',
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
            function EnviarRecordatorioFacturaVencer($name, $monthList, $total_deuda, $cellphone, $vencimiento) {
                swal({
                    title: "¿Estas seguro?",
                    text: "Esto enviara un recordatorio por WhatsApp al cliente.",
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
                            type: "POST",
                            dataType: "json",
                            url: "{{ route('recordatorioMoraVencer') }}", 
                            data: {
                                name: $name,
                                monthList: $monthList,
                                total_deuda: $total_deuda,
                                cellphone: $cellphone,
                                vencimiento : $vencimiento,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result){console.log(result)
                                if(result.respuesta){
                                    swal({
                                        title: "Solicitud aceptada por WhatsApp",
                                        text: "Numero: " + (result.wa_id || $cellphone) + "\nId: " + (result.message_id || 'sin id') + "\nLa entrega final depende del estado que reporte Meta.",
                                        icon: "success",
                                    })
                                }else{
                                    swal({
                                        title: (result.error && result.error.message) ? result.error.message : 'No fue posible enviar el mensaje',
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
        @isset($script)
            {{$script}}
        @endisset
    </body>

    </html>
