<x-app-layout>
    <x-slot name="titulo_pagina">
        {{ __('Mapa general de clientes') }}
    </x-slot>
    <x-slot name="opciones_nav">
        <ol class="breadcrumb ms-auto"></ol>
    </x-slot>

    @livewire('client-map-component')

    <x-slot name="script">
        @php($googleMapsApiKey = config('services.google_maps.key'))

        <script type="text/javascript">
            let clientMap = null;
            let clientMapMarkers = [];
            let clientMapInfoWindow = null;
            let activeClientMapMode = 'Satelital';
            let clientMapLabelsVisible = true;
            let isGoogleClientMapReady = false;
            let clientMapRefreshHookBound = false;

            const defaultClientMapCoordinates = {
                lat: 15.3134,
                lng: -91.4781,
                zoom: 11
            };

            function escapeClientMapHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getClientMapPayload() {
                const payloadElement = document.getElementById('clientMapPayload');

                if (!payloadElement) {
                    return { points: [] };
                }

                try {
                    return JSON.parse(payloadElement.textContent || '{"points":[]}');
                } catch (error) {
                    console.error('No fue posible leer el payload del mapa de clientes.', error);
                    return { points: [] };
                }
            }

            function updateClientMapControlState() {
                const satelliteButton = document.getElementById('clientMapModeSatellite');
                const streetButton = document.getElementById('clientMapModeStreet');
                const labelsButton = document.getElementById('clientMapLabelsToggle');

                if (satelliteButton) {
                    satelliteButton.classList.toggle('active', activeClientMapMode === 'Satelital');
                }

                if (streetButton) {
                    streetButton.classList.toggle('active', activeClientMapMode === 'Calles');
                }

                if (labelsButton) {
                    labelsButton.classList.toggle('active', clientMapLabelsVisible);
                }
            }

            function clientMapTypeId() {
                if (activeClientMapMode === 'Calles') {
                    return google.maps.MapTypeId.ROADMAP;
                }

                return clientMapLabelsVisible ? google.maps.MapTypeId.HYBRID : google.maps.MapTypeId.SATELLITE;
            }

            function applyClientMapType() {
                if (!clientMap || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                clientMap.setMapTypeId(clientMapTypeId());
                updateClientMapControlState();
            }

            function clientMarkerColor(tone) {
                switch (tone) {
                    case 'success':
                        return '#16a34a';
                    case 'warning':
                        return '#f59e0b';
                    case 'danger':
                        return '#dc2626';
                    default:
                        return '#64748b';
                }
            }

            function createClientMarker(point) {
                return new google.maps.Marker({
                    position: { lat: Number(point.latitude), lng: Number(point.longitude) },
                    map: clientMap,
                    title: point.clientName,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        fillColor: clientMarkerColor(point.statusTone),
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 3,
                        scale: 9
                    }
                });
            }

            function buildClientMapPopup(point) {
                return ''
                    + '<div class="client-map-popup">'
                    + '  <div class="client-map-popup-title">' + escapeClientMapHtml(point.clientName) + '</div>'
                    + '  <div class="client-map-popup-chip client-map-popup-chip-' + escapeClientMapHtml(point.statusTone) + '">' + escapeClientMapHtml(point.statusLabel) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Plan:</strong> ' + escapeClientMapHtml(point.productName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Zona:</strong> ' + escapeClientMapHtml(point.zoneName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Nodo:</strong> ' + escapeClientMapHtml(point.nodeName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Direccion:</strong> ' + escapeClientMapHtml(point.address) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Telefono:</strong> ' + escapeClientMapHtml(point.clientPhone || 'Sin telefono') + '</div>'
                    + '  <div class="client-map-popup-actions">'
                    + '      <a href="' + escapeClientMapHtml(point.clientUrl) + '" class="client-map-popup-link">Cliente</a>'
                    + '      <a href="' + escapeClientMapHtml(point.planUrl) + '" class="client-map-popup-link client-map-popup-link-primary">Plan</a>'
                    + '  </div>'
                    + '</div>';
            }

            function buildClientMap() {
                if (clientMap || !document.getElementById('clientMap') || typeof google === 'undefined' || !google.maps) {
                    return;
                }

                clientMap = new google.maps.Map(document.getElementById('clientMap'), {
                    center: defaultClientMapCoordinates,
                    zoom: defaultClientMapCoordinates.zoom,
                    mapTypeId: google.maps.MapTypeId.HYBRID,
                    streetViewControl: false,
                    fullscreenControl: true,
                    mapTypeControl: false,
                    clickableIcons: false,
                });

                clientMapInfoWindow = new google.maps.InfoWindow();
                applyClientMapType();
            }

            function setClientMapStatus(message, tone) {
                const statusElement = document.getElementById('clientMapStatus');

                if (!statusElement) {
                    return;
                }

                statusElement.textContent = message;
                statusElement.className = 'plan-map-status plan-map-status-' + tone + ' mb-3';
            }

            function clearClientMarkers() {
                clientMapMarkers.forEach(function (marker) {
                    marker.setMap(null);
                });

                clientMapMarkers = [];
            }

            function renderClientMapFromPayload() {
                if (!isGoogleClientMapReady) {
                    return;
                }

                buildClientMap();

                if (!clientMap) {
                    return;
                }

                const payload = getClientMapPayload();
                const points = Array.isArray(payload.points) ? payload.points : [];
                clearClientMarkers();

                if (!points.length) {
                    clientMap.setCenter(defaultClientMapCoordinates);
                    clientMap.setZoom(defaultClientMapCoordinates.zoom);
                    setClientMapStatus('No hay clientes con coordenadas para los filtros actuales.', 'muted');
                    return;
                }

                const bounds = new google.maps.LatLngBounds();

                points.forEach(function (point) {
                    const marker = createClientMarker(point);

                    marker.addListener('click', function () {
                        clientMapInfoWindow.setContent(buildClientMapPopup(point));
                        clientMapInfoWindow.open(clientMap, marker);
                    });

                    clientMapMarkers.push(marker);
                    bounds.extend(marker.getPosition());
                });

                if (points.length === 1) {
                    clientMap.setCenter(bounds.getCenter());
                    clientMap.setZoom(17);
                } else {
                    clientMap.fitBounds(bounds, 40);
                }

                setClientMapStatus('Mostrando ' + points.length + ' clientes con coordenadas registradas.', 'good');
                applyClientMapType();
            }

            function resetClientMapView() {
                renderClientMapFromPayload();
            }

            function switchClientMapMode(mode) {
                activeClientMapMode = mode;
                applyClientMapType();
            }

            function toggleClientMapLabels() {
                clientMapLabelsVisible = !clientMapLabelsVisible;
                applyClientMapType();
            }

            function prepareClientMapRefresh() {
                if (clientMapRefreshHookBound) {
                    return;
                }

                if (window.livewire && typeof window.livewire.hook === 'function') {
                    clientMapRefreshHookBound = true;
                    window.livewire.hook('message.processed', function () {
                        renderClientMapFromPayload();
                    });
                }
            }

            window.initClientMap = function () {
                isGoogleClientMapReady = true;
                buildClientMap();
                renderClientMapFromPayload();
                prepareClientMapRefresh();
            };

            document.addEventListener('livewire:load', function () {
                prepareClientMapRefresh();

                @if (empty($googleMapsApiKey))
                    setClientMapStatus('Configura GOOGLE_MAPS_API_KEY para usar Google Maps en este modulo.', 'warning');
                @endif

                if (window.google && window.google.maps) {
                    window.initClientMap();
                }
            });
        </script>

        @if (!empty($googleMapsApiKey))
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initClientMap"></script>
        @endif
    </x-slot>
</x-app-layout>
