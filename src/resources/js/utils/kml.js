export const parseKmlPolygonCoordinates = (kmlString = '') => {
  if (typeof kmlString !== 'string' || !kmlString.trim()) {
    return [];
  }

  if (typeof DOMParser === 'undefined') {
    return [];
  }

  try {
    const parser = new DOMParser();
    const doc = parser.parseFromString(kmlString, 'application/xml');
    const coordinatesElement = doc.getElementsByTagName('coordinates')[0];

    if (!coordinatesElement || !coordinatesElement.textContent) {
      return [];
    }

    const rawCoordinates = coordinatesElement.textContent.trim();
    const tokens = rawCoordinates.split(/\s+/);

    const points = tokens.reduce((acc, token) => {
      const [lng, lat] = token.split(',').map((value) => Number.parseFloat(value));

      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        acc.push({ lat, lng });
      }

      return acc;
    }, []);

    if (points.length > 1) {
      const first = points[0];
      const last = points[points.length - 1];

      if (first.lat === last.lat && first.lng === last.lng) {
        points.pop();
      }
    }

    return points;
  } catch (error) {
    console.error('Failed to parse KML polygon coordinates:', error);
    return [];
  }
};
