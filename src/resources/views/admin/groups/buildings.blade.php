@extends('admin.layouts.app')

@section('title', __('Manage Buildings for :group', ['group' => $group->name]))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>@lang('Manage Buildings for :group', ['group' => $group->name])</strong>
        <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary btn-sm">
            <i class="cil-arrow-left"></i> @lang('Back to Groups')
        </a>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.groups.buildings.update', $group) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $oldRows = old('buildings');
                $rows = [];

                if (is_array($oldRows)) {
                    foreach ($oldRows as $row) {
                        $id = $row['id'] ?? '';
                        $name = $row['name'] ?? '';
                        if ($id && !$name && isset($labelsById[$id])) {
                            $name = $labelsById[$id];
                        }
                        $rows[] = [
                            'id' => $id,
                            'name' => $name,
                        ];
                    }
                } else {
                    $rows = $assignedRows;
                }

                $minimumRows = 20;
                $rowCount = max($minimumRows, count($rows));
                for ($i = count($rows); $i < $rowCount; $i++) {
                    $rows[] = ['id' => '', 'name' => ''];
                }
            @endphp

            <datalist id="buildings-datalist">
                @foreach($buildingOptions as $option)
                    <option data-value="{{ $option['id'] }}" value="{{ $option['label'] }}"></option>
                @endforeach
            </datalist>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70%;">@lang('Building')</th>
                            <th class="text-end" style="width: 30%;">@lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody id="group-building-rows" data-next-index="{{ count($rows) }}">
                        @foreach($rows as $index => $row)
                            @php
                                $inputName = $row['name'] ?? '';
                                $inputId = $row['id'] ?? '';
                                if ($inputId && !$inputName && isset($labelsById[$inputId])) {
                                    $inputName = $labelsById[$inputId];
                                }
                            @endphp
                            <tr class="building-row" data-row-index="{{ $index }}">
                                <td>
                                    <input
                                        type="text"
                                        name="buildings[{{ $index }}][name]"
                                        value="{{ $inputName }}"
                                        class="form-control js-building-name {{ $errors->has('buildings.' . $index . '.name') ? 'is-invalid' : '' }}"
                                        data-row-index="{{ $index }}"
                                        list="buildings-datalist"
                                        autocomplete="off"
                                    >
                                    <input
                                        type="hidden"
                                        name="buildings[{{ $index }}][id]"
                                        value="{{ $inputId }}"
                                        class="js-building-id"
                                        data-row-index="{{ $index }}"
                                    >
                                    @error('buildings.' . $index . '.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('buildings.' . $index . '.id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td class="text-end">
                                    @if($googleMapsApiKey)
                                        <button type="button" class="btn btn-outline-secondary btn-sm js-open-map" data-row-index="{{ $index }}" data-coreui-toggle="modal" data-coreui-target="#groupBuildingMapModal">
                                            <i class="cil-map"></i> @lang('Select from map')
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary" id="add-building-row">
                    <i class="cil-plus"></i> @lang('Add Building Row')
                </button>
                <div class="d-flex gap-2">
                    @if($googleMapsApiKey)
                        <button type="button" class="btn btn-outline-info" data-coreui-toggle="modal" data-coreui-target="#groupBuildingPreviewModal">
                            <i class="cil-media-play"></i> @lang('Preview')
                        </button>
                    @endif
                    <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">@lang('Cancel')</a>
                    <button type="submit" class="btn btn-primary">@lang('Save')</button>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="building-row-template">
    <tr class="building-row">
        <td>
            <input
                type="text"
                class="form-control js-building-name"
                list="buildings-datalist"
                autocomplete="off"
            >
            <input type="hidden" class="js-building-id">
        </td>
        <td class="text-end">
            @if($googleMapsApiKey)
                <button type="button" class="btn btn-outline-secondary btn-sm js-open-map" data-coreui-toggle="modal" data-coreui-target="#groupBuildingMapModal">
                    <i class="cil-map"></i> @lang('Select from map')
                </button>
            @endif
        </td>
    </tr>
</template>

@if($googleMapsApiKey)
    @include('admin.partials.building-map-modal', ['googleMapsApiKey' => $googleMapsApiKey, 'buildingOptions' => $buildingOptions])
    @include('admin.groups.partials.preview-map-modal')
@endif
@endsection

@push('scripts')
<script>
    (function() {
        const buildingOptions = @json($buildingOptions);
        const labelsById = @json($labelsById);
        const rowsContainer = document.getElementById('group-building-rows');
        const addRowButton = document.getElementById('add-building-row');
        const rowTemplate = document.getElementById('building-row-template');
        const datalist = document.getElementById('buildings-datalist');
        const datalistOptions = datalist ? Array.from(datalist.options) : [];
        let nextIndex = Number(rowsContainer?.dataset.nextIndex || 0);
        let activeRowIndex = null;

        const optionByDisplay = new Map(datalistOptions.map(option => [option.value, option.dataset.value]));
        const optionById = new Map(buildingOptions.map(option => [String(option.id), option]));

        function updateHiddenFromDisplay(input) {
            const rowIndex = input.dataset.rowIndex;
            const hidden = rowsContainer.querySelector(`.js-building-id[name="buildings[${rowIndex}][id]"]`);
            if (!hidden) {
                return;
            }
            const matchedId = optionByDisplay.get(input.value);
            hidden.value = matchedId ? matchedId : '';
        }

        function handleBlur(event) {
            const input = event.target;
            if (!(input instanceof HTMLInputElement)) {
                return;
            }
            if (!input.classList.contains('js-building-name')) {
                return;
            }
            const matchedId = optionByDisplay.get(input.value);
            if (!matchedId) {
                input.value = '';
            }
            updateHiddenFromDisplay(input);
        }

        function attachRowEvents(row) {
            const nameInput = row.querySelector('.js-building-name');
            const hiddenInput = row.querySelector('.js-building-id');
            const mapButton = row.querySelector('.js-open-map');

            if (nameInput) {
                if (!nameInput.dataset.rowIndex) {
                    const index = row.dataset.rowIndex;
                    nameInput.dataset.rowIndex = index;
                }

                nameInput.addEventListener('input', () => updateHiddenFromDisplay(nameInput));
                nameInput.addEventListener('change', () => updateHiddenFromDisplay(nameInput));
                nameInput.addEventListener('blur', handleBlur);
            }

            if (hiddenInput && !hiddenInput.name) {
                const index = row.dataset.rowIndex;
                hiddenInput.name = `buildings[${index}][id]`;
            }

            if (nameInput && !nameInput.name) {
                const index = row.dataset.rowIndex;
                nameInput.name = `buildings[${index}][name]`;
            }

            if (mapButton) {
                mapButton.dataset.rowIndex = row.dataset.rowIndex;
                mapButton.addEventListener('click', () => {
                    activeRowIndex = mapButton.dataset.rowIndex;
                });
            }
        }

        function addRow(data = { name: '', id: '' }) {
            if (!rowsContainer || !rowTemplate) {
                return;
            }

            const index = nextIndex++;
            const clone = rowTemplate.content.firstElementChild.cloneNode(true);
            clone.dataset.rowIndex = index;

            const nameInput = clone.querySelector('.js-building-name');
            const hiddenInput = clone.querySelector('.js-building-id');
            const mapButton = clone.querySelector('.js-open-map');

            if (nameInput) {
                nameInput.name = `buildings[${index}][name]`;
                nameInput.dataset.rowIndex = index;
                nameInput.value = data.name || '';
            }

            if (hiddenInput) {
                hiddenInput.name = `buildings[${index}][id]`;
                hiddenInput.dataset.rowIndex = index;
                hiddenInput.value = data.id || '';
            }

            if (mapButton) {
                mapButton.dataset.rowIndex = index;
            }

            rowsContainer.appendChild(clone);
            attachRowEvents(clone);
        }

        if (rowsContainer) {
            rowsContainer.querySelectorAll('.building-row').forEach(row => {
                const index = row.dataset.rowIndex;
                const nameInput = row.querySelector('.js-building-name');
                if (nameInput && !nameInput.dataset.rowIndex) {
                    nameInput.dataset.rowIndex = index;
                }
                const hiddenInput = row.querySelector('.js-building-id');
                if (hiddenInput && !hiddenInput.name) {
                    hiddenInput.name = `buildings[${index}][id]`;
                }
                if (nameInput && !nameInput.name) {
                    nameInput.name = `buildings[${index}][name]`;
                }
                if (hiddenInput && hiddenInput.value && nameInput && !nameInput.value && labelsById[hiddenInput.value]) {
                    nameInput.value = labelsById[hiddenInput.value];
                }
                attachRowEvents(row);
            });
        }

        if (addRowButton) {
            addRowButton.addEventListener('click', () => addRow());
        }

        document.addEventListener('click', (event) => {
            const selectButton = event.target.closest('.js-map-select-building');
            if (!selectButton) {
                return;
            }

            event.preventDefault();
            const buildingId = selectButton.dataset.buildingId;
            const building = optionById.get(buildingId);
            if (!building) {
                return;
            }

            const rowIndex = activeRowIndex ?? selectButton.dataset.rowIndex;
            const nameInput = rowsContainer.querySelector(`.js-building-name[name="buildings[${rowIndex}][name]"]`);
            const hiddenInput = rowsContainer.querySelector(`.js-building-id[name="buildings[${rowIndex}][id]"]`);

            if (nameInput && hiddenInput) {
                nameInput.value = building.label;
                hiddenInput.value = building.id;
            }

            const modalElement = document.getElementById('groupBuildingMapModal');
            if (modalElement) {
                const instance = window.coreui ? window.coreui.Modal.getInstance(modalElement) : null;
                if (instance) {
                    instance.hide();
                } else {
                    const closeButton = modalElement.querySelector('[data-coreui-dismiss="modal"]');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            }
        });

        window.groupBuildingMapConfig = {
            getActiveRowIndex: () => activeRowIndex,
            setActiveRowIndex: (index) => {
                activeRowIndex = index;
            },
            optionById,
        };
    })();
</script>
@if($googleMapsApiKey)
    <script>
        (function() {
            const buildingOptions = @json($buildingOptions);
            const defaultPosition = @json(google_default_position());
            let mapInstance = null;
            let infoWindow = null;
            const markersById = new Map();
            const modalElement = document.getElementById('groupBuildingMapModal');
            const rowsContainer = document.getElementById('group-building-rows');
            const markerStyles = @json(google_marker_styles());
            const fallbackMarkerStyle = {
                background: (markerStyles.default && markerStyles.default.background) || '#607D8B',
                borderColor: (markerStyles.default && markerStyles.default.borderColor) || '#455A64',
                glyphColor: (markerStyles.default && markerStyles.default.glyphColor) || '#455A64',
            };
            let pendingCenterBuildingId = null;
            let markerLibElements = null;
            let markerLibPromise = null;

            const previewModalElement = document.getElementById('groupBuildingPreviewModal');
            let previewMapInstance = null;
            let previewInfoWindow = null;
            let previewMarkers = [];
            let pendingPreviewBuildings = [];
            let previewNeedsRender = false;

            function getMarkerStyle(type) {
                const style = markerStyles[type] || markerStyles.default || {};
                return {
                    background: style.background || fallbackMarkerStyle.background,
                    borderColor: style.borderColor || fallbackMarkerStyle.borderColor,
                    glyphColor: style.glyphColor || fallbackMarkerStyle.glyphColor,
                };
            }

            function ensureMarkerLibrary() {
                if (markerLibElements) {
                    return Promise.resolve(markerLibElements);
                }
                if (!markerLibPromise) {
                    markerLibPromise = google.maps.importLibrary('marker').then((lib) => {
                        markerLibElements = {
                            AdvancedMarkerElement: lib.AdvancedMarkerElement,
                            PinElement: lib.PinElement,
                        };
                        return markerLibElements;
                    });
                }
                return markerLibPromise;
            }

            window.initGroupBuildingMap = function initGroupBuildingMap() {
                const target = document.getElementById('group-building-map');
                if (!target || mapInstance) {
                    return;
                }

                const validBuildings = buildingOptions.filter(option => option.lat !== null && option.lat !== undefined && option.lng !== null && option.lng !== undefined);
                const initialCenter = {
                    lat: parseFloat(defaultPosition.lat),
                    lng: parseFloat(defaultPosition.lng),
                };

                const mapsLibPromise = google.maps.importLibrary('maps');

                Promise.all([ensureMarkerLibrary(), mapsLibPromise]).then(([markerLib]) => {
                    const { AdvancedMarkerElement, PinElement } = markerLib;

                    mapInstance = new google.maps.Map(target, {
                        center: initialCenter,
                        zoom: validBuildings.length ? 14 : 11,
                        mapTypeControl: false,
                        zoomControl: true,
                        mapId: 'group-building-map-id',
                    });

                    infoWindow = new google.maps.InfoWindow();

                    validBuildings.forEach(building => {
                        const position = { lat: parseFloat(building.lat), lng: parseFloat(building.lng) };
                        const style = getMarkerStyle(building.self_lock_type);
                        const marker = new AdvancedMarkerElement({
                            map: mapInstance,
                            position,
                            title: building.label,
                            content: new PinElement({
                                background: style.background,
                                borderColor: style.borderColor,
                                glyphColor: style.glyphColor,
                            }).element,
                        });

                        markersById.set(String(building.id), { marker, building });

                        marker.addListener('click', () => {
                            const content = createInfoWindowContent(building);
                            infoWindow.setContent(content);
                            infoWindow.open({ anchor: marker, map: mapInstance });
                        });
                    });
                    if (pendingCenterBuildingId) {
                        centerMapOnBuilding(pendingCenterBuildingId);
                    } else {
                        resetMapToDefault();
                    }

                    if (previewNeedsRender) {
                        renderPreviewMap(pendingPreviewBuildings);
                    }
                });
            };

            function centerMapOnBuilding(buildingId) {
                if (!mapInstance) {
                    return;
                }
                const optionById = window.groupBuildingMapConfig?.optionById;
                if (!optionById) {
                    return;
                }
                const building = optionById.get(String(buildingId));
                if (!building || building.lat === null || building.lng === null) {
                    return;
                }
                const position = { lat: parseFloat(building.lat), lng: parseFloat(building.lng) };
                mapInstance.setCenter(position);
                mapInstance.setZoom(16);

                const markerEntry = markersById.get(String(buildingId));
                if (markerEntry && infoWindow) {
                    infoWindow.setContent(createInfoWindowContent(markerEntry.building));
                    infoWindow.open({ anchor: markerEntry.marker, map: mapInstance });
                }

                pendingCenterBuildingId = null;
            }

            function createInfoWindowContent(building, withAddButton = true) {
                return `
                    <div class="p-2">
                        <div class="fw-bold mb-1">${building.label}</div>
                        ${building.url ? `<div class="mb-2"><a href="${building.url}" target="_blank" rel="noopener">@lang('See at external site')</a></div>` : ''}
                        ${withAddButton ? `<a href="#" class="btn btn-sm btn-primary js-map-select-building" data-building-id="${building.id}">@lang('Select this building')</a>` : ''}
                    </div>
                `;
            }

            function resetMapToDefault() {
                if (!mapInstance) {
                    return;
                }
                if (infoWindow) {
                    infoWindow.close();
                }
                const position = {
                    lat: parseFloat(defaultPosition.lat),
                    lng: parseFloat(defaultPosition.lng),
                };
                mapInstance.setCenter(position);
                mapInstance.setZoom(11);
                pendingCenterBuildingId = null;
            }

            function renderPreviewMap(buildings) {
                if (typeof google === 'undefined' || !google.maps) {
                    previewNeedsRender = true;
                    pendingPreviewBuildings = buildings;
                    return;
                }

                if (!markerLibElements) {
                    ensureMarkerLibrary().then(() => renderPreviewMap(buildings));
                    return;
                }

                const { AdvancedMarkerElement, PinElement } = markerLibElements;

                if (!previewMapInstance) {
                    const target = document.getElementById('group-preview-map');
                    if (!target) {
                        return;
                    }
                    previewMapInstance = new google.maps.Map(target, {
                        center: {
                            lat: parseFloat(defaultPosition.lat),
                            lng: parseFloat(defaultPosition.lng),
                        },
                        zoom: 11,
                        mapTypeControl: false,
                        zoomControl: true,
                        mapId: 'group-preview-map-id',
                    });
                    previewInfoWindow = new google.maps.InfoWindow();
                }

                previewMarkers.forEach(marker => {
                    marker.map = null;
                });
                previewMarkers = [];

                if (!buildings.length) {
                    if (previewInfoWindow) {
                        previewInfoWindow.close();
                    }
                    previewMapInstance.setCenter({
                        lat: parseFloat(defaultPosition.lat),
                        lng: parseFloat(defaultPosition.lng),
                    });
                    previewMapInstance.setZoom(11);
                    return;
                }

                const firstBuilding = buildings[0];
                const initialCenter = {
                    lat: parseFloat(firstBuilding.lat),
                    lng: parseFloat(firstBuilding.lng),
                };
                previewMapInstance.setCenter(initialCenter);
                previewMapInstance.setZoom(14);

                buildings.forEach(building => {
                    const position = { lat: parseFloat(building.lat), lng: parseFloat(building.lng) };
                    const style = getMarkerStyle(building.self_lock_type);
                    const marker = new AdvancedMarkerElement({
                        map: previewMapInstance,
                        position,
                        title: building.label,
                        content: new PinElement({
                            background: style.background,
                            borderColor: style.borderColor,
                            glyphColor: style.glyphColor,
                        }).element,
                    });

                    marker.addListener('click', () => {
                        if (!previewInfoWindow) {
                            previewInfoWindow = new google.maps.InfoWindow();
                        }
                        const content = createInfoWindowContent(building, false);
                        previewInfoWindow.setContent(content);
                        previewInfoWindow.open({ anchor: marker, map: previewMapInstance });
                    });

                    previewMarkers.push(marker);
                });
            }

            if (modalElement) {
                modalElement.addEventListener('show.coreui.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (trigger && trigger.dataset && trigger.dataset.rowIndex !== undefined) {
                        window.groupBuildingMapConfig.setActiveRowIndex(trigger.dataset.rowIndex);
                        
                        if (rowsContainer) {
                            const hidden = rowsContainer.querySelector(`.js-building-id[name="buildings[${trigger.dataset.rowIndex}][id]"]`);
                            if (hidden && hidden.value) {
                                pendingCenterBuildingId = hidden.value;
                            }
                        }
                        
                        if (mapInstance) {
                            if (pendingCenterBuildingId) {
                                centerMapOnBuilding(pendingCenterBuildingId);
                            } else {
                                resetMapToDefault();
                            }
                        }
                    }
                });

                modalElement.addEventListener('shown.coreui.modal', () => {
                    if (typeof google !== 'undefined' && google.maps) {
                        initGroupBuildingMap();
                        window.setTimeout(() => google.maps.event.trigger(mapInstance, 'resize'), 50);
                    }
                });
            }

            if (previewModalElement) {
                previewModalElement.addEventListener('show.coreui.modal', () => {
                    const selectedIds = Array.from(document.querySelectorAll('.js-building-id'))
                        .map(input => input.value)
                        .filter(Boolean);
                    const uniqueIds = Array.from(new Set(selectedIds));
                    pendingPreviewBuildings = uniqueIds
                        .map(id => buildingOptions.find(option => String(option.id) === String(id)))
                        .filter(building => building && building.lat !== null && building.lng !== null);
                    previewNeedsRender = true;
                });

                previewModalElement.addEventListener('shown.coreui.modal', () => {
                    if (previewNeedsRender) {
                        previewNeedsRender = false;
                        renderPreviewMap(pendingPreviewBuildings);
                    }

                    if (typeof google !== 'undefined' && google.maps && previewMapInstance) {
                        window.setTimeout(() => google.maps.event.trigger(previewMapInstance, 'resize'), 50);
                    }
                });

                previewModalElement.addEventListener('hidden.coreui.modal', () => {
                    if (previewInfoWindow) {
                        previewInfoWindow.close();
                    }
                });
            }
        })();
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initGroupBuildingMap" async defer></script>
@endif
@endpush
