<template>
  <div class="space-y-6 lg:space-y-10">
    <section class="rounded-2xl border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8 sm:py-8">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-400">Reassignment</p>
          <h1 class="text-2xl font-semibold text-slate-900 sm:text-3xl">区域の受け取り</h1>
          <p class="mt-2 text-sm text-slate-500">
            再割当待機になっている区域の一覧です。区域の詳細と境界を確認して受け取ることができます。
          </p>
        </div>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="state.isLoading"
          @click="refreshVisits"
        >
          <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0112.713-5.303M19.5 12a7.5 7.5 0 01-12.713 5.303M16.5 6.75V3.75m0 3h-3m3 0l-2.25-2.25M7.5 17.25v3m0-3h3m-3 0l2.25 2.25" />
          </svg>
          <span>再読み込み</span>
        </button>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-5">
      <div class="lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">受け取り可能な区域</h2>
            <p class="text-xs text-slate-500">「再割当待機」になっている区域のみ表示しています。</p>
          </div>
          <div class="max-h-[520px] divide-y divide-slate-100 overflow-y-auto">
            <div v-if="state.isLoading" class="space-y-3 px-5 py-6">
              <div class="h-4 w-32 animate-pulse rounded bg-slate-200"></div>
              <div v-for="n in 4" :key="n" class="space-y-2">
                <div class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="h-3 w-2/3 animate-pulse rounded bg-slate-200"></div>
              </div>
            </div>
            <div v-else-if="state.error" class="px-5 py-6 text-sm text-red-600">
              {{ state.error }}
            </div>
            <div v-else-if="!visits.length" class="px-5 py-10 text-center text-sm text-slate-500">
              現在受け取り可能な区域はありません。
            </div>
            <template v-else>
              <div
                v-for="visit in visits"
                :key="visit.id"
                class="flex flex-col gap-2 px-5 py-4 transition hover:bg-slate-50"
                :class="selectedVisit?.id === visit.id ? 'bg-slate-100' : ''"
              >
                <button
                  type="button"
                  class="flex w-full flex-col gap-1 text-left focus:outline-none"
                  @click="selectVisit(visit)"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-slate-900">
                      No. {{ visit.area?.number ?? '—' }}
                      <span v-if="visit.area?.name" class="text-slate-500">/ {{ visit.area.name }}</span>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-amber-600">再割当待機</span>
                  </div>
                  <p class="text-xs text-slate-500">
                    前回担当:
                    <span class="font-semibold text-slate-700">
                      {{ visit.previous_publisher?.name ?? '—' }}
                    </span>
                  </p>
                  <p class="text-xs text-slate-500">更新日: {{ visit.updated_at ?? '—' }}</p>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:border-sky-300 hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200"
                  @click="openConfirm(visit)"
                >
                  この区域を受け取る
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>

      <div class="lg:col-span-3">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">区域詳細</h2>
            <p class="text-xs text-slate-500">区域番号・名称と地図を確認してから受け取れます。</p>
          </div>
          <div class="space-y-4 px-5 py-5">
            <div v-if="!selectedVisit && !state.isLoading" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
              左のリストから区域を選択してください。
            </div>
            <div v-else-if="selectedVisit" class="space-y-4">
              <div>
                <h3 class="text-xl font-semibold text-slate-900">
                  {{ selectedVisit.area?.number ?? '—' }}
                  <span v-if="selectedVisit.area?.name" class="text-base font-medium text-slate-500">/ {{ selectedVisit.area?.name }}</span>
                </h3>
                <div class="mt-3 grid grid-cols-1 gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-2">
                  <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">前回担当</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ selectedVisit.previous_publisher?.name ?? '—' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-400">更新日</dt>
                    <dd class="mt-1 font-semibold text-slate-900">{{ selectedVisit.updated_at ?? '—' }}</dd>
                  </div>
                </div>
              </div>
              <div>
                <p class="text-sm font-semibold text-slate-900">区域地図</p>
                <div class="relative h-[360px] overflow-hidden rounded-2xl border border-slate-200">
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
                class="inline-flex w-full items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="!selectedVisit"
                @click="openConfirm(selectedVisit)"
              >
                この区域を受け取る
              </button>
            </div>
            <div v-else-if="state.isLoading" class="h-[360px] animate-pulse rounded-2xl bg-slate-100"></div>
          </div>
        </div>
      </div>
    </section>

    <transition name="fade">
      <div
        v-if="modalVisit"
        class="fixed inset-0 z-[90] flex items-start justify-center overflow-y-auto bg-slate-900/70 p-4 sm:px-6 md:p-8"
        role="dialog"
        aria-modal="true"
        @click.self="closeConfirm"
      >
        <div class="mx-auto mt-10 w-full max-w-md rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/5">
          <div class="space-y-6 p-6 sm:p-8">
            <div class="flex items-start gap-4">
              <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16m-7 5H4" />
                </svg>
              </span>
              <div class="space-y-2 text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Confirm</p>
                <h3 class="text-xl font-semibold text-slate-900">この区域を受け取りますか？</h3>
                <p class="text-sm text-slate-600">
                  現在の訪問を「再割当済み」に更新し、あなたの訪問として新しいレコードを作成します。すぐに訪問モードで管理できます。
                </p>
              </div>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">対象区域</p>
              <p class="mt-1 text-base font-semibold text-slate-900">
                {{ modalVisit?.area?.number ?? '—' }}
                <span v-if="modalVisit?.area?.name" class="text-sm font-medium text-slate-500">/ {{ modalVisit.area.name }}</span>
              </p>
              <p class="text-xs text-slate-500">
                前回担当: {{ modalVisit?.previous_publisher?.name ?? '—' }}
              </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
              <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 sm:w-auto"
                :disabled="isSubmitting || !modalVisit"
                @click="handleAccept"
              >
                <svg
                  v-if="isSubmitting"
                  class="-ml-1 mr-2 h-4 w-4 animate-spin text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4A4 4 0 0 0 8 12H4z" />
                </svg>
                OK
              </button>
              <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-200 focus:ring-offset-2 sm:w-auto"
                :disabled="isSubmitting"
                @click="closeConfirm"
              >
                キャンセル
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { kmlParser } from '../utils/kmlParser';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { toast } from '../utils/toast';
import { acceptReassignment, fetchReassignableVisits } from '../services/visitsService';

