@php
    $mapId = $mapId ?? 'building-location-picker';
    $applyButtonId = $mapId . '-apply';
    $selectedLatId = $mapId . '-selected-lat';
    $selectedLngId = $mapId . '-selected-lng';
    $currentLatId = $mapId . '-current-lat';
    $currentLngId = $mapId . '-current-lng';
    $currentLatValue = old('lat', $currentLat ?? '');
    $currentLngValue = old('lng', $currentLng ?? '');
    $defaultLat = $defaultLat ?? config('services.google.default_position.lat');
    $defaultLng = $defaultLng ?? config('services.google.default_position.lng');
@endphp

<div class="mb-4">
    <label class="form-label">@lang('Select location from map')</label>
    <div
        id="{{ $mapId }}"
        class="border rounded"
        style="height: 320px;"
        data-default-lat="{{ $defaultLat }}"
        data-default-lng="{{ $defaultLng }}"
    ></div>
    <div class="row gx-3 gy-2 align-items-center mt-2 small text-muted">
        <div class="col-md-4">
            <span class="fw-semibold">@lang('Current position'):</span>
            <span id="{{ $currentLatId }}">{{ $currentLatValue ?: '―' }}</span>,
            <span id="{{ $currentLngId }}">{{ $currentLngValue ?: '―' }}</span>
        </div>
        <div class="col-md-4">
            <span class="fw-semibold">@lang('Selected on map'):</span>
            <span id="{{ $selectedLatId }}">―</span>,
            <span id="{{ $selectedLngId }}">―</span>
        </div>
        <div class="col-md-4 text-md-end">
            <button type="button" class="btn btn-outline-primary btn-sm" id="{{ $applyButtonId }}" disabled>
                @lang('Use this location')
            </button>
        </div>
    </div>
    <div class="form-text">
        @lang('Click on the map to drop a temporary marker, then choose "Use this location" to copy the latitude and longitude into the form.')
    </div>
</div>

@once('building-location-picker-style')
    @push('styles')
        <style>
            @keyframes building-picker-pulse {
                0% {
                    transform: scale(0.9);
                    opacity: 0.7;
                }
                70% {
                    transform: scale(1.15);
                    opacity: 0;
                }
                100% {
                    opacity: 0;
                }
            }
        </style>
    @endpush
@endonce

