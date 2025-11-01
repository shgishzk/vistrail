<?php

namespace App\Support;

class GeoJsonCenterCalculator
{
    /**
     * Calculate the geographic center from a GeoJSON string.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function calculate(?string $geoJson): ?array
    {
        if (empty($geoJson) || !is_string($geoJson)) {
            return null;
        }

        $decoded = json_decode($geoJson, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $coordinates = [];

        $collectPairs = function ($node) use (&$collectPairs, &$coordinates): void {
            if (!is_array($node)) {
                return;
            }

            $isCoordinatePair = count($node) >= 2
                && isset($node[0], $node[1])
                && is_numeric($node[0])
                && is_numeric($node[1])
                && !is_array($node[0])
                && !is_array($node[1]);

            if ($isCoordinatePair) {
                $coordinates[] = [(float) $node[0], (float) $node[1]];
                return;
            }

            foreach ($node as $child) {
                $collectPairs($child);
            }
        };

        $processGeometry = function ($geometry) use (&$collectPairs, &$processGeometry): void {
            if (!is_array($geometry)) {
                return;
            }

            if (isset($geometry['coordinates'])) {
                $collectPairs($geometry['coordinates']);
                return;
            }

            foreach ($geometry as $child) {
                $processGeometry($child);
            }
        };

        if (isset($decoded['type'])) {
            switch ($decoded['type']) {
                case 'FeatureCollection':
                    if (isset($decoded['features']) && is_array($decoded['features'])) {
                        foreach ($decoded['features'] as $feature) {
                            if (isset($feature['geometry'])) {
                                $processGeometry($feature['geometry']);
                            }
                        }
                    }
                    break;
                case 'Feature':
                    if (isset($decoded['geometry'])) {
                        $processGeometry($decoded['geometry']);
                    }
                    break;
                default:
                    $processGeometry($decoded);
                    break;
            }
        } elseif (isset($decoded['features'])) {
            foreach ($decoded['features'] as $feature) {
                if (isset($feature['geometry'])) {
                    $processGeometry($feature['geometry']);
                }
            }
        } else {
            $collectPairs($decoded);
        }

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
