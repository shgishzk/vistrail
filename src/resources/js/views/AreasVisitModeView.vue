<template>
  <div class="space-y-6 lg:space-y-10">
    <header class="rounded-3xl border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-10 sm:py-8">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.45em] text-slate-400">Visit Mode</p>
          <h1 class="text-3xl font-semibold text-slate-900 sm:text-4xl">
            {{ activeVisit?.area?.number ?? '区域' }}
            <span v-if="activeVisit?.area?.name" class="text-lg font-medium text-slate-500">/ {{ activeVisit.area.name }}</span>
          </h1>
          <p class="mt-2 text-sm text-slate-500">
            Google マップ上をタップして、訪問記録を追加・編集することができます。
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <RouterLink
            to="/areas/my"
            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-600"
          >
            <span class="text-lg">←</span>
            一覧に戻る
          </RouterLink>
          <span class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700">
            訪問中
          </span>
        </div>
      </div>
    </header>

    <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
      <div v-if="state.isLoading" class="space-y-4">
        <div class="h-6 w-1/3 animate-pulse rounded bg-slate-200"></div>
        <div class="h-[60vh] animate-pulse rounded-2xl bg-slate-100"></div>
      </div>
      <div v-else-if="state.error" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">
        {{ state.error }}
      </div>
      <div v-else-if="!activeVisit" class="rounded-2xl border border-dashed border-slate-200 px-5 py-6 text-center text-sm text-slate-500">
        表示できる区域が見つかりませんでした。<br />
        <RouterLink to="/areas/my" class="text-sky-600 underline">区域一覧に戻る</RouterLink>
      </div>
      <div v-else class="space-y-5">
        <dl class="grid gap-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-2">
          <div>
            <dt class="text-xs uppercase tracking-wide text-slate-400">訪問開始日</dt>
            <dd class="mt-1 text-base font-semibold text-slate-900">{{ activeVisit.start_date ?? '未設定' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs uppercase tracking-wide text-slate-400">メモ</dt>
            <dd class="mt-1 text-slate-700">{{ activeVisit.memo ?? '—' }}</dd>
          </div>
        </dl>
        <div class="areas-visit-map relative min-h-[60vh] rounded-3xl border border-slate-200">
          <div
            v-if="mapError"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 px-6 text-center text-sm text-red-600"
          >
            {{ mapError }}
          </div>
          <div ref="mapContainer" class="h-[70vh] w-full rounded-3xl" :class="{ 'opacity-30': mapError }"></div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { fetchMyAreas } from '../services/areasService';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { kmlParser } from '../utils/kmlParser';

const route = useRoute();
const router = useRouter();

const state = reactive({
  isLoading: true,
  error: '',
});

const visits = ref([]);
const mapContainer = ref(null);
const mapError = ref('');
const mapConfig = ref(null);
const activeVisit = ref(null);
const createdPins = reactive({});
const loadedPinVisitIds = new Set();

const pinForm = reactive({
  mode: 'create',
  pinId: null,
  lat: null,
  lng: null,
  memo: '',
  isSaving: false,
  isDeleting: false,
});

let mapInstance = null;
let mapInitPromise = null;
let markerModule = null;
let mapClickListener = null;
let pinInfoWindow = null;
const polygonOverlays = [];
const pointMarkers = [];
const userPinMarkers = [];
const buildingMarkers = [];
let buildingInfoWindow = null;
let buildingFetchAbortController = null;
let buildingFetchTimeout = null;
let buildingIdleListener = null;
let lastBuildingFetchCenter = null;

const markerStyles = {
  default: {
    background: '#607D8B',
    borderColor: '#455A64',
    glyphColor: '#455A64',
  },
  has_lock: {
    background: '#EA766C',
    borderColor: '#C81714',
    glyphColor: '#C81714',
  },
  no_lock: {
    background: '#6AAF6C',
    borderColor: '#2D8F32',
    glyphColor: '#2D8F32',
  },
};

const applyMarkerStyles = (styles = {}) => {
  Object.keys(markerStyles).forEach((key) => {
    if (styles[key]) {
      markerStyles[key] = {
        background: styles[key].background || markerStyles[key].background,
        borderColor: styles[key].borderColor || markerStyles[key].borderColor,
        glyphColor: styles[key].glyphColor || markerStyles[key].glyphColor,
      };
    }
  });
};

const getMarkerStyle = (type) => {
  if (!type) {
    return markerStyles.default;
  }
  return markerStyles[type] || markerStyles.default;
};

const escapeHtml = (value = '') => {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
};

const formatVisitRate = (value) => {
  if (typeof value !== 'number' || Number.isNaN(value)) {
    return '―';
  }
  return `${value.toFixed(1)}%`;
};

const formatMemo = (value) => {
  if (!value) {
    return '―';
  }
  return escapeHtml(value).replace(/\n/g, '<br>');
};

const appendQueryParams = (url, params = {}) => {
  const query = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.append(key, value);
    }
  });
  const queryString = query.toString();
  if (!queryString) {
    return url;
  }
  return url.includes('?') ? `${url}&${queryString}` : `${url}?${queryString}`;
};

