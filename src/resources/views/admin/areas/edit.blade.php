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
                <label for="boundary_geojson" class="form-label">@lang('Boundary GeoJSON')</label>
                <textarea class="form-control @error('boundary_geojson') is-invalid @enderror" id="boundary_geojson" name="boundary_geojson" rows="8" required>{{ old('boundary_geojson', $area->boundary_geojson) }}</textarea>
                <div class="form-text">
                    @lang('Paste KML data from Google Maps here.')
                </div>
                @error('boundary_geojson')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if(!empty($googleMapsApiKey))
                <div class="mb-3">
                    <label class="form-label">@lang('Map Preview')</label>
                    <div id="boundary-map" class="border rounded" style="height: 320px;"></div>
                    <div id="boundary-map-error" class="form-text text-danger d-none">@lang('Failed to parse GeoJSON. Please check the format.')</div>
                    <div class="form-text">@lang('Valid GeoJSON updates will be shown on the map automatically.')</div>
                </div>

                <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initBoundaryGeoJsonMap" async defer></script>
                <script>
                    function initBoundaryGeoJsonMap() {
                        const textarea = document.getElementById('boundary_geojson');
                        const mapElement = document.getElementById('boundary-map');
                        const errorElement = document.getElementById('boundary-map-error');

                        if (!textarea || !mapElement) {
                            return;
                        }

                        const defaultCenter = { lat: 35.681236, lng: 139.767125 };
                        const map = new google.maps.Map(mapElement, {
                            center: defaultCenter,
                            zoom: 10,
                            mapTypeId: 'roadmap'
                        });

                        const clearDataLayer = () => {
                            map.data.forEach(feature => {
                                map.data.remove(feature);
                            });
                        };

                        const processPoints = (geometry, callback, thisArg) => {
                            if (!geometry) {
                                return;
                            }

                            if (geometry instanceof google.maps.LatLng) {
                                callback.call(thisArg, geometry);
                            } else if (geometry instanceof google.maps.Data.Point) {
                                callback.call(thisArg, geometry.get());
                            } else {
                                geometry.getArray().forEach(g => processPoints(g, callback, thisArg));
                            }
                        };

                        const renderGeoJson = () => {
                            clearTimeout(renderGeoJson.debounceId);
                            renderGeoJson.debounceId = setTimeout(() => {
                                clearDataLayer();
                                if (errorElement) {
                                    errorElement.classList.add('d-none');
                                }

                                const raw = textarea.value.trim();
                                if (!raw) {
                                    return;
                                }

                                try {
                                    const geoJson = JSON.parse(raw);
                                    const features = map.data.addGeoJson(geoJson);

                                    if (features.length > 0) {
                                        const bounds = new google.maps.LatLngBounds();
                                        map.data.forEach(feature => {
                                            processPoints(feature.getGeometry(), latLng => {
                                                bounds.extend(latLng);
                                            });
                                        });

                                        if (!bounds.isEmpty()) {
                                            map.fitBounds(bounds);
                                        }
                                    }
                                } catch (error) {
                                    if (errorElement) {
                                        errorElement.classList.remove('d-none');
                                    }
                                }
                            }, 250);
                        };

                        textarea.addEventListener('input', renderGeoJson);
                        renderGeoJson();
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
