const parseDocument = (kmlString) => {
  if (!kmlString || typeof kmlString !== 'string') {
    return null;
  }

  try {
    const parser = new DOMParser();
    return parser.parseFromString(kmlString, 'application/xml');
  } catch (error) {
    console.error('Failed to parse KML document', error);
    return null;
  }
};

const parseKmlColor = (value, defaultColor = '#ffffff', defaultOpacity = 1) => {
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
    .map((pair) => {
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
  if (!xml) {
    return styles;
  }

  const registerStyle = (key, data) => {
    if (!key || !data) {
      return;
    }

    const normalized = key.startsWith('#') ? key.slice(1) : key;
    const copy = { ...data };

    styles[`#${normalized}`] = copy;
    styles[normalized] = { ...copy };
  };

  Array.from(xml.getElementsByTagName('Style')).forEach((styleNode) => {
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

  Array.from(xml.getElementsByTagName('StyleMap')).forEach((styleMapNode) => {
    const id = styleMapNode.getAttribute('id');
    if (!id) {
      return;
    }

    const normalPair = Array.from(styleMapNode.getElementsByTagName('Pair')).find((pair) => {
      return pair.querySelector('key')?.textContent?.trim() === 'normal';
    });

    const styleUrl = normalPair?.querySelector('styleUrl')?.textContent?.trim();
    if (styleUrl) {
      registerStyle(id, styles[styleUrl] || styles[styleUrl.replace('#', '')] || {});
    }
  });

  return styles;
};

const parsePolygons = (xml) => {
  if (!xml) {
    return [];
  }

  const placemarks = Array.from(xml.getElementsByTagName('Placemark'));
  const polygons = [];

  placemarks.forEach((placemark) => {
    const polygonNode = placemark.getElementsByTagName('Polygon')[0];
    if (!polygonNode) {
      return;
    }

    const coordinatesNode = polygonNode.querySelector('outerBoundaryIs LinearRing coordinates');
    if (!coordinatesNode || !coordinatesNode.textContent) {
      return;
    }

    const coordinates = parseCoordinates(coordinatesNode.textContent);
    if (!coordinates.length) {
      return;
    }

    polygons.push({
      coordinates,
      name: placemark.getElementsByTagName('name')[0]?.textContent?.trim() || '',
      description: placemark.getElementsByTagName('description')[0]?.textContent?.trim() || '',
      styleUrl: placemark.getElementsByTagName('styleUrl')[0]?.textContent?.trim() || null,
    });
  });

  return polygons;
};

const parsePoints = (xml) => {
  if (!xml) {
    return [];
  }

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

    points.push({
      lat: latRaw,
      lng: lngRaw,
      name: placemark.getElementsByTagName('name')[0]?.textContent?.trim() || '',
      description: placemark.getElementsByTagName('description')[0]?.textContent?.trim() || '',
      styleUrl: placemark.getElementsByTagName('styleUrl')[0]?.textContent?.trim() || null,
    });
  });

  return points;
};

export const kmlParser = {
  parseDocument,
  parseStyles,
  parsePolygons,
  parsePoints,
  parseKmlColor,
};