@push('scripts')
<script>
    window.__buildingLocationPickers = window.__buildingLocationPickers || [];
    window.__buildingLocationPickers.push({
        mapId: '{{ $mapId }}',
        defaultLat: {{ $defaultLat }},
        defaultLng: {{ $defaultLng }},
        applyButtonId: '{{ $applyButtonId }}',
        selectedLatId: '{{ $selectedLatId }}',
        selectedLngId: '{{ $selectedLngId }}',
        currentLatId: '{{ $currentLatId }}',
        currentLngId: '{{ $currentLngId }}',
    });

    if (!window.__initBuildingLocationPickerSetup) {
        window.__initBuildingLocationPickerSetup = true;

        window.__setupBuildingLocationPicker = function setupBuildingLocationPicker(config) {
            const mapElement = document.getElementById(config.mapId);
            if (!mapElement || mapElement.dataset.mapInitialized === 'true') {
                return;
            }
            mapElement.dataset.mapInitialized = 'true';

            const latInput = document.getElementById('lat');
            const lngInput = document.getElementById('lng');
            if (!latInput || !lngInput) {
                return;
            }

            const applyButton = document.getElementById(config.applyButtonId);
            const selectedLatEl = document.getElementById(config.selectedLatId);
            const selectedLngEl = document.getElementById(config.selectedLngId);
            const currentLatEl = document.getElementById(config.currentLatId);
            const currentLngEl = document.getElementById(config.currentLngId);

            const parsedLat = parseFloat(latInput.value);
            const parsedLng = parseFloat(lngInput.value);

            const startingLat = Number.isFinite(parsedLat) ? parsedLat : config.defaultLat;
            const startingLng = Number.isFinite(parsedLng) ? parsedLng : config.defaultLng;

            const map = new google.maps.Map(mapElement, {
                center: { lat: startingLat, lng: startingLng },
                zoom: Number.isFinite(parsedLat) && Number.isFinite(parsedLng) ? 17 : 15,
                mapId: 'building_location_picker',
                zoomControl: true,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false,
                clickableIcons: false,
            });

            let currentMarker = null;
            let previewMarker = null;
            let pendingPosition = null;
            const AdvancedMarkerElement = google?.maps?.marker?.AdvancedMarkerElement || null;
            const PinElement = google?.maps?.marker?.PinElement || null;

            const createMarkerContent = (options = {}) => {
                const size = options.size || 18;
                const border = options.border || 3;
                const color = options.color || '#0ea5e9';
                const borderColor = options.borderColor || '#bae6fd';

                const container = document.createElement('div');
                container.style.width = `${size}px`;
                container.style.height = `${size}px`;
                container.style.borderRadius = '999px';
                container.style.border = `${border}px solid ${borderColor}`;
                container.style.background = color;
                container.style.boxShadow = '0 4px 10px rgba(14, 165, 233, 0.35)';
                container.style.display = 'inline-block';

                if (options.pulse) {
                    const ripple = document.createElement('div');
                    ripple.style.position = 'absolute';
                    ripple.style.width = `${size * 1.8}px`;
                    ripple.style.height = `${size * 1.8}px`;
                    ripple.style.borderRadius = '999px';
                    ripple.style.background = 'rgba(14, 165, 233, 0.25)';
                    ripple.style.animation = 'building-picker-pulse 2s infinite';
                    ripple.style.transform = 'translate(-50%, -50%)';

                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';
                    wrapper.style.width = `${size * 1.8}px`;
                    wrapper.style.height = `${size * 1.8}px`;
                    wrapper.style.display = 'inline-flex';
                    wrapper.style.alignItems = 'center';
                    wrapper.style.justifyContent = 'center';
                    wrapper.appendChild(ripple);
                    wrapper.appendChild(container);
                    return wrapper;
                }

                return container;
            };

            const updateCurrentDisplay = () => {
                currentLatEl.textContent = latInput.value ? Number(latInput.value).toFixed(7) : '―';
                currentLngEl.textContent = lngInput.value ? Number(lngInput.value).toFixed(7) : '―';
            };

            const setCurrentMarker = (lat, lng, pan = false) => {
                if (!google?.maps) {
                    return;
                }
                const position = { lat, lng };
                if (!currentMarker) {
                    if (AdvancedMarkerElement && PinElement) {
                        const pin = new PinElement();
                        currentMarker = new AdvancedMarkerElement({
                            map,
                            position,
                            content: pin.element,
                        });
                    } else {
                        currentMarker = new google.maps.Marker({
                            map,
                            position,
                            draggable: false,
                        });
                    }
                } else if (currentMarker.position !== undefined) {
                    currentMarker.position = position;
                    currentMarker.map = map;
                } else {
                    currentMarker.setPosition(position);
                    currentMarker.setMap(map);
                }

                if (pan) {
                    map.panTo(position);
                    map.setZoom(17);
                }
            };

            if (Number.isFinite(parsedLat) && Number.isFinite(parsedLng)) {
                setCurrentMarker(parsedLat, parsedLng, true);
            }

            updateCurrentDisplay();

            map.addListener('click', (event) => {
                const lat = parseFloat(event.latLng.lat().toFixed(7));
                const lng = parseFloat(event.latLng.lng().toFixed(7));
                pendingPosition = { lat, lng };

                if (!previewMarker) {
                    if (AdvancedMarkerElement) {
                        previewMarker = new AdvancedMarkerElement({
                            map,
                            position: event.latLng,
                            content: createMarkerContent({ size: 14, border: 2 }),
                        });
                    } else {
                        previewMarker = new google.maps.Marker({
                            map,
                            position: event.latLng,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 6,
                                fillColor: '#0ea5e9',
                                fillOpacity: 1,
                                strokeColor: '#0284c7',
                                strokeWeight: 2,
                            },
                        });
                    }
                } else if (typeof previewMarker.setPosition === 'function') {
                    previewMarker.setPosition(event.latLng);
                    previewMarker.setMap(map);
                } else {
                    previewMarker.position = event.latLng;
                    previewMarker.map = map;
                }

                selectedLatEl.textContent = lat.toFixed(7);
                selectedLngEl.textContent = lng.toFixed(7);
                if (applyButton) {
                    applyButton.disabled = false;
                }
            });

            if (applyButton) {
                applyButton.addEventListener('click', () => {
                    if (!pendingPosition) {
                        return;
                    }

                    latInput.value = pendingPosition.lat.toFixed(7);
                    lngInput.value = pendingPosition.lng.toFixed(7);
                    setCurrentMarker(pendingPosition.lat, pendingPosition.lng, true);
                    updateCurrentDisplay();

                    if (previewMarker) {
                        if (previewMarker.map !== undefined) {
                            previewMarker.map = null;
                        } else {
                            previewMarker.setMap(null);
                        }
                        previewMarker = null;
                    }
                    pendingPosition = null;
                    selectedLatEl.textContent = '―';
                    selectedLngEl.textContent = '―';
                    applyButton.disabled = true;
                });
            }

            const handleManualChange = () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (Number.isFinite(lat) && Number.isFinite(lng)) {
                    setCurrentMarker(lat, lng, true);
                } else if (currentMarker) {
                    if (currentMarker.map !== undefined) {
                        currentMarker.map = null;
                    } else {
                        currentMarker.setMap(null);
                    }
                    currentMarker = null;
                }
                updateCurrentDisplay();
            };

            latInput.addEventListener('change', handleManualChange);
            lngInput.addEventListener('change', handleManualChange);
        };

        window.initBuildingLocationPicker = function () {
            (window.__buildingLocationPickers || []).forEach((config) => {
                window.__setupBuildingLocationPicker(config);
            });
        };
    }
</script>
@once('building-location-picker-script')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initBuildingLocationPicker" async defer></script>
@endonce
@endpush
