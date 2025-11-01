<?php

namespace App\Support;

class KmlCenterCalculator
{
    /**
     * Calculate geographic center from a KML string.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function calculate(?string $kml): ?array
    {
        if (empty($kml) || !is_string($kml)) {
            return null;
        }

        $internalErrors = libxml_use_internal_errors(true);

        $document = new \DOMDocument();
        $loaded = $document->loadXML($kml);

        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($internalErrors);
            return null;
        }

        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('k', 'http://www.opengis.net/kml/2.2');

        $coordinateNodes = $xpath->query('//k:coordinates | //coordinates');
        $coordinates = [];

        if ($coordinateNodes !== false) {
            foreach ($coordinateNodes as $node) {
                $raw = trim($node->textContent ?? '');
                if ($raw === '') {
                    continue;
                }

                $pairs = preg_split('/\s+/', $raw) ?: [];
                foreach ($pairs as $pair) {
                    $pair = trim($pair);
                    if ($pair === '') {
                        continue;
                    }

                    $parts = explode(',', $pair);
                    if (count($parts) < 2) {
                        continue;
                    }

                    $lng = filter_var($parts[0], FILTER_VALIDATE_FLOAT);
                    $lat = filter_var($parts[1], FILTER_VALIDATE_FLOAT);

                    if ($lng === false || $lat === false) {
                        continue;
                    }

                    $coordinates[] = [$lng, $lat];
                }
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        if (empty($coordinates)) {
            return null;
        }

        $sumLat = 0.0;
        $sumLng = 0.0;

        foreach ($coordinates as [$lng, $lat]) {
            $sumLat += $lat;
            $sumLng += $lng;
        }

        $total = count($coordinates);

        return [
            'lat' => round($sumLat / $total, 7),
            'lng' => round($sumLng / $total, 7),
        ];
    }
}