const state = reactive({
  isLoading: true,
  error: '',
});

const visits = ref([]);
const selectedVisit = ref(null);
const modalVisit = ref(null);
const isSubmitting = ref(false);

const mapContainer = ref(null);
const mapError = ref('');
const mapConfig = ref(null);
let mapInstance = null;
let mapInitPromise = null;
let advancedMarkerModule = null;
const polygonOverlays = [];
const pointMarkers = [];
let markerStyles = {};
let fallbackMarkerStyle = {
  background: '#607D8B',
  borderColor: '#455A64',
  glyphColor: '#455A64',
};

const applyMarkerStyles = (styles = {}) => {
  markerStyles = styles || {};
  fallbackMarkerStyle = {
    background: markerStyles.default?.background || '#607D8B',
    borderColor: markerStyles.default?.borderColor || '#455A64',
    glyphColor: markerStyles.default?.glyphColor || markerStyles.default?.borderColor || '#455A64',
  };
};

const loadVisits = async () => {
  state.isLoading = true;
  try {
    const { visits: payload = [] } = await fetchReassignableVisits();
    visits.value = payload;
    state.error = '';

    if (!payload.length) {
      selectedVisit.value = null;
    } else if (!selectedVisit.value) {
      selectedVisit.value = payload[0];
    } else {
      const refreshed = payload.find((visit) => visit.id === selectedVisit.value?.id);
      selectedVisit.value = refreshed || payload[0];
    }
  } catch (error) {
    console.error('Failed to load reassignable visits:', error);
    state.error = '区域の取得に失敗しました。時間をおいて再度お試しください。';
    visits.value = [];
    selectedVisit.value = null;
  } finally {
    state.isLoading = false;
  }
};

const refreshVisits = () => {
  if (state.isLoading) {
    return;
  }
  loadVisits();
};

const selectVisit = (visit) => {
  selectedVisit.value = visit;
};

const openConfirm = (visit) => {
  if (!visit) {
    return;
  }
  modalVisit.value = visit;
};

const closeConfirm = () => {
  modalVisit.value = null;
  isSubmitting.value = false;
};

const handleAccept = async () => {
  if (!modalVisit.value) {
    return;
  }
  isSubmitting.value = true;
  try {
    await acceptReassignment(modalVisit.value.id);
    toast.success('区域を受け取りました。');
    closeConfirm();
    await loadVisits();
  } catch (error) {
    console.error('Failed to accept reassigned area:', error);
    const message =
      error?.response?.data?.message ||
      error?.response?.data?.errors?.status?.[0] ||
      '区域の受け取りに失敗しました。';
    toast.error(message);
  } finally {
    isSubmitting.value = false;
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

    applyMarkerStyles(mapConfig.value?.marker_styles || {});

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
      mapId: 'areas-accept-reassign-map',
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
    if (marker?.setMap) {
      marker.setMap(null);
    } else if (marker?.map) {
      marker.map = null;
    }
  }
};

const renderSelectedArea = async () => {
  if (!selectedVisit.value || !mapContainer.value) {
    clearOverlays();
    mapError.value = '';
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
      const stroke = kmlParser.parseKmlColor(style.lineColor, '#0ea5e9', strokeEnabled ? 1 : 0);
      const fill = kmlParser.parseKmlColor(style.polyColor, '#bae6fd', fillEnabled ? 0.35 : 0);
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
          title: point.name || '',
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
  advancedMarkerModule = null;
});
</script>
