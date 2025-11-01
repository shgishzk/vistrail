@extends('admin.layouts.app')

@section('title', __('Edit Area'))

@section('content')
<div class="card">
    <div class="card-header">
        <strong>@lang('Edit Area')</strong>
    </div>
    <div class="card-body">
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
                <label for="name" class="form-label">@lang('Name')</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $area->name) }}">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                    function initBoundaryKmlMap() {
                        const textarea = document.getElementById('boundary_kml');
                        const mapElement = document.getElementById('boundary-map');
                        const errorElement = document.getElementById('boundary-map-error');

                        if (!textarea || !mapElement) {
                            return;
                        }

                        const defaultCenter = { lat: 35.024842, lng: 135.762106 };
                        const map = new google.maps.Map(mapElement, {
                            center: defaultCenter,
                            zoom: 10,
                            mapTypeId: 'roadmap'
                        });

                        const polygons = [];
                        const markers = [];

                        const renderKml = () => {
                            clearTimeout(renderKml.debounceId);
                            renderKml.debounceId = setTimeout(() => {
                                polygons.forEach(polygon => polygon.setMap(null));
                                polygons.length = 0;
                                markers.forEach(marker => marker.setMap(null));
                                markers.length = 0;
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

                                    const coordinateNodes = Array.from(xml.getElementsByTagName('coordinates'));
                                    const bounds = new google.maps.LatLngBounds();

                                    coordinateNodes.forEach(node => {
                                        const text = (node.textContent || '').trim();
                                        if (!text) {
                                            return;
                                        }

                                        const points = text
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

                                        if (points.length === 1) {
                                            const marker = new google.maps.Marker({
                                                position: points[0],
                                                map,
                                                icon: {
                                                    path: google.maps.SymbolPath.CIRCLE,
                                                    scale: 5,
                                                    fillColor: '#0288D1',
                                                    fillOpacity: 0.9,
                                                    strokeColor: '#ffffff',
                                                    strokeWeight: 1,
                                                },
                                            });
                                            markers.push(marker);
                                            bounds.extend(points[0]);
                                        } else if (points.length >= 3) {
                                            points.forEach(point => bounds.extend(point));

                                            const polygon = new google.maps.Polygon({
                                                paths: points,
                                                strokeColor: '#FF0000',
                                                strokeOpacity: 0.8,
                                                strokeWeight: 2,
                                                fillColor: '#FF0000',
                                                fillOpacity: 0.15,
                                            });

                                            polygon.setMap(map);
                                            polygons.push(polygon);
                                        }
                                    });

                                    if (!polygons.length && !markers.length) {
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