const distanceInMeters = (lat1, lng1, lat2, lng2) => {
  const toRad = (value) => (value * Math.PI) / 180;
  const R = 6371000;
  const dLat = toRad(lat2 - lat1);
  const dLng = toRad(lng2 - lng1);
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) * Math.sin(dLng / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
};

const BUILDING_RADIUS_METERS = 500;
const BUILDING_FETCH_DEBOUNCE_MS = 300;
const BUILDING_MIN_CENTER_CHANGE_METERS = 50;

const createBuildingInfoWindowContent = (building) => {
  const visitId = activeVisit.value?.id;
  const detailUrl = appendQueryParams(building.detail_url || `/buildings/${building.id}`, {
    from: 'areasVisit',
    visitId: visitId ?? undefined,
  });
  const safeName = escapeHtml(building.name || 'マンション');
  const lastVisit = building.last_visit_date ? escapeHtml(building.last_visit_date) : '―';
  const visitRate = formatVisitRate(building.visit_rate);
  const memo = formatMemo(building.memo);

  return `
    <div class="min-w-[220px] max-w-xs space-y-2">
      <a href="${detailUrl}" class="block text-base font-semibold text-indigo-600 hover:text-indigo-500 hover:underline" target="_self">
        ${safeName}
      </a>
      <div class="space-y-1 text-sm text-slate-600">
        <div><span class="font-medium text-slate-700">最終訪問日:</span> ${lastVisit}</div>
        <div><span class="font-medium text-slate-700">訪問率:</span> ${visitRate}</div>
        <div><span class="font-medium text-slate-700">メモ:</span> ${memo}</div>
      </div>
    </div>
  `;
};

const syncActiveVisit = () => {
  const targetId = Number(route.params.visitId);
  const selected = visits.value.find((visit) => visit.id === targetId);
  activeVisit.value = selected || visits.value[0] || null;

  if (!selected && visits.value.length && !Number.isNaN(targetId)) {
    router.replace({
      name: 'areasMyVisit',
      params: { visitId: visits.value[0].id },
    });
  }
};

const loadVisits = async () => {
  state.isLoading = true;
  try {
    const { visits: payload = [] } = await fetchMyAreas();
    visits.value = payload;
    state.error = '';
    syncActiveVisit();
  } catch (error) {
    console.error('Failed to load my areas:', error);
    state.error = '担当区域の取得に失敗しました。時間をおいて再度お試しください。';
    visits.value = [];
    activeVisit.value = null;
  } finally {
    state.isLoading = false;
  }
};

