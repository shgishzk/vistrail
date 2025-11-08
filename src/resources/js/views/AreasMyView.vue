<template>
  <div class="space-y-6 lg:space-y-10">
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8 sm:py-8">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-400">My Areas</p>
          <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">担当区域</h1>
          <p class="mt-2 text-sm text-slate-500">
            現在担当中の区域一覧です。区域を選択すると地図と詳細を表示します。
          </p>
        </div>
        <div class="inline-flex items-center gap-3 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700">
          <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
          ステータス: 訪問中 ({{ visits.length }})
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-5">
      <div class="lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">担当区域一覧</h2>
            <p class="text-xs text-slate-500">区域を選択して詳細を確認します。</p>
          </div>
          <div class="max-h-[520px] divide-y divide-slate-100 overflow-y-auto">
            <div v-if="state.isLoading" class="space-y-3 px-5 py-6">
              <div class="h-4 w-40 animate-pulse rounded bg-slate-200"></div>
              <div v-for="n in 3" :key="n" class="space-y-2">
                <div class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="h-3 w-2/3 animate-pulse rounded bg-slate-200"></div>
              </div>
            </div>
            <div v-else-if="state.error" class="px-5 py-4 text-sm text-red-600">
              {{ state.error }}
            </div>
            <div v-else-if="!visits.length" class="px-5 py-6 text-center text-sm text-slate-500">
              担当中の区域はありません。
            </div>
            <div
              v-else
              v-for="visit in visits"
              :key="visit.id"
              class="flex flex-col gap-3 px-5 py-4 transition hover:bg-slate-50"
              :class="selectedVisit?.id === visit.id ? 'bg-slate-100' : ''"
            >
              <button
                type="button"
                class="flex w-full flex-col gap-1 text-left focus:outline-none"
                @click="selectVisit(visit)"
              >
                <div class="flex items-center justify-between">
                  <div class="text-sm font-semibold text-slate-900">
                    {{ visit.area?.number ?? '区域' }} <span v-if="visit.area?.name" class="text-slate-500">/{{ visit.area?.name }}</span>
                  </div>
                  <span class="text-xs font-semibold uppercase tracking-wide text-emerald-600">訪問中</span>
                </div>
                <p class="text-xs text-slate-500">
                  訪問開始日: {{ visit.start_date ?? '未設定' }}
                </p>
                <p v-if="visit.memo" class="text-xs text-slate-500 line-clamp-2">{{ visit.memo }}</p>
              </button>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-sky-300 hover:text-sky-600"
                @click="openVisitMode(visit)"
              >
                訪問モード
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="lg:col-span-3">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">区域詳細</h2>
            <p class="text-xs text-slate-500">区域番号・名称と地図で境界を確認できます。</p>
          </div>
          <div class="space-y-4 px-5 py-5">
            <div v-if="!selectedVisit && !state.isLoading" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
              左のリストから区域を選択してください。
            </div>
            <div v-else-if="selectedVisit" class="space-y-3">
              <h3 class="text-xl font-semibold text-slate-900">
                {{ selectedVisit.area?.number }}
                <span v-if="selectedVisit.area?.name" class="text-base font-medium text-slate-500">/ {{ selectedVisit.area?.name }}</span>
              </h3>
              <dl class="grid grid-cols-1 gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-2">
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">訪問開始日</dt>
                  <dd class="mt-1 font-semibold text-slate-900">{{ selectedVisit.start_date ?? '未設定' }}</dd>
                </div>
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">メモ</dt>
                  <dd class="mt-1 min-h-[1.5rem] text-slate-700">{{ selectedVisit.memo ?? '—' }}</dd>
                </div>
              </dl>
              <div class="space-y-3">
                <div>
                  <p class="text-sm font-semibold text-slate-900">区域地図</p>
                  <div class="areas-my-map relative h-[360px] overflow-hidden rounded-2xl border border-slate-200">
                    <div
                      v-if="mapError"
                      class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 px-6 text-center text-sm text-red-600"
                    >
                      {{ mapError }}
                    </div>
                    <div
                      ref="mapContainer"
                      class="h-full w-full"
                      :class="{ 'opacity-30': mapError }"
                    ></div>
                  </div>
                </div>
                <button
                  type="button"
                  class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-600 disabled:cursor-not-allowed disabled:opacity-50"
                  :disabled="!selectedVisit"
                  @click="openVisitMode"
                >
                  訪問モードで表示
                </button>
              </div>
            </div>
            <div v-else-if="state.isLoading" class="h-[360px] animate-pulse rounded-2xl bg-slate-100"></div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { fetchMyAreas } from '../services/areasService';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { kmlParser } from '../utils/kmlParser';

const state = reactive({
  isLoading: true,
  error: '',
});

const router = useRouter();
const visits = ref([]);
const selectedVisit = ref(null);
const mapContainer = ref(null);
const mapError = ref('');
const mapConfig = ref(null);

let mapInstance = null;
let mapInitPromise = null;
let advancedMarkerModule = null;
const polygonOverlays = [];
const pointMarkers = [];

const loadVisits = async () => {
  state.isLoading = true;
  try {
    const { visits: payload = [] } = await fetchMyAreas();
    visits.value = payload;
    state.error = '';

    if (!selectedVisit.value && payload.length) {
      selectedVisit.value = payload[0];
    } else if (selectedVisit.value) {
      const refreshed = payload.find((visit) => visit.id === selectedVisit.value.id);
      selectedVisit.value = refreshed || payload[0] || null;
    }
  } catch (error) {
    console.error('Failed to load my areas:', error);
    state.error = '担当区域の取得に失敗しました。時間をおいて再度お試しください。';
    visits.value = [];
    selectedVisit.value = null;
  } finally {
    state.isLoading = false;
  }
};

const selectVisit = (visit) => {
  selectedVisit.value = visit;
};

const openVisitMode = (visit = selectedVisit.value) => {
  if (!visit) {
    return;
  }

  router.push({
    name: 'areasMyVisit',
    params: {
      visitId: visit.id,
    },
  });
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
      mapId: 'areas-my-map',
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
    const overlay = polygonOverlays.pop();
    overlay.setMap(null);
  }

  while (pointMarkers.length) {
    const marker = pointMarkers.pop();
    if (marker.map) {
      marker.map = null;
    } else if (marker.setMap) {
      marker.setMap(null);
    }
  }
};

const renderSelectedArea = async () => {
  if (!selectedVisit.value || !mapContainer.value) {
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

  const boundary = selectedVisit.value.area?.boundary_kml;
  clearOverlays();

  if (!mapInstance) {
    mapError.value = 'マップを表示できませんでした。';
    return;
  }

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
      mapInstance.fitBounds(bounds, 40);
    }

    mapError.value = '';
  } catch (error) {
    console.error('Failed to render KML boundary:', error);
    mapError.value = '境界データの解析に失敗しました。';
    clearOverlays();
  }
};

watch(selectedVisit, () => {
  renderSelectedArea();
});

watch(mapContainer, () => {
  if (selectedVisit.value) {
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
