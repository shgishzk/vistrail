@extends('admin.layouts.app')

@section('title', __('Edit Area'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Edit Area')</strong>
        <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to Areas')
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.areas.update', $area) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="number" class="form-label">@lang('Number')</label>
                <input type="text" class="form-control @error('number') is-invalid @enderror" id="number" name="number" value="{{ old('number', $area->number) }}" required>
                @error('number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="name" class="form-label">@lang('Area Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $area->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="boundary_kml_upload" class="form-label">@lang('Upload KML')</label>
                <input type="file" class="form-control" id="boundary_kml_upload" accept=".kml">
                <div class="form-text">@lang('Select a .kml file to load into the editor.')</div>
            </div>

            <div class="mb-3">
                <label for="boundary_kml" class="form-label">@lang('Boundary KML')</label>
                <textarea class="form-control @error('boundary_kml') is-invalid @enderror" id="boundary_kml" name="boundary_kml" rows="8" required>{{ old('boundary_kml', $area->boundary_kml) }}</textarea>
                <div class="form-text">
                    @lang('Paste KML data from Google Maps here.')
                </div>
                @error('boundary_kml')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(!empty($googleMapsApiKey))
                <div class="mb-3">
                    <label class="form-label">@lang('Map Preview')</label>
                    <div id="boundary-map" class="border rounded" style="height: 320px;"></div>
                    <div id="boundary-map-error" class="form-text text-danger d-none">@lang('Failed to parse KML. Please check the format or try again.')</div>
                    <div class="form-text">@lang('Valid KML updates will be shown on the map automatically.')</div>
                </div>

                <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initBoundaryKmlMap" async defer></script>
                <script>
                    async function initBoundaryKmlMap() {
                        const textarea = document.getElementById('boundary_kml');
                        const fileInput = document.getElementById('boundary_kml_upload');
                        const mapElement = document.getElementById('boundary-map');
                        const errorElement = document.getElementById('boundary-map-error');

                        if (!textarea || !mapElement) {
                            return;
                        }

                        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary('marker');

                        const defaultCenter = { lat: 35.024842, lng: 135.762106 };
                        const map = new google.maps.Map(mapElement, {
                            center: defaultCenter,
                            zoom: 10,
                            mapId: 'roadmap'
                        });

                        const polygons = [];
                        const markers = [];
                        let infoWindow = null;

                        const parseKmlColor = (value, defaultColor, defaultOpacity) => {
                            if (!value) {
                                return { color: defaultColor, opacity: defaultOpacity };
                            }

                            let color = value.trim();
                            if (color.startsWith('#')) {
                                color = color.slice(1);
                            }
                            if (color.length === 6) {
                                color = `ff${color}`;
                            }
                            if (color.length !== 8) {
                                return { color: defaultColor, opacity: defaultOpacity };
                            }

                            const alpha = parseInt(color.slice(0, 2), 16);
                            const blue = color.slice(2, 4);
                            const green = color.slice(4, 6);
                            const red = color.slice(6, 8);

                            const opacity = Number.isNaN(alpha) ? defaultOpacity : Math.min(Math.max(alpha / 255, 0), 1);
                            return {
                                color: `#${red}${green}${blue}`,
                                opacity,
                            };
                        };

                        const parseCoordinates = (text) => {
                            if (!text) {
                                return [];
                            }

                            return text
                                .trim()
                                .split(/\s+/)
                                .map(pair => {
                                    const [lng, lat] = pair.split(',');
                                    const parsedLng = parseFloat(lng);
                                    const parsedLat = parseFloat(lat);
                                    if (Number.isNaN(parsedLng) || Number.isNaN(parsedLat)) {
                                        return null;
                                    }
                                    return { lat: parsedLat, lng: parsedLng };
                                })
                                .filter(Boolean);
                        };

                        const parseStyles = (xml) => {
                            const styles = {};

                            const registerStyle = (key, data) => {
                                if (!key || !data) {
                                    return;
                                }

                                const normalized = key.startsWith('#') ? key.slice(1) : key;
                                const copy = { ...data };

                                styles[`#${normalized}`] = copy;
                                styles[normalized] = { ...copy };
                            };

                            Array.from(xml.getElementsByTagName('Style')).forEach(styleNode => {
                                const id = styleNode.getAttribute('id');
                                if (!id) {
                                    return;
                                }

                                registerStyle(id, {
                                    lineColor: styleNode.querySelector('LineStyle > color')?.textContent?.trim() || null,
                                    lineWidth: styleNode.querySelector('LineStyle > width')?.textContent?.trim() || null,
                                    polyColor: styleNode.querySelector('PolyStyle > color')?.textContent?.trim() || null,
                                    polyFill: styleNode.querySelector('PolyStyle > fill')?.textContent?.trim() || null,
                                    polyOutline: styleNode.querySelector('PolyStyle > outline')?.textContent?.trim() || null,
                                    iconColor: styleNode.querySelector('IconStyle > color')?.textContent?.trim() || null,
                                });
                            });

                            Array.from(xml.getElementsByTagName('StyleMap')).forEach(styleMapNode => {
                                const id = styleMapNode.getAttribute('id');
                                if (!id) {
                                    return;
                                }

                                const normalPair = Array.from(styleMapNode.getElementsByTagName('Pair')).find(pair => {
                                    return pair.querySelector('key')?.textContent?.trim() === 'normal';
                                });

                                const styleUrl = normalPair?.querySelector('styleUrl')?.textContent?.trim();
                                if (!styleUrl) {
                                    return;
                                }

                                const referenced = styles[styleUrl] || styles[styleUrl.replace(/^#/, '')];
                                if (referenced) {
                                    registerStyle(id, referenced);
                                }
                            });

                            return styles;
                        };

                        const renderKml = () => {
                            clearTimeout(renderKml.debounceId);
                            renderKml.debounceId = setTimeout(() => {
                                polygons.forEach(polygon => polygon.setMap(null));
                                polygons.length = 0;
                                markers.forEach(marker => {
                                    marker.map = null;
                                });
                                markers.length = 0;
                                if (infoWindow) {
                                    infoWindow.close();
                                }
                                if (errorElement) {
                                    errorElement.classList.add('d-none');
                                }

                                const raw = textarea.value.trim();
                                if (!raw) {
                                    return;
                                }

                                try {
                                    const parser = new DOMParser();
                                    const xml = parser.parseFromString(raw, 'application/xml');

                                    if (xml.querySelector('parsererror')) {
                                        throw new Error('invalid_xml');
                                    }

                                    const styles = parseStyles(xml);
                                    const placemarks = Array.from(xml.getElementsByTagName('Placemark'));
                                    const bounds = new google.maps.LatLngBounds();
                                    let geometryCount = 0;

                                    placemarks.forEach(placemark => {
                                        const styleUrl = placemark.querySelector('styleUrl')?.textContent?.trim();
                                        const style = (styleUrl && (styles[styleUrl] || styles[styleUrl.replace(/^#/, '')])) || {};
                                        const name = placemark.querySelector('name')?.textContent?.trim() || null;

                                        Array.from(placemark.getElementsByTagName('Polygon')).forEach(polygonNode => {
                                            const coordinateNode = polygonNode.querySelector('outerBoundaryIs coordinates') || polygonNode.querySelector('coordinates');
                                            const points = parseCoordinates(coordinateNode?.textContent || '');
                                            if (points.length < 3) {
                                                return;
                                            }

                                            points.forEach(point => bounds.extend(point));

                                            const strokeEnabled = style.polyOutline !== '0';
                                            const fillEnabled = style.polyFill !== '0';
                                            const stroke = parseKmlColor(style.lineColor, '#FF0000', strokeEnabled ? 0.8 : 0);
                                            const fill = parseKmlColor(style.polyColor, '#FF0000', fillEnabled ? 0.15 : 0);
                                            const strokeWeight = Number.parseFloat(style.lineWidth);

                                            const polygon = new google.maps.Polygon({
                                                paths: points,
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

                                        Array.from(placemark.getElementsByTagName('Point')).forEach(pointNode => {
                                            const coordinateNode = pointNode.querySelector('coordinates');
                                            const points = parseCoordinates(coordinateNode?.textContent || '');
                                            if (!points.length) {
                                                return;
                                            }

                                            const markerStyle = parseKmlColor(style.iconColor || style.lineColor || style.polyColor, '#0288D1', 1);
                                            const pin = new PinElement({
                                                background: markerStyle.color,
                                                borderColor: '#ffffff',
                                                glyphColor: '#000000',
                                                scale: 1.2,
                                            });
                                            pin.element.style.opacity = markerStyle.opacity;

                                            const marker = new AdvancedMarkerElement({
                                                map,
                                                position: points[0],
                                                content: pin.element,
                                            });

                                            markers.push(marker);
                                            bounds.extend(points[0]);
                                            geometryCount++;

                                            const description = placemark.querySelector('description')?.textContent?.trim() || '';

                                            if (name || description) {
                                                marker.addListener('click', () => {
                                                    if (!infoWindow) {
                                                        infoWindow = new google.maps.InfoWindow();
                                                    }

                                                    const titleHtml = name ? `<div class="fw-bold mb-1" style="font-size:1.05rem;">${name}</div>` : '';
                                                    const descriptionHtml = description
                                                        ? `<div class="text-muted" style="white-space: pre-line;font-size:0.95rem;">${description}</div>`
                                                        : '';

                                                    infoWindow.setContent(`<div>${titleHtml}${descriptionHtml}</div>`);
                                                    infoWindow.open({
                                                        anchor: marker,
                                                        map,
                                                    });
                                                });
                                            }
                                        });
                                    });

                                    if (!geometryCount) {
                                        throw new Error('no_coordinates');
                                    }

                                    if (!bounds.isEmpty()) {
                                        map.fitBounds(bounds);
                                    }
                                } catch (error) {
                                    if (errorElement) {
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
            
            <div class="mb-3">
                <label for="memo" class="form-label">@lang('Memo')</label>
                <textarea class="form-control @error('memo') is-invalid @enderror" id="memo" name="memo" rows="3">{{ old('memo', $area->memo) }}</textarea>
                @error('memo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.areas') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('Update Area')</button>
            </div>
        </form>
    </div>
</div>
@endsection