const loadPinsForVisit = async (visit) => {
  if (!visit) {
    return;
  }

  try {
    const { data } = await axios.get('/api/pins', {
      params: {
        area_id: visit.area.id,
        visit_id: visit.id,
      },
    });
    createdPins[visit.id] = Array.isArray(data?.pins) ? data.pins : [];
    loadedPinVisitIds.add(visit.id);
  } catch (error) {
    console.error('Failed to load visit pins:', error);
  }
};

const ensurePinsLoaded = async () => {
  if (!activeVisit.value) {
    return;
  }

  if (loadedPinVisitIds.has(activeVisit.value.id)) {
    return;
  }

  await loadPinsForVisit(activeVisit.value);
};

const initializeMap = async () => {
  if (mapInitPromise) {
    return mapInitPromise;
  }

  mapInitPromise = (async () => {
    if (typeof window === 'undefined' || !mapContainer.value) {
      return;
    }

    if (!mapConfig.value) {
      const { data } = await axios.get('/api/map/config');
      mapConfig.value = data;
    }

    if (!mapConfig.value?.maps_api_key) {
      throw new Error('Google Maps APIキーが設定されていません。');
    }

    const defaultPosition = {
      lat: Number(mapConfig.value?.default_position?.lat) || 35.0238868,
      lng: Number(mapConfig.value?.default_position?.lng) || 135.760201,
    };

    applyMarkerStyles(mapConfig.value?.marker_styles || {});

    await loadGoogleMaps(mapConfig.value.maps_api_key);
    if (google.maps?.importLibrary) {
      await google.maps.importLibrary('maps');
      if (!markerModule) {
        markerModule = await google.maps.importLibrary('marker');
      }
    }

    mapInstance = new google.maps.Map(mapContainer.value, {
      center: defaultPosition,
      zoom: 13,
      mapId: 'areas-my-visit-map',
      zoomControl: true,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
      clickableIcons: false,
    });

    if (mapClickListener) {
      mapClickListener.remove();
    }
    mapClickListener = mapInstance.addListener('click', handleMapClick);

    if (buildingIdleListener) {
      buildingIdleListener.remove();
    }
    buildingIdleListener = mapInstance.addListener('idle', scheduleBuildingFetch);

    fetchNearbyBuildings(defaultPosition.lat, defaultPosition.lng);
  })();

  return mapInitPromise;
};

const clearOverlays = () => {
  while (polygonOverlays.length) {
    polygonOverlays.pop()?.setMap(null);
  }

  while (pointMarkers.length) {
    const marker = pointMarkers.pop();
    if (marker?.map) {
      marker.map = null;
    } else if (marker?.setMap) {
      marker.setMap(null);
    }
  }

  clearUserPinMarkers();
  clearBuildingMarkers();
};

const clearUserPinMarkers = () => {
  while (userPinMarkers.length) {
    const marker = userPinMarkers.pop();
    if (marker?.map) {
      marker.map = null;
    } else if (marker?.setMap) {
      marker.setMap(null);
    }
  }
};

const clearBuildingMarkers = () => {
  while (buildingMarkers.length) {
    const marker = buildingMarkers.pop();
    if (marker?.map) {
      marker.map = null;
    } else if (marker?.setMap) {
      marker.setMap(null);
    }
  }
};

