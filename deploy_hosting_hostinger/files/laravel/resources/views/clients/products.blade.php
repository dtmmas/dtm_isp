<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Planes contratados por: '.$client->user->name_complete) }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto">
        </ol>
    </x-slot>
    @livewire('client-product-component', ['client'=>$client])

    <x-slot name="script">
        @php($googleMapsApiKey = config('services.google_maps.key'))

        <script type="text/javascript">
            let planMap = null;
            let planMarker = null;
            let planGeoWatchId = null;
            let planAccuracyCircle = null;
            let isSyncingPlanInputs = false;
            let activePlanMapMode = 'Satelital';
            let planLabelsVisible = true;
            let isGooglePlanMapReady = false;
            const defaultPlanCoordinates = {
                lat: 15.3134,
                lng: -91.4781,
                zoom: 17
            };

            function updatePlanPrecisionStatus(message, tone = 'muted') {
                const statusElement = document.getElementById('planPrecisionStatus');

                if (!statusElement) {
                    return;
                }

                statusElement.textContent = message;
                statusElement.className = 'plan-map-status plan-map-status-' + tone + ' mb-2';
            }

            function updatePlanCoordinateSummary(lat = null, lng = null) {
                const summaryElement = document.getElementById('planCoordinateSummary');

                if (!summaryElement) {
                    return;
                }

                if (lat === null || lng === null || Number.isNaN(Number(lat)) || Number.isNaN(Number(lng))) {
                    summaryElement.textContent = 'Aun no se han definido coordenadas para este plan.';
                    return;
                }

                summaryElement.textContent = 'Latitud: ' + Number(lat).toFixed(7) + ' | Longitud: ' + Number(lng).toFixed(7);
            }

            function updatePlanMapControlState() {
                const satelliteButton = document.getElementById('planMapModeSatellite');
                const streetButton = document.getElementById('planMapModeStreet');
                const labelsButton = document.getElementById('planMapLabelsToggle');

                if (satelliteButton) {
                    satelliteButton.classList.toggle('active', activePlanMapMode === 'Satelital');
                }

                if (streetButton) {
                    streetButton.classList.toggle('active', activePlanMapMode === 'Calles');
                }

                if (labelsButton) {
                    labelsButton.classList.toggle('active', planLabelsVisible);
                }
            }

            function planMapTypeId() {
                if (activePlanMapMode === 'Calles') {
                    return google.maps.MapTypeId.ROADMAP;
                }

                return planLabelsVisible ? google.maps.MapTypeId.HYBRID : google.maps.MapTypeId.SATELLITE;
            }

            function applyPlanMapType() {
                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                planMap.setMapTypeId(planMapTypeId());
                updatePlanMapControlState();
            }

            function createPlanMarkerIcon() {
                return {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: '#2563eb',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 3,
                    scale: 9
                };
            }

            function buildPlanNavigationUrl(provider, lat, lng) {
                const normalizedLat = Number(lat).toFixed(7);
                const normalizedLng = Number(lng).toFixed(7);

                if (provider === 'waze') {
                    return 'https://waze.com/ul?ll=' + normalizedLat + ',' + normalizedLng + '&navigate=yes';
                }

                return 'https://www.google.com/maps/dir/?api=1&destination=' + normalizedLat + ',' + normalizedLng;
            }

            function openPlanNavigation(provider) {
                const currentCoordinates = getCurrentPlanCoordinates();

                if (!currentCoordinates) {
                    swal('Primero define las coordenadas del plan para poder navegar.', {icon: 'warning'});
                    return;
                }

                window.open(buildPlanNavigationUrl(provider, currentCoordinates.lat, currentCoordinates.lng), '_blank', 'noopener');
            }

            function getCoordinateInputValue(inputId) {
                const input = document.getElementById(inputId);

                return input && input.value !== '' ? parseFloat(input.value) : null;
            }

            function setCoordinateInputValue(inputId, value) {
                const input = document.getElementById(inputId);

                if (!input) {
                    return;
                }

                const normalizedValue = Number(value).toFixed(7);

                if (input.value !== normalizedValue) {
                    input.value = normalizedValue;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            function drawPlanAccuracy(lat, lng, accuracy) {
                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                if (planAccuracyCircle) {
                    planAccuracyCircle.setMap(null);
                    planAccuracyCircle = null;
                }

                if (!accuracy || Number.isNaN(accuracy)) {
                    return;
                }

                planAccuracyCircle = new google.maps.Circle({
                    map: planMap,
                    center: { lat: Number(lat), lng: Number(lng) },
                    radius: accuracy,
                    strokeColor: '#2563eb',
                    strokeWeight: 1,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.16
                });
            }

            function getCurrentPlanCoordinates() {
                const lat = getCoordinateInputValue('planLatitudeInput');
                const lng = getCoordinateInputValue('planLongitudeInput');

                if (lat === null || lng === null || Number.isNaN(lat) || Number.isNaN(lng)) {
                    return null;
                }

                return { lat, lng };
            }

            function syncPlanCoordinates(lat, lng, shouldCenter = true) {
                isSyncingPlanInputs = true;
                setCoordinateInputValue('planLatitudeInput', lat);
                setCoordinateInputValue('planLongitudeInput', lng);
                isSyncingPlanInputs = false;
                window.livewire.emit('setPlanCoordinates', Number(lat).toFixed(7), Number(lng).toFixed(7));

                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                const target = { lat: parseFloat(lat), lng: parseFloat(lng) };
                updatePlanCoordinateSummary(lat, lng);

                if (!planMarker) {
                    planMarker = new google.maps.Marker({
                        position: target,
                        map: planMap,
                        draggable: true,
                        icon: createPlanMarkerIcon()
                    });
                    planMarker.addListener('dragend', function (event) {
                        syncPlanCoordinates(event.latLng.lat(), event.latLng.lng(), false);
                        updatePlanPrecisionStatus('Marcador ajustado manualmente.', 'good');
                    });
                } else {
                    planMarker.setPosition(target);
                }

                if (shouldCenter) {
                    planMap.setCenter(target);
                    planMap.setZoom(Math.max(planMap.getZoom() || defaultPlanCoordinates.zoom, 19));
                }

                if (planAccuracyCircle) {
                    planAccuracyCircle.setCenter(target);
                }
            }

            function buildPlanMap() {
                if (planMap || !document.getElementById('planMap') || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                planMap = new google.maps.Map(document.getElementById('planMap'), {
                    center: defaultPlanCoordinates,
                    zoom: defaultPlanCoordinates.zoom,
                    mapTypeId: google.maps.MapTypeId.HYBRID,
                    streetViewControl: false,
                    fullscreenControl: true,
                    mapTypeControl: false,
                    clickableIcons: false
                });

                applyPlanMapType();

                planMap.addListener('click', function (event) {
                    syncPlanCoordinates(event.latLng.lat(), event.latLng.lng(), false);
                    drawPlanAccuracy(event.latLng.lat(), event.latLng.lng(), null);
                    updatePlanPrecisionStatus('Ubicacion ajustada manualmente en el mapa.', 'good');
                });

                const currentCoordinates = getCurrentPlanCoordinates();
                if (currentCoordinates) {
                    syncPlanCoordinates(currentCoordinates.lat, currentCoordinates.lng);
                } else {
                    updatePlanCoordinateSummary();
                }
            }

            function refreshPlanMap() {
                buildPlanMap();

                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                const currentCoordinates = getCurrentPlanCoordinates();

                if (currentCoordinates) {
                    syncPlanCoordinates(currentCoordinates.lat, currentCoordinates.lng);
                } else if (planMarker) {
                    planMarker.setMap(null);
                    if (planAccuracyCircle) {
                        planAccuracyCircle.setMap(null);
                    }
                    planMarker = null;
                    planAccuracyCircle = null;
                    planMap.setCenter(defaultPlanCoordinates);
                    planMap.setZoom(defaultPlanCoordinates.zoom);
                    updatePlanCoordinateSummary();
                    updatePlanPrecisionStatus('Use el GPS del telefono y luego ajuste el punto manualmente si hace falta.', 'muted');
                }

                setTimeout(function () {
                    google.maps.event.trigger(planMap, 'resize');

                    if (currentCoordinates) {
                        planMap.setCenter({ lat: currentCoordinates.lat, lng: currentCoordinates.lng });
                    } else {
                        planMap.setCenter(defaultPlanCoordinates);
                    }
                }, 250);
            }

            function centrarMapaEnCoordenadasActuales() {
                if (!planMap) {
                    refreshPlanMap();
                }

                const currentCoordinates = getCurrentPlanCoordinates();

                if (currentCoordinates && planMap) {
                    planMap.setCenter({ lat: currentCoordinates.lat, lng: currentCoordinates.lng });
                    planMap.setZoom(Math.max(planMap.getZoom() || defaultPlanCoordinates.zoom, 18));
                    return;
                }

                swal('Primero define las coordenadas actuales del plan.', {icon: 'warning'});
            }

            function switchPlanMapMode(mode) {
                if (!planMap) {
                    refreshPlanMap();
                }

                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                activePlanMapMode = mode;
                applyPlanMapType();
            }

            function togglePlanMapLabels() {
                if (!planMap) {
                    refreshPlanMap();
                }

                if (!planMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                planLabelsVisible = !planLabelsVisible;
                applyPlanMapType();
            }

            function zoomPlanMap(step) {
                if (!planMap) {
                    refreshPlanMap();
                }

                if (!planMap) {
                    return;
                }

                planMap.setZoom((planMap.getZoom() || defaultPlanCoordinates.zoom) + step);
            }

            window.initPlanMap = function () {
                isGooglePlanMapReady = true;
                buildPlanMap();
            };

            window.livewire.on('productUpdate', (message) => {
                swal(message, {icon: "success",});
                $('#exampleModal').modal('hide');
            });

            window.livewire.on('productAction', (message, icon) => {
                swal(message, {icon: icon,});
            });

            window.livewire.on('clientStoreInvoice', (message, icon) => {
                swal(message, {icon: icon,});
                $('#modalCreateInvoice').modal('hide');
                window.livewire.emit('onRefresh')
            });

            window.livewire.on('storePaymentAdvancement', (message) => {
                swal(message, {icon: "success",});
                $('#modalAdvancement').modal('hide');
                window.livewire.emit('onRefresh')
                window.livewire.emit('onRefreshCP')
            });

            function printPaymentReceipts(paymentId) {
                if (!paymentId) {
                    return;
                }

                if (window.DTMPrinter) {
                        return window.DTMPrinter.printPaymentReceipt(paymentId, {
                            allowBrowserFallback: false,
                        }).catch(function () {
                            swal('No fue posible reimprimir sin selector. Verifica que QZ Tray este activo en esta estacion.', {icon: 'warning'});
                        });
                }
            }

            function promptPaymentReceiptReprint(paymentId) {
                if (!paymentId) {
                    return;
                }

                setTimeout(function () {
                    swal({
                        title: "Ticket impreso",
                        text: "¿Desea reimprimir el ticket?",
                        icon: "info",
                        buttons: {
                            cancel: "No",
                            confirm: {
                                text: "Si, reimprimir",
                                value: true,
                                visible: true
                            },
                        },
                    }).then((shouldReprint) => {
                        if (shouldReprint) {
                            printPaymentReceipts(paymentId);
                        }
                    });
                }, 1200);
            }

            window.livewire.on('paymentUpdateStore', (message, id, paymentId) => {
                $('#ModalPagar'+id).modal('hide');
                $('#ModalPagarEfectivo'+id).modal('hide');
                swal(message, {icon: "success", buttons: false, timer: 1200});
                printPaymentReceipts(paymentId);
                promptPaymentReceiptReprint(paymentId);
            });

            function obtenerCoordenadasPlan(){
                if (!navigator.geolocation) {
                    swal('Tu dispositivo no permite obtener ubicacion.', {icon: 'warning'});
                    return;
                }

                let bestPosition = null;
                updatePlanPrecisionStatus('Buscando la mejor lectura GPS disponible...', 'pending');

                if (planGeoWatchId !== null) {
                    navigator.geolocation.clearWatch(planGeoWatchId);
                    planGeoWatchId = null;
                }

                const finalizeGeolocation = function() {
                    if (planGeoWatchId !== null) {
                        navigator.geolocation.clearWatch(planGeoWatchId);
                        planGeoWatchId = null;
                    }

                    if (!bestPosition) {
                        swal('No fue posible obtener la ubicacion. Verifica los permisos del navegador.', {icon: 'warning'});
                        return;
                    }

                    syncPlanCoordinates(bestPosition.coords.latitude, bestPosition.coords.longitude);

                    const accuracy = bestPosition.coords.accuracy ? Math.round(bestPosition.coords.accuracy) : null;
                    drawPlanAccuracy(bestPosition.coords.latitude, bestPosition.coords.longitude, accuracy);
                    const message = accuracy
                        ? 'Coordenadas capturadas correctamente. Precision aproximada: ' + accuracy + ' metros.'
                        : 'Coordenadas capturadas correctamente.';

                    if (accuracy) {
                        const tone = accuracy <= 10 ? 'good' : (accuracy <= 25 ? 'warning' : 'danger');
                        updatePlanPrecisionStatus('Precision GPS estimada: ' + accuracy + ' m. Puede mover el marcador para afinar el punto.', tone);
                    } else {
                        updatePlanPrecisionStatus('Coordenadas capturadas. Puede mover el marcador para afinar el punto.', 'good');
                    }

                    swal(message, {icon: 'success'});
                };

                const timeoutId = setTimeout(function () {
                    finalizeGeolocation();
                }, 15000);

                planGeoWatchId = navigator.geolocation.watchPosition(function(position){
                    const accuracy = position.coords && position.coords.accuracy ? Math.round(position.coords.accuracy) : null;

                    if (!bestPosition || (accuracy && (!bestPosition.coords.accuracy || accuracy < bestPosition.coords.accuracy))) {
                        bestPosition = position;
                        syncPlanCoordinates(position.coords.latitude, position.coords.longitude);
                        drawPlanAccuracy(position.coords.latitude, position.coords.longitude, accuracy);

                        if (accuracy) {
                            updatePlanPrecisionStatus('Mejor lectura actual: ' + accuracy + ' m. Esperando una lectura aun mas precisa...', 'pending');
                        }
                    }

                    if (position.coords && position.coords.accuracy && position.coords.accuracy <= 15) {
                        clearTimeout(timeoutId);
                        finalizeGeolocation();
                    }
                }, function(error){
                    clearTimeout(timeoutId);

                    if (!bestPosition && error && error.message) {
                        updatePlanPrecisionStatus(error.message, 'danger');
                    }

                    finalizeGeolocation();
                }, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                });
            }

            $(document).on('shown.bs.modal', '#exampleModal', function () {
                if (!isGooglePlanMapReady) {
                    updatePlanPrecisionStatus('Configura GOOGLE_MAPS_API_KEY para usar Google Maps en este modulo.', 'warning');
                    return;
                }

                refreshPlanMap();
            });

            $(document).on('hidden.bs.modal', '#exampleModal', function () {
                if (planGeoWatchId !== null) {
                    navigator.geolocation.clearWatch(planGeoWatchId);
                    planGeoWatchId = null;
                }
            });

            $(document).on('input', '#planLatitudeInput, #planLongitudeInput', function () {
                if (isSyncingPlanInputs) {
                    return;
                }

                const currentCoordinates = getCurrentPlanCoordinates();

                if (currentCoordinates) {
                    syncPlanCoordinates(currentCoordinates.lat, currentCoordinates.lng, false);
                    updatePlanPrecisionStatus('Coordenadas ajustadas manualmente.', 'good');
                } else {
                    updatePlanCoordinateSummary();
                }
            });

            
            @can('clients.edit')
            function EliminarPlan(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminará el plan "+name+". Esta accion es irreversible",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Eliminar",
                            closeModal: false,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDelete) => {
                    if (willDelete) {
                        window.livewire.emit('onDeleteRow',id)
                    }
                });
            }

            function SuspenderPlan(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se suspenderá el plan "+name+".",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Suspender",
                            closeModal: true,
                            className: 'swal-button--warning'
                        },
                    },
                  })
                .then((willDo) => {
                    if (willDo) {
                        window.livewire.emit('onSuspendRow',id)
                    }
                });
            }

            function CancelarPlan(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se eliminarán primero los pagos pendientes y las facturas pendientes/vencidas del plan "+name+", y luego se cancelará el plan sin eliminarlo.",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Cancelar plan",
                            closeModal: true,
                            className: 'swal-button--danger'
                        },
                    },
                  })
                .then((willDo) => {
                    if (willDo) {
                        window.livewire.emit('onCancelRow',id)
                    }
                });
            }

            function ActivarPlan(id, name){
                swal({
                    title: "¿Estas seguro?",
                    text: "Se activará el plan "+name+".",
                    icon: "info",
                    buttons: {
                        cancel: "Cancelar",
                        catch: {
                            text: "Si, Activar",
                            closeModal: true,
                            className: 'swal-button--success'
                        },
                    },
                  })
                .then((willDo) => {
                    if (willDo) {
                        window.livewire.emit('onActivateRow',id)
                    }
                });
            }
            @endcan
        </script>

        @if (!empty($googleMapsApiKey))
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initPlanMap"></script>
        @endif
    </x-slot>
</x-app-layout>
