@if(!empty($googleMapsApiKey))
    @php
        $initialLat = $initialCenterLat ?? ($defaultPosition['lat'] ?? 35.0238868);
        $initialLng = $initialCenterLng ?? ($defaultPosition['lng'] ?? 135.760201);
        $fallbackLat = $defaultPosition['lat'] ?? 35.0238868;
        $fallbackLng = $defaultPosition['lng'] ?? 135.760201;
    @endphp

    <div class="mb-3">
        <label class="form-label">@lang('Map Preview')</label>
        <div
            id="boundary-map"
            class="border rounded"
            style="height: 320px;"
            data-initial-lat="{{ $initialLat }}"
            data-initial-lng="{{ $initialLng }}"
            data-default-lat="{{ $fallbackLat }}"
            data-default-lng="{{ $fallbackLng }}"
        ></div>
        <div id="boundary-map-error" class="form-text text-danger d-none">@lang('Failed to parse KML. Please check the format or try again.')</div>
        <div class="form-text">@lang('Valid KML updates will be shown on the map automatically.')</div>
        <style>
            #boundary-map .marker-label {
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                background: rgba(33, 33, 33, 0.75);
                color: #fff;
                padding: 6px 10px;
                border-radius: 6px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
                font-size: 13px;
                line-height: 1.35;
                transform: translateY(24px);
                white-space: nowrap;
                max-width: 260px;
            }
            #boundary-map .marker-label__title {
                font-weight: 600;
                font-size: 11px;
            }
            #boundary-map .marker-label__desc {
                margin-top: 4px;
                font-size: 10px;
                opacity: 0.9;
            }
        </style>
    </div>

    <script src="{{ asset('js/kml-parser.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initBoundaryKmlMap" async defer></script>
    <script>
        async function initBoundaryKmlMap() {
        const textarea = document.getElementById('boundary_kml');
        const fileInput = document.getElementById('boundary_kml_upload');
        const mapElement = document.getElementById('boundary-map');
        const errorElement = document.getElementById('boundary-map-error');
        const defaultErrorMessage = errorElement ? errorElement.textContent : '';

        if (!textarea || !mapElement) {
            return;
        }

        const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');

        const parseNumber = (value, fallback) => {
            const number = Number.parseFloat(value);
            return Number.isFinite(number) ? number : fallback;
        };

        const mapDefaultLat = parseNumber(mapElement.dataset.defaultLat, 35.0238868);
        const mapDefaultLng = parseNumber(mapElement.dataset.defaultLng, 135.760201);
        const initialCenter = {
            lat: parseNumber(mapElement.dataset.initialLat, mapDefaultLat),
            lng: parseNumber(mapElement.dataset.initialLng, mapDefaultLng),
        };

        const map = new google.maps.Map(mapElement, {
            center: initialCenter,
            zoom: 10,
            mapId: 'roadmap',
            fullscreenControl: false,
        });

        const centerLatInput = document.getElementById('center_lat');
        const centerLngInput = document.getElementById('center_lng');
        const syncCenterInputs = () => {
            if (!centerLatInput || !centerLngInput) {
                return;
            }
            const center = map.getCenter();
            if (!center) {
                return;
            }
            centerLatInput.value = center.lat().toFixed(7);
            centerLngInput.value = center.lng().toFixed(7);
        };
        syncCenterInputs();
        map.addListener('idle', syncCenterInputs);

        const polygons = [];
        const markers = [];
        const kmlUtils = window.kmlParser;
        if (!kmlUtils) {
            console.error('kmlParser utilities are not loaded.');
            return;
        }
        const {
            parseDocument,
            parseStyles,
            parsePolygons,
            parsePoints,
            parseKmlColor,
        } = kmlUtils;

        const resolveStyle = (styleUrl, styles) => {
            if (!styleUrl) {
                return {};
            }
            return styles[styleUrl] || styles[styleUrl.replace(/^#/, '')] || {};
        };

        const renderKml = () => {
            clearTimeout(renderKml.debounceId);
            renderKml.debounceId = setTimeout(() => {
                polygons.forEach(polygon => polygon.setMap(null));
                polygons.length = 0;
                markers.forEach((marker) => {
                    marker.map = null;
                });
                markers.length = 0;
                if (errorElement) {
                    errorElement.textContent = defaultErrorMessage;
                    errorElement.classList.add('d-none');
                }

                const raw = textarea.value.trim();
                if (!raw) {
                    return;
                }

                try {
                    const xml = parseDocument(raw);
                    if (!xml || xml.querySelector('parsererror')) {
                        throw new Error('invalid_xml');
                    }

                    const bounds = new google.maps.LatLngBounds();
                    const styles = parseStyles(xml);
                    const polygonData = parsePolygons(xml);
                    const pointData = parsePoints(xml);
                    let geometryCount = 0;

                    polygonData.forEach((polygonDatum) => {
                        if (!Array.isArray(polygonDatum.coordinates) || polygonDatum.coordinates.length < 3) {
                            return;
                        }

                        polygonDatum.coordinates.forEach((point) => bounds.extend(point));

                        const style = resolveStyle(polygonDatum.styleUrl, styles);
                        const strokeEnabled = style.polyOutline !== '0';
                        const fillEnabled = style.polyFill !== '0';
                        const stroke = parseKmlColor(style.lineColor, '#8e24aa', strokeEnabled ? 1 : 0);
                        const fill = parseKmlColor(style.polyColor, '#ba68c8', fillEnabled ? 0.3 : 0);
                        const strokeWeight = Number.parseFloat(style.lineWidth);

                        const polygon = new google.maps.Polygon({
                            paths: polygonDatum.coordinates,
                            strokeColor: stroke.color,
                            strokeOpacity: stroke.opacity,
                            strokeWeight: Number.isFinite(strokeWeight) ? strokeWeight : 2,
                            fillColor: fill.color,
                            fillOpacity: fill.opacity,
                        });

                        polygon.setMap(map);
                        polygons.push(polygon);
                        geometryCount++;
                    });

                    pointData.forEach((point) => {
                        if (!Number.isFinite(point.lat) || !Number.isFinite(point.lng)) {
                            return;
                        }

                        const title = point.name?.trim();
                        const description = point.description?.trim();

                        const container = document.createElement('div');
                        container.className = 'marker-label';

                        const titleEl = document.createElement('div');
                        titleEl.className = 'marker-label__title';
                        titleEl.textContent = title || '{{ __("Point") }}';
                        container.appendChild(titleEl);

                        if (description) {
                            const descEl = document.createElement('div');
                            descEl.className = 'marker-label__desc';
                            descEl.textContent = description;
                            container.appendChild(descEl);
                        }

                        const marker = new AdvancedMarkerElement({
                            map,
                            position: { lat: point.lat, lng: point.lng },
                            content: container,
                            title: title || '',
                            collisionBehavior: google.maps.CollisionBehavior.OPTIONAL_AND_HIDES_LOWER_PRIORITY,
                        });

                        markers.push(marker);
                        bounds.extend({ lat: point.lat, lng: point.lng });
                        geometryCount++;
                    });

                    if (!geometryCount) {
                        throw new Error('no_coordinates');
                    }

                    if (!bounds.isEmpty()) {
                        map.fitBounds(bounds);
                    }
                } catch (error) {
                    console.error('Failed to render KML preview.', error);
                    if (errorElement) {
                        errorElement.textContent = defaultErrorMessage;
                        errorElement.classList.remove('d-none');
                    }
                }
            }, 250);
        };

        textarea.addEventListener('input', renderKml);
        if (fileInput) {
            fileInput.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }

                if (!file.name.toLowerCase().endsWith('.kml')) {
                    if (errorElement) {
                        errorElement.textContent = @json(__('Only .kml files can be uploaded.'));
                        errorElement.classList.remove('d-none');
                    }
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    textarea.value = e.target?.result || '';
                    renderKml();
                };
                reader.onerror = () => {
                    if (errorElement) {
                        errorElement.textContent = @json(__('Failed to read the selected file.'));
                        errorElement.classList.remove('d-none');
                    }
                };
                reader.readAsText(file);
            });
        }
            renderKml();
        }
    </script>
@endif