const renderSelectedArea = async () => {
  if (!activeVisit.value || !mapContainer.value) {
    clearOverlays();
    return;
  }

  try {
    await initializeMap();
  } catch (error) {
    console.error('Failed to initialize Google Maps:', error);
    mapError.value = 'マップの初期化に失敗しました。';
    return;
  }

  mapError.value = '';
  clearOverlays();

  if (!mapInstance) {
    mapError.value = 'マップを表示できませんでした。';
    return;
  }

  await ensurePinsLoaded();

  const boundary = activeVisit.value.area?.boundary_kml;

  if (!boundary) {
    mapError.value = 'この区域には境界データが登録されていません。';
    return;
  }

  try {
    const xml = kmlParser.parseDocument(boundary);
    if (!xml || xml.querySelector('parsererror')) {
      throw new Error('invalid_kml');
    }

    const bounds = new google.maps.LatLngBounds();
    const styles = kmlParser.parseStyles(xml);
    const polygons = kmlParser.parsePolygons(xml);
    const points = kmlParser.parsePoints(xml);

    const resolveStyle = (styleUrl) => {
      if (!styleUrl) {
        return {};
      }
      return styles[styleUrl] || styles[styleUrl.replace(/^#/, '')] || {};
    };

    let geometryCount = 0;

    polygons.forEach((polygonDatum) => {
      if (!Array.isArray(polygonDatum.coordinates) || polygonDatum.coordinates.length < 3) {
        return;
      }

      polygonDatum.coordinates.forEach((point) => bounds.extend(point));

      const style = resolveStyle(polygonDatum.styleUrl);
      const strokeEnabled = style.polyOutline !== '0';
      const fillEnabled = style.polyFill !== '0';
      const stroke = kmlParser.parseKmlColor(style.lineColor, '#8e24aa', strokeEnabled ? 1 : 0);
      const fill = kmlParser.parseKmlColor(style.polyColor, '#ba68c8', fillEnabled ? 0.3 : 0);
      const strokeWeight = Number.parseFloat(style.lineWidth);

      const polygon = new google.maps.Polygon({
        paths: polygonDatum.coordinates,
        strokeColor: stroke.color,
        strokeOpacity: stroke.opacity,
        strokeWeight: Number.isFinite(strokeWeight) ? strokeWeight : 2,
        fillColor: fill.color,
        fillOpacity: fill.opacity,
      });

      polygon.setMap(mapInstance);
      polygon.addListener('click', (event) => {
        if (event?.latLng) {
          handleMapClick(event);
        }
      });
      polygonOverlays.push(polygon);
      geometryCount++;
    });

    const AdvancedMarkerElement =
      markerModule?.AdvancedMarkerElement || google.maps.marker?.AdvancedMarkerElement || null;

    const createMarkerLabel = (point) => {
      const container = document.createElement('div');
      container.className = 'marker-label';

      const titleEl = document.createElement('div');
      titleEl.className = 'marker-label__title';
      titleEl.textContent = point.name?.trim() || 'Point';
      container.appendChild(titleEl);

      if (point.description?.trim()) {
        const descEl = document.createElement('div');
        descEl.className = 'marker-label__desc';
        descEl.textContent = point.description.trim();
        container.appendChild(descEl);
      }

      return container;
    };

    points.forEach((point) => {
      if (!Number.isFinite(point.lat) || !Number.isFinite(point.lng)) {
        return;
      }

      const position = { lat: point.lat, lng: point.lng };
      bounds.extend(position);

      if (AdvancedMarkerElement) {
        const marker = new AdvancedMarkerElement({
          map: mapInstance,
          position,
          content: createMarkerLabel(point),
          title: point.name || '',
          collisionBehavior: google.maps.CollisionBehavior.OPTIONAL_AND_HIDES_LOWER_PRIORITY,
        });
        pointMarkers.push(marker);
      } else {
        const marker = new google.maps.Marker({
          map: mapInstance,
          position,
          label: point.name?.slice(0, 2) || 'Pt',
        });
        pointMarkers.push(marker);
      }

      geometryCount++;
    });

    if (!geometryCount) {
      throw new Error('no_geometry');
    }

    if (!bounds.isEmpty()) {
      mapInstance.fitBounds(bounds, 60);
    }

    renderLocalPins();
    fetchNearbyBuildings();

    mapError.value = '';
  } catch (error) {
    console.error('Failed to render KML boundary:', error);
    mapError.value = '境界データの解析に失敗しました。';
    clearOverlays();
  }
};

const renderLocalPins = () => {
  if (!activeVisit.value) {
    return;
  }

  clearUserPinMarkers();

  const visitPins = createdPins[activeVisit.value.id] || [];
  visitPins.forEach((pin) => {
    addCustomPinMarker(pin);
  });
};

const renderBuildingMarkers = (buildings, centerLat, centerLng) => {
  if (!mapInstance) {
    return;
  }

  const module = markerModule || google.maps.marker;
  const PinElement = module?.PinElement;
  const AdvancedMarkerElement = module?.AdvancedMarkerElement;

  if (!PinElement || !AdvancedMarkerElement) {
    return;
  }

  clearBuildingMarkers();

  buildings.forEach((building) => {
    if (typeof building.lat !== 'number' || typeof building.lng !== 'number') {
      return;
    }

    const distance = distanceInMeters(centerLat, centerLng, building.lat, building.lng);
    if (distance > BUILDING_RADIUS_METERS) {
      return;
    }

    const style = getMarkerStyle(building.self_lock_type);
    const pin = new PinElement({
      background: style.background,
      borderColor: style.borderColor,
      glyphColor: style.glyphColor,
    });

    const marker = new AdvancedMarkerElement({
      map: mapInstance,
      position: { lat: building.lat, lng: building.lng },
      title: building.name || 'Building',
      content: pin.element,
    });

    marker.addListener('click', () => {
      if (!buildingInfoWindow) {
        buildingInfoWindow = new google.maps.InfoWindow({
          disableAutoPan: true,
          shouldFocus: false,
        });
      }
      buildingInfoWindow.setContent(createBuildingInfoWindowContent(building));
      buildingInfoWindow.open({ anchor: marker, map: mapInstance });
    });

    buildingMarkers.push(marker);
  });
};

const fetchNearbyBuildings = async (lat, lng) => {
  const center = mapInstance?.getCenter();
  const targetLat = typeof lat === 'number' ? lat : center?.lat();
  const targetLng = typeof lng === 'number' ? lng : center?.lng();

  if (!Number.isFinite(targetLat) || !Number.isFinite(targetLng)) {
    return;
  }

  try {
    if (buildingFetchAbortController) {
      buildingFetchAbortController.abort();
    }
    buildingFetchAbortController = new AbortController();

    const { data } = await axios.get('/api/buildings', {
      params: { lat: targetLat, lng: targetLng },
      signal: buildingFetchAbortController.signal,
    });

    const buildings = Array.isArray(data?.buildings) ? data.buildings : [];
    renderBuildingMarkers(buildings, targetLat, targetLng);
  } catch (error) {
    const isCanceled =
      error?.name === 'CanceledError' ||
      error?.message === 'canceled' ||
      error?.message === 'cancelled' ||
      error?.code === 'ERR_CANCELED';
    if (!isCanceled) {
      console.error('Failed to load nearby buildings:', error);
    }
  } finally {
    buildingFetchAbortController = null;
  }
};

const scheduleBuildingFetch = () => {
  if (!mapInstance) {
    return;
  }

  const center = mapInstance.getCenter();
  if (!center) {
    return;
  }

  const lat = center.lat();
  const lng = center.lng();

  if (lastBuildingFetchCenter) {
    const moved = distanceInMeters(
      lastBuildingFetchCenter.lat,
      lastBuildingFetchCenter.lng,
      lat,
      lng
    );
    if (moved < BUILDING_MIN_CENTER_CHANGE_METERS) {
      return;
    }
  }

  lastBuildingFetchCenter = { lat, lng };

  if (buildingFetchTimeout) {
    clearTimeout(buildingFetchTimeout);
    buildingFetchTimeout = null;
  }

  buildingFetchTimeout = setTimeout(() => {
    buildingFetchTimeout = null;
    fetchNearbyBuildings(lat, lng);
  }, BUILDING_FETCH_DEBOUNCE_MS);
};

const addCustomPinMarker = (pin) => {
  if (!mapInstance || !google?.maps) {
    return;
  }

  const position = { lat: Number(pin.lat), lng: Number(pin.lng) };
  const AdvancedMarkerElement =
    markerModule?.AdvancedMarkerElement || google.maps.marker?.AdvancedMarkerElement || null;

  if (AdvancedMarkerElement) {
    const container = document.createElement('div');
    container.className = 'visit-pin-marker';
    container.title = pin.memo || '訪問メモ';

    const ripple = document.createElement('div');
    ripple.className = 'visit-pin-marker__ripple';
    container.appendChild(ripple);

    const dot = document.createElement('div');
    dot.className = 'visit-pin-marker__dot';
    container.appendChild(dot);

    const marker = new AdvancedMarkerElement({
      map: mapInstance,
      position,
      content: container,
    });
    container.addEventListener('click', (event) => {
      event.stopPropagation();
      openExistingPinForm(pin, position);
    });
    userPinMarkers.push(marker);
    return;
  }

  const marker = new google.maps.Marker({
    map: mapInstance,
    position,
    icon: {
      path: google.maps.SymbolPath.CIRCLE,
      scale: 8,
      fillColor: '#38bdf8',
      fillOpacity: 1,
      strokeColor: '#0ea5e9',
      strokeWeight: 2,
    },
    title: pin.memo || '訪問メモ',
  });

  marker.addListener('click', (event) => {
    if (event?.domEvent) {
      event.domEvent.stopPropagation();
    }
    openExistingPinForm(pin, position);
  });
  userPinMarkers.push(marker);
};

const openExistingPinForm = (pin, position) => {
  const latLng = new google.maps.LatLng(position.lat, position.lng);
  openPinInfoWindow(latLng, 'edit', pin);
};

const handleMapClick = (event) => {
  if (!activeVisit.value || !event?.latLng) {
    return;
  }

  openPinInfoWindow(event.latLng, 'create');
};

const openPinInfoWindow = (latLng, mode = 'create', pin = null) => {
  if (!google?.maps) {
    return;
  }

  pinForm.mode = mode;
  pinForm.pinId = pin?.id ?? null;
  pinForm.lat = Number(latLng.lat().toFixed(7));
  pinForm.lng = Number(latLng.lng().toFixed(7));
  pinForm.memo = pin?.memo ?? '';
  pinForm.isSaving = false;
  pinForm.isDeleting = false;

  const container = document.createElement('div');
  container.className = 'pin-infowindow';

  const label = document.createElement('label');
  label.className = 'pin-infowindow__label';
  label.textContent = 'メモ';
  container.appendChild(label);

  const input = document.createElement('textarea');
  input.placeholder = 'メモを入力';
  input.className = 'pin-infowindow__input';
  input.rows = 3;
  input.value = pinForm.memo || '';
  input.addEventListener('input', (event) => {
    pinForm.memo = event.target.value;
  });
  container.appendChild(input);

  const actions = document.createElement('div');
  actions.className = 'pin-infowindow__actions';

  const saveButton = document.createElement('button');
  saveButton.type = 'button';
  saveButton.textContent = '保存';
  saveButton.className = 'pin-infowindow__button pin-infowindow__button--primary';
  saveButton.addEventListener('click', async () => {
    if (pinForm.isSaving) {
      return;
    }
    pinForm.isSaving = true;
    saveButton.disabled = true;
    try {
      if (pinForm.mode === 'edit') {
        await updatePin();
      } else {
        await savePin();
      }
      pinInfoWindow?.close();
    } catch (error) {
      console.error('Failed to save pin:', error);
      window.alert('ピンの保存に失敗しました。時間をおいて再度お試しください。');
    } finally {
      pinForm.isSaving = false;
      saveButton.disabled = false;
    }
  });

  actions.appendChild(saveButton);

  if (mode === 'edit') {
    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.textContent = '削除';
    deleteButton.className = 'pin-infowindow__button pin-infowindow__button--danger';
    deleteButton.addEventListener('click', async () => {
      if (pinForm.isDeleting) {
        return;
      }
      pinForm.isDeleting = true;
      deleteButton.disabled = true;
      try {
        await deletePin();
        pinInfoWindow?.close();
      } catch (error) {
        console.error('Failed to delete pin:', error);
        window.alert('ピンの削除に失敗しました。時間をおいて再度お試しください。');
      } finally {
        pinForm.isDeleting = false;
        deleteButton.disabled = false;
      }
    });
    actions.appendChild(deleteButton);

    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'キャンセル';
    cancelButton.className = 'pin-infowindow__button';
    cancelButton.addEventListener('click', () => {
      pinInfoWindow?.close();
    });
    actions.appendChild(cancelButton);
  } else {
    const cancelButton = document.createElement('button');
    cancelButton.type = 'button';
    cancelButton.textContent = 'キャンセル';
    cancelButton.className = 'pin-infowindow__button';
    cancelButton.addEventListener('click', () => {
      pinInfoWindow?.close();
    });
    actions.appendChild(cancelButton);
  }

  actions.appendChild(saveButton);
  container.appendChild(actions);

  if (!pinInfoWindow) {
    pinInfoWindow = new google.maps.InfoWindow();
  }

  pinInfoWindow.setContent(container);
  pinInfoWindow.setPosition(latLng);
  pinInfoWindow.open(mapInstance);
};

const savePin = async () => {
  if (!activeVisit.value) {
    return;
  }

  const payload = {
    area_id: activeVisit.value.area.id,
    visit_id: activeVisit.value.id,
    lat: pinForm.lat,
    lng: pinForm.lng,
    memo: pinForm.memo || null,
  };

  const { data } = await axios.post('/api/pins', payload);
  const pin = data?.pin;
  if (!pin) {
    throw new Error('Missing pin data.');
  }

  if (!createdPins[pin.visit_id]) {
    createdPins[pin.visit_id] = [];
  }
  createdPins[pin.visit_id].push(pin);
  loadedPinVisitIds.add(pin.visit_id);
  addCustomPinMarker(pin);
};

const updatePin = async () => {
  if (!pinForm.pinId || !createdPins[activeVisit.value.id]) {
    return;
  }

  const { data } = await axios.put(`/api/pins/${pinForm.pinId}`, {
    memo: pinForm.memo || null,
  });

  const updated = data?.pin;
  if (!updated) {
    return;
  }

  const pins = createdPins[activeVisit.value.id] || [];
  const index = pins.findIndex((pin) => pin.id === updated.id);
  if (index !== -1) {
    pins[index] = { ...pins[index], memo: updated.memo };
  }

  renderLocalPins();
};

const deletePin = async () => {
  if (!pinForm.pinId || !createdPins[activeVisit.value.id]) {
    return;
  }

  await axios.delete(`/api/pins/${pinForm.pinId}`);

  createdPins[activeVisit.value.id] = (createdPins[activeVisit.value.id] || []).filter(
    (pin) => pin.id !== pinForm.pinId
  );

  renderLocalPins();
};

watch(
  () => route.params.visitId,
  () => {
    syncActiveVisit();
  }
);

watch(activeVisit, async () => {
  await ensurePinsLoaded();
  renderSelectedArea();
});

watch(mapContainer, async () => {
  if (activeVisit.value) {
    await ensurePinsLoaded();
    renderSelectedArea();
  }
});

onMounted(() => {
  loadVisits();
});

onBeforeUnmount(() => {
  clearOverlays();
  if (mapClickListener) {
    mapClickListener.remove();
    mapClickListener = null;
  }
  if (buildingIdleListener) {
    buildingIdleListener.remove();
    buildingIdleListener = null;
  }
  if (buildingFetchTimeout) {
    clearTimeout(buildingFetchTimeout);
    buildingFetchTimeout = null;
  }
  if (buildingFetchAbortController) {
    buildingFetchAbortController.abort('component-unmounted');
    buildingFetchAbortController = null;
  }
  if (pinInfoWindow) {
    pinInfoWindow.close();
    pinInfoWindow = null;
  }
  mapInstance = null;
  mapInitPromise = null;
});
</script>
