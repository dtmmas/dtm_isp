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
            let clientInfrastructureMarkers = [];
            let clientFiberLines = [];
            let clientCoverageShapes = [];
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
                    return JSON.parse(payloadElement.textContent || '{"points":[],"assets":[],"fiberRoutes":[],"coverages":[]}');
                } catch (error) {
                    console.error('No fue posible leer el payload del mapa de clientes.', error);
                    return { points: [], assets: [], fiberRoutes: [], coverages: [] };
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

            function clientBillingMarkerColor(point) {
                if (point.billingStatus === 'deuda') {
                    return '#dc2626';
                }

                if (point.status === '0') {
                    return '#f59e0b';
                }

                return '#16a34a';
            }

            function clientTicketPriorityColor(priorityColor) {
                return priorityColor || '#2563eb';
            }

            function buildClientTicketMarkerSvg(point) {
                const pinColor = clientTicketPriorityColor(point.openTicketPriorityColor);
                const strokeColor = '#ffffff';

                return ''
                    + '<svg xmlns="http://www.w3.org/2000/svg" width="42" height="52" viewBox="0 0 42 52">'
                    + '  <path d="M21 2C11.6 2 4 9.6 4 19c0 12.5 14.1 26.1 16 29 1.9-2.9 18-16.5 18-29C38 9.6 30.4 2 21 2z" fill="' + pinColor + '" stroke="' + strokeColor + '" stroke-width="2"/>'
                    + '  <circle cx="21" cy="20" r="10" fill="#ffffff" fill-opacity="0.96"/>'
                    + '  <path d="M21 12c-1.7 0-3 1.3-3 3v6.5c0 1.7 1.3 3 3 3s3-1.3 3-3V15c0-1.7-1.3-3-3-3z" fill="' + pinColor + '"/>'
                    + '  <circle cx="21" cy="29" r="2.3" fill="' + pinColor + '"/>'
                    + '</svg>';
            }

            function buildClientMarkerIcon(point) {
                if (point.hasOpenTickets) {
                    return {
                        url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(buildClientTicketMarkerSvg(point)),
                        scaledSize: new google.maps.Size(42, 52),
                        anchor: new google.maps.Point(21, 50),
                    };
                }

                return {
                    path: google.maps.SymbolPath.CIRCLE,
                    fillColor: clientBillingMarkerColor(point),
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 3,
                    scale: 9
                };
            }

            function infrastructureIconSvg(asset) {
                const color = asset.assetColor || '#475569';
                const icon = asset.assetIcon || 'asset';
                let glyph = '';

                switch (icon) {
                    case 'post':
                        glyph = '<rect x="18" y="8" width="6" height="28" rx="2" fill="#fff"/><rect x="10" y="14" width="22" height="4" rx="2" fill="#fff"/>';
                        break;
                    case 'box':
                        glyph = '<rect x="10" y="12" width="22" height="18" rx="4" fill="#fff"/><path d="M10 18h22" stroke="' + color + '" stroke-width="2"/>';
                        break;
                    case 'olt':
                        glyph = '<rect x="8" y="12" width="26" height="16" rx="3" fill="#fff"/><circle cx="14" cy="20" r="2" fill="' + color + '"/><circle cx="21" cy="20" r="2" fill="' + color + '"/><circle cx="28" cy="20" r="2" fill="' + color + '"/>';
                        break;
                    case 'onu':
                        glyph = '<rect x="11" y="13" width="20" height="14" rx="3" fill="#fff"/><path d="M15 31h12" stroke="#fff" stroke-width="3" stroke-linecap="round"/>';
                        break;
                    case 'antenna':
                        glyph = '<circle cx="21" cy="20" r="4" fill="#fff"/><path d="M21 8v8M13 14c4-4 12-4 16 0M9 10c6-6 18-6 24 0" stroke="#fff" stroke-width="3" stroke-linecap="round" fill="none"/>';
                        break;
                    default:
                        glyph = '<circle cx="21" cy="21" r="7" fill="#fff"/>';
                        break;
                }

                return ''
                    + '<svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 42 42">'
                    + '  <circle cx="21" cy="21" r="18" fill="' + color + '" stroke="#ffffff" stroke-width="2"/>'
                    + glyph
                    + '</svg>';
            }

            function buildInfrastructureMarkerIcon(asset) {
                return {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(infrastructureIconSvg(asset)),
                    scaledSize: new google.maps.Size(34, 34),
                    anchor: new google.maps.Point(17, 17),
                };
            }

            function createClientMarker(point) {
                return new google.maps.Marker({
                    position: { lat: Number(point.latitude), lng: Number(point.longitude) },
                    map: clientMap,
                    title: point.clientName,
                    icon: buildClientMarkerIcon(point)
                });
            }

            function createInfrastructureMarker(asset) {
                return new google.maps.Marker({
                    position: { lat: Number(asset.latitude), lng: Number(asset.longitude) },
                    map: clientMap,
                    title: asset.name,
                    icon: buildInfrastructureMarkerIcon(asset)
                });
            }

            function buildClientMapPopup(point) {
                const popupActions = []
                    .concat(point.canOpenClient && point.clientUrl
                        ? ['      <a href="' + escapeClientMapHtml(point.clientUrl) + '" class="client-map-popup-link">Cliente</a>']
                        : [])
                    .concat(point.canOpenPlan && point.planUrl
                        ? ['      <a href="' + escapeClientMapHtml(point.planUrl) + '" class="client-map-popup-link client-map-popup-link-primary">Plan</a>']
                        : [])
                    .concat(point.canOpenTickets && point.ticketsUrl
                        ? ['      <a href="' + escapeClientMapHtml(point.ticketsUrl) + '" class="client-map-popup-link client-map-popup-link-primary">Tickets</a>']
                        : [])
                    .join('');

                return ''
                    + '<div class="client-map-popup">'
                    + '  <div class="client-map-popup-title">' + escapeClientMapHtml(point.clientName) + '</div>'
                    + '  <div class="client-map-popup-chip client-map-popup-chip-' + escapeClientMapHtml(point.billingTone) + '">' + escapeClientMapHtml(point.billingLabel) + '</div>'
                    + (point.hasOpenTickets
                        ? '  <div class="client-map-popup-row"><strong>Tickets abiertos:</strong> ' + escapeClientMapHtml(point.openTicketsCount) + ' (' + escapeClientMapHtml(point.openTicketPriorityLabel || 'Sin prioridad') + ')</div>'
                        : '')
                    + '  <div class="client-map-popup-row"><strong>Plan:</strong> ' + escapeClientMapHtml(point.productName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Estado de conexion:</strong> ' + escapeClientMapHtml(point.statusLabel) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Estado de facturacion:</strong> ' + escapeClientMapHtml(point.billingLabel) + '</div>'
                    + (Number(point.debtMonths) > 0
                        ? '  <div class="client-map-popup-row"><strong>Facturas pendientes:</strong> ' + escapeClientMapHtml(point.debtMonths) + '</div>'
                        : '')
                    + '  <div class="client-map-popup-row"><strong>Zona:</strong> ' + escapeClientMapHtml(point.zoneName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Nodo:</strong> ' + escapeClientMapHtml(point.nodeName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Direccion:</strong> ' + escapeClientMapHtml(point.address) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Telefono:</strong> ' + escapeClientMapHtml(point.clientPhone || 'Sin telefono') + '</div>'
                    + '  <div class="client-map-popup-actions">'
                    + popupActions
                    + '  </div>'
                    + '</div>';
            }

            function buildInfrastructurePopup(asset) {
                const metaRows = Object.entries(asset.meta || {}).map(function (entry) {
                    return '<div class="client-map-popup-row"><strong>' + escapeClientMapHtml(entry[0]) + ':</strong> ' + escapeClientMapHtml(entry[1]) + '</div>';
                }).join('');

                return ''
                    + '<div class="client-map-popup">'
                    + '  <div class="client-map-popup-title">' + escapeClientMapHtml(asset.name) + '</div>'
                    + '  <div class="client-map-popup-chip client-map-popup-chip-primary">' + escapeClientMapHtml(asset.assetLabel) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Codigo:</strong> ' + escapeClientMapHtml(asset.code || 'Sin codigo') + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Estado:</strong> ' + escapeClientMapHtml(asset.assetStatus || 'Activo') + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Zona:</strong> ' + escapeClientMapHtml(asset.zoneName) + '</div>'
                    + '  <div class="client-map-popup-row"><strong>Nodo:</strong> ' + escapeClientMapHtml(asset.nodeName) + '</div>'
                    + (asset.description ? '  <div class="client-map-popup-row"><strong>Detalle:</strong> ' + escapeClientMapHtml(asset.description) + '</div>' : '')
                    + metaRows
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

            function clearInfrastructureMarkers() {
                clientInfrastructureMarkers.forEach(function (marker) {
                    marker.setMap(null);
                });

                clientInfrastructureMarkers = [];
            }

            function clearFiberRoutes() {
                clientFiberLines.forEach(function (line) {
                    line.setMap(null);
                });

                clientFiberLines = [];
            }

            function clearCoverageShapes() {
                clientCoverageShapes.forEach(function (shape) {
                    shape.setMap(null);
                });

                clientCoverageShapes = [];
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
                const assets = Array.isArray(payload.assets) ? payload.assets : [];
                const fiberRoutes = Array.isArray(payload.fiberRoutes) ? payload.fiberRoutes : [];
                const coverages = Array.isArray(payload.coverages) ? payload.coverages : [];
                clearClientMarkers();
                clearInfrastructureMarkers();
                clearFiberRoutes();
                clearCoverageShapes();

                if (!points.length && !assets.length && !fiberRoutes.length && !coverages.length) {
                    clientMap.setCenter(defaultClientMapCoordinates);
                    clientMap.setZoom(defaultClientMapCoordinates.zoom);
                    setClientMapStatus('No hay elementos ISP para los filtros actuales.', 'muted');
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

                assets.forEach(function (asset) {
                    const marker = createInfrastructureMarker(asset);

                    marker.addListener('click', function () {
                        clientMapInfoWindow.setContent(buildInfrastructurePopup(asset));
                        clientMapInfoWindow.open(clientMap, marker);
                    });

                    clientInfrastructureMarkers.push(marker);
                    bounds.extend(marker.getPosition());
                });

                fiberRoutes.forEach(function (route) {
                    if (!Array.isArray(route.path) || !route.path.length) {
                        return;
                    }

                    const polyline = new google.maps.Polyline({
                        path: route.path,
                        geodesic: true,
                        strokeColor: route.strokeColor || '#0ea5e9',
                        strokeOpacity: 0.95,
                        strokeWeight: Number(route.strokeWeight || 4),
                        map: clientMap,
                    });

                    polyline.addListener('click', function (event) {
                        clientMapInfoWindow.setContent(
                            '<div class="client-map-popup">'
                            + '<div class="client-map-popup-title">' + escapeClientMapHtml(route.name) + '</div>'
                            + '<div class="client-map-popup-chip client-map-popup-chip-primary">Ruta de fibra</div>'
                            + '<div class="client-map-popup-row"><strong>Tipo:</strong> ' + escapeClientMapHtml(route.routeType || 'Fibra') + '</div>'
                            + (route.description ? '<div class="client-map-popup-row"><strong>Detalle:</strong> ' + escapeClientMapHtml(route.description) + '</div>' : '')
                            + '</div>'
                        );
                        clientMapInfoWindow.setPosition(event.latLng);
                        clientMapInfoWindow.open(clientMap);
                    });

                    clientFiberLines.push(polyline);
                    route.path.forEach(function (point) {
                        bounds.extend(point);
                    });
                });

                coverages.forEach(function (coverage) {
                    let shape = null;

                    if (coverage.coverageType === 'polygon' && Array.isArray(coverage.polygon) && coverage.polygon.length) {
                        shape = new google.maps.Polygon({
                            paths: coverage.polygon,
                            strokeColor: coverage.strokeColor || '#15803d',
                            strokeOpacity: 0.85,
                            strokeWeight: 2,
                            fillColor: coverage.fillColor || '#22c55e',
                            fillOpacity: 0.16,
                            map: clientMap,
                        });

                        coverage.polygon.forEach(function (point) {
                            bounds.extend(point);
                        });
                    } else {
                        shape = new google.maps.Circle({
                            strokeColor: coverage.strokeColor || '#15803d',
                            strokeOpacity: 0.85,
                            strokeWeight: 2,
                            fillColor: coverage.fillColor || '#22c55e',
                            fillOpacity: 0.16,
                            map: clientMap,
                            center: coverage.center,
                            radius: Number(coverage.radiusMeters || 0),
                        });

                        if (coverage.center && coverage.center.lat && coverage.center.lng) {
                            bounds.extend(coverage.center);
                        }
                    }

                    shape.addListener('click', function (event) {
                        clientMapInfoWindow.setContent(
                            '<div class="client-map-popup">'
                            + '<div class="client-map-popup-title">' + escapeClientMapHtml(coverage.name) + '</div>'
                            + '<div class="client-map-popup-chip client-map-popup-chip-success">Cobertura</div>'
                            + '<div class="client-map-popup-row"><strong>Tecnologia:</strong> ' + escapeClientMapHtml(coverage.technology || 'No definida') + '</div>'
                            + (coverage.description ? '<div class="client-map-popup-row"><strong>Detalle:</strong> ' + escapeClientMapHtml(coverage.description) + '</div>' : '')
                            + '</div>'
                        );
                        clientMapInfoWindow.setPosition(event.latLng || coverage.center);
                        clientMapInfoWindow.open(clientMap);
                    });

                    clientCoverageShapes.push(shape);
                });

                const totalRenderedItems = points.length + assets.length + fiberRoutes.length + coverages.length;

                if (totalRenderedItems === 1 && !bounds.isEmpty()) {
                    clientMap.setCenter(bounds.getCenter());
                    clientMap.setZoom(17);
                } else if (!bounds.isEmpty()) {
                    clientMap.fitBounds(bounds, 40);
                }

                setClientMapStatus(
                    'Mostrando ' + points.length + ' clientes, '
                    + assets.length + ' activos de red, '
                    + fiberRoutes.length + ' rutas de fibra y '
                    + coverages.length + ' coberturas.',
                    'good'
                );
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
