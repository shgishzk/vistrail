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

    <script>
        const areaKml = @json($area->boundary_kml);
        const defaultPosition = @json($defaultPosition);

        function parseKmlCoordinates(kmlString) {
            if (!kmlString) {
                return [];
            }
            try {
                const parser = new DOMParser();
                const xml = parser.parseFromString(kmlString, 'application/xml');
                const coordinateNodes = xml.getElementsByTagName('coordinates');
                const paths = [];

                Array.from(coordinateNodes).forEach(node => {
                    const text = (node.textContent || '').trim();
                    if (!text) {
                        return;
                    }
                    const coords = text.split(/\s+/).map(pair => {
                        const [lng, lat] = pair.split(',').map(num => parseFloat(num));
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            return { lat, lng };
                        }
                        return null;
                    }).filter(Boolean);
                    if (coords.length) {
                        paths.push(coords);
                    }
                });

                return paths;
            } catch (error) {
                console.error('Failed to parse KML', error);
                return [];
            }
        }

        function parseKmlPoints(kmlString) {
            if (!kmlString) {
                return [];
            }

            try {
                const parser = new DOMParser();
                const xml = parser.parseFromString(kmlString, 'application/xml');
                const placemarks = Array.from(xml.getElementsByTagName('Placemark'));
                const points = [];

                placemarks.forEach((placemark) => {
                    const pointNode = placemark.getElementsByTagName('Point')[0];
                    if (!pointNode) {
                        return;
                    }

                    const coordinateNode = pointNode.getElementsByTagName('coordinates')[0];
                    if (!coordinateNode || !coordinateNode.textContent) {
                        return;
                    }

                    const [lngRaw, latRaw] = coordinateNode.textContent.trim().split(',').map(Number);
                    if (!Number.isFinite(latRaw) || !Number.isFinite(lngRaw)) {
                        return;
                    }

                    const name = placemark.getElementsByTagName('name')[0]?.textContent?.trim() || '';
                    const description = placemark.getElementsByTagName('description')[0]?.textContent?.trim() || '';

                    points.push({
                        lat: latRaw,
                        lng: lngRaw,
                        name,
                        description,
                    });
                });

                return points;
            } catch (error) {
                console.error('Failed to parse KML points', error);
                return [];
            }
        }

        async function initMap() {
            const initialCenter = {
                lat: Number(defaultPosition?.lat) || 35.0238868,
                lng: Number(defaultPosition?.lng) || 135.760201,
            };

            const map = new google.maps.Map(document.getElementById('map'), {
                center: initialCenter,
                zoom: 12,
                mapTypeId: 'roadmap',
                mapId: 'area-print-map'
            });

            const paths = parseKmlCoordinates(areaKml);
            const points = parseKmlPoints(areaKml);
            const bounds = new google.maps.LatLngBounds();

            if (paths.length) {
                const polygon = new google.maps.Polygon({
                    paths: paths[0],
                    strokeColor: '#8e24aa',
                    strokeOpacity: 1,
                    strokeWeight: 2,
                    fillColor: '#ba68c8',
                    fillOpacity: 0.25,
                });

                polygon.setMap(map);

                paths[0].forEach(point => bounds.extend(point));
            }

            if (points.length) {
                const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');

                points.forEach(point => {
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
