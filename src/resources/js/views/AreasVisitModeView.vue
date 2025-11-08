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
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
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

let mapInstance = null;
let mapInitPromise = null;
let advancedMarkerModule = null;
const polygonOverlays = [];
const pointMarkers = [];

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
    });
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

    mapError.value = '';
  } catch (error) {
    console.error('Failed to render KML boundary:', error);
    mapError.value = '境界データの解析に失敗しました。';
    clearOverlays();
  }
};

watch(
  () => route.params.visitId,
  () => {
    syncActiveVisit();
  }
);

watch(activeVisit, () => {
  renderSelectedArea();
});

watch(mapContainer, () => {
  if (activeVisit.value) {
    renderSelectedArea();
  }
});

onMounted(() => {
  loadVisits();
});

onBeforeUnmount(() => {
  clearOverlays();
  mapInstance = null;
  mapInitPromise = null;
});
</script>
