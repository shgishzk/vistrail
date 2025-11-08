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
let advancedMarkerModule = null;
let mapClickListener = null;
let pinInfoWindow = null;
const polygonOverlays = [];
const pointMarkers = [];
const userPinMarkers = [];

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

    await loadGoogleMaps(mapConfig.value.maps_api_key);
    if (google.maps?.importLibrary) {
      await google.maps.importLibrary('maps');
      if (!advancedMarkerModule) {
        advancedMarkerModule = await google.maps.importLibrary('marker');
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

  while (userPinMarkers.length) {
    const marker = userPinMarkers.pop();
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
      advancedMarkerModule?.AdvancedMarkerElement ||
      google.maps.marker?.AdvancedMarkerElement ||
      null;

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

  while (userPinMarkers.length) {
    const marker = userPinMarkers.pop();
    if (marker?.map) {
      marker.map = null;
    } else if (marker?.setMap) {
      marker.setMap(null);
    }
  }

  const visitPins = createdPins[activeVisit.value.id] || [];
  visitPins.forEach((pin) => {
    addCustomPinMarker(pin);
  });
};

const addCustomPinMarker = (pin) => {
  if (!mapInstance || !google?.maps) {
    return;
  }

  const position = { lat: Number(pin.lat), lng: Number(pin.lng) };
  const AdvancedMarkerElement =
    advancedMarkerModule?.AdvancedMarkerElement ||
    google.maps.marker?.AdvancedMarkerElement ||
    null;

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
      if (!window.confirm('このピンを削除しますか？')) {
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
  if (pinInfoWindow) {
    pinInfoWindow.close();
    pinInfoWindow = null;
  }
  mapInstance = null;
  mapInitPromise = null;
});
</script>
