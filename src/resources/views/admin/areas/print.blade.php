<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>{{ __('Area') }} {{ $area->number }} {{ $area->name }}</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Helvetica Neue", Arial, sans-serif;
        }
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        .wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        header {
            padding-bottom: 12px;
            border-bottom: 2px solid #333;
            margin-bottom: 12px;
        }
        header h1 {
            font-size: 24px;
            margin: 0;
        }
        #map {
            flex: 1;
            width: 100%;
            min-height: 100px;
        }
        .marker-label {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            background: rgba(33, 33, 33, 0.7);
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
            font-size: 13px;
            line-height: 1.35;
            transform: translateY(24px);
            white-space: nowrap;
            max-width: 260px;
            opacity: 0.75;
        }
        .marker-label__title {
            font-weight: 600;
            font-size: 11px;
        }
        .marker-label__desc {
            margin-top: 4px;
            font-size: 10px;
            opacity: 0.9;
        }
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <header>
            <h1>
                {{ __('Area') }} {{ $area->number }}
                @if($area->name)
                    - {{ $area->name }}
                @endif
            </h1>
        </header>
        <div id="map"></div>
    </div>

    <script src="{{ asset('js/kml-parser.js') }}"></script>
    <script>
        const areaKml = @json($area->boundary_kml);
        const defaultPosition = @json($defaultPosition);

        async function initMap() {
            const initialCenter = {
                lat: Number(defaultPosition?.lat) || 35.0238868,
                lng: Number(defaultPosition?.lng) || 135.760201,
            };

            const kmlUtils = window.kmlParser;
            if (!kmlUtils) {
                console.error('kmlParser utilities are not loaded.');
                return;
            }

            const {
                parseDocument,
                parsePolygons,
                parsePoints,
                parseStyles,
                parseKmlColor,
            } = kmlUtils;

            const map = new google.maps.Map(document.getElementById('map'), {
                center: initialCenter,
                zoom: 12,
                mapTypeId: 'roadmap',
                mapId: 'area-print-map',
                fullscreenControl: false,
            });

            const bounds = new google.maps.LatLngBounds();
            let polygons = [];
            let points = [];
            let styles = {};

            if (areaKml && areaKml.trim()) {
                const xml = parseDocument(areaKml);
                if (xml && !xml.querySelector('parsererror')) {
                    styles = parseStyles(xml);
                    polygons = parsePolygons(xml);
                    points = parsePoints(xml);
                } else {
                    console.error('Failed to parse KML for print view.');
                }
            }

            const resolveStyle = (styleUrl) => {
                if (!styleUrl) {
                    return {};
                }
                return styles[styleUrl] || styles[styleUrl.replace(/^#/, '')] || {};
            };

            polygons.forEach((polygonDatum) => {
                if (!Array.isArray(polygonDatum.coordinates) || polygonDatum.coordinates.length < 3) {
                    return;
                }

                polygonDatum.coordinates.forEach((coordinate) => bounds.extend(coordinate));

                const style = resolveStyle(polygonDatum.styleUrl);
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
            });

            if (!bounds.isEmpty() && bounds.getNorthEast().equals(bounds.getSouthWest())) {
                map.setCenter(bounds.getCenter());
            }

            if (points.length) {
                const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');

                points.forEach((point) => {
                    if (!Number.isFinite(point.lat) || !Number.isFinite(point.lng)) {
                        return;
                    }

                    const container = document.createElement('div');
                    container.className = 'marker-label';

                    const titleEl = document.createElement('div');
                    titleEl.className = 'marker-label__title';
                    titleEl.textContent = point.name || '{{ __("Point") }}';
                    container.appendChild(titleEl);

                    if (point.description) {
                        const descEl = document.createElement('div');
                        descEl.className = 'marker-label__desc';
                        descEl.textContent = point.description;
                        container.appendChild(descEl);
                    }

                    const marker = new AdvancedMarkerElement({
                        map,
                        position: { lat: point.lat, lng: point.lng },
                        content: container,
                        title: point.name || '',
                        collisionBehavior: google.maps.CollisionBehavior.OPTIONAL_AND_HIDES_LOWER_PRIORITY
                    });

                    bounds.extend({ lat: point.lat, lng: point.lng });
                });
            }

            if (!bounds.isEmpty()) {
                map.fitBounds(bounds, { top: 100, right: 100, bottom: 100, left: 100 });
            } else {
                map.setCenter(initialCenter);
            }

            setTimeout(() => {
                window.print();
            }, 800);
        }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initMap" async defer></script>
</body>
</html>
