<template>
  <div class="space-y-6 lg:space-y-10">
    <section class="rounded-3xl border border-slate-200 bg-gradient-to-br from-emerald-600 via-emerald-500 to-sky-500 p-8 text-white shadow-2xl sm:p-10">
      <div class="grid gap-6 lg:grid-cols-2 lg:items-center">
        <div class="space-y-4">
          <p class="text-xs font-semibold uppercase tracking-[0.4em] text-white/80">Areas</p>
          <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">区域を選んで奉仕する</h1>
          <p class="text-sm leading-relaxed text-white/90">
            現在空いている区域の中から、奉仕したい区域を選んでください。区域の詳細情報や境界線も確認できます。
          </p>
        </div>
      </div>
    </section>

    <section>
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
          <h2 class="text-base font-semibold text-slate-900">位置で探す</h2>
          <p class="text-xs text-slate-500">地図の中央付近にある空き区域から優先的に表示します。</p>
        </div>
        <div class="relative h-[320px] overflow-hidden rounded-b-2xl">
          <div
            v-if="searchMapError"
            class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 px-6 text-center text-sm text-red-600"
          >
            {{ searchMapError }}
          </div>
          <div ref="searchMapContainer" class="h-full w-full"></div>
          <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="flex flex-col items-center gap-1 text-emerald-600">
              <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-white/95 text-emerald-600 shadow-lg ring-1 ring-emerald-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21a8.25 8.25 0 0 1 15 0" />
                </svg>
              </span>
              <span class="h-8 w-1 rounded-full bg-emerald-500/70"></span>
            </div>
          </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">
          マップ中央から100m以上移動すると一覧が自動更新されます。
          <span v-if="searchCenterDisplay" class="ml-2 font-semibold text-slate-700">現在: {{ searchCenterDisplay }}</span>
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-5">
      <div class="lg:col-span-2 space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
              <h2 class="text-base font-semibold text-slate-900">空き区域一覧</h2>
              <p class="text-xs text-slate-500">現在「空き区域」と判定された区域のみ表示しています。</p>
            </div>
            <button
              type="button"
              class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100"
              @click="refreshAreas"
            >
              <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
              最新情報に更新
            </button>
          </div>

          <div class="max-h-[560px] divide-y divide-slate-100 overflow-y-auto">
            <div v-if="state.isLoading && !areas.length" class="space-y-3 px-5 py-6">
              <div class="h-4 w-32 animate-pulse rounded bg-slate-200"></div>
              <div v-for="n in 5" :key="n" class="space-y-2">
                <div class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="h-3 w-2/3 animate-pulse rounded bg-slate-200"></div>
              </div>
            </div>

            <div v-else-if="state.listError" class="px-5 py-6 text-sm text-red-600">
              {{ state.listError }}
            </div>

            <div v-else-if="!areas.length" class="px-5 py-10 text-center text-sm text-slate-500">
              現在空いている区域はありません。時間をおいて再度ご確認ください。
            </div>

            <template v-else>
              <div
                v-for="area in areas"
                :key="area.id"
                class="flex flex-col gap-3 px-5 py-4 transition hover:bg-slate-50"
                :class="selectedAreaSummary?.id === area.id ? 'bg-slate-100' : ''"
              >
                <button
                  type="button"
                  class="flex w-full flex-col gap-2 text-left"
                  @click="selectArea(area)"
                >
                  <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-900">
                      {{ area.number }}
                      <span v-if="area.name" class="text-slate-500">/ {{ area.name }}</span>
                    </p>
                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                      空き区域
                    </span>
                  </div>
                  <p class="text-xs text-slate-500">
                    <template v-if="area.latest_visit?.end_date">
                      最終訪問日: {{ area.latest_visit.end_date }}
                    </template>
                    <template v-else>
                      訪問履歴が登録されていません。
                    </template>
                    <span v-if="formatDistance(area.distance_meters)" class="ml-2 text-slate-400">
                      距離: {{ formatDistance(area.distance_meters) }}
                    </span>
                  </p>
                </button>
                <button
                  type="button"
                  :data-hs-overlay="`#${modalId}`"
                  class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700"
                  @click="openConfirm(area)"
                >
                  この区域を選んで奉仕する
                </button>
              </div>
            </template>
          </div>

          <div v-if="pagination.hasMore" class="border-t border-slate-100 px-5 py-4">
            <button
              type="button"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-600 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="state.isLoadingMore"
              @click="loadMore"
            >
              <svg
                v-if="state.isLoadingMore"
                class="h-4 w-4 animate-spin text-sky-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4A4 4 0 0 0 8 12H4z"></path>
              </svg>
              {{ state.isLoadingMore ? '読み込み中…' : 'さらに表示' }}
            </button>
          </div>
        </div>
      </div>

      <div class="lg:col-span-3">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">区域詳細</h2>
            <p class="text-xs text-slate-500">区域番号、最新の訪問状況、境界線を確認できます。</p>
          </div>

          <div class="space-y-4 px-5 py-5">
            <div v-if="!selectedAreaDisplay && !state.isLoading" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
              左のリストから区域を選択してください。
            </div>

            <div v-else-if="state.isLoading && !selectedAreaDisplay" class="h-[320px] animate-pulse rounded-2xl bg-slate-100"></div>

            <template v-else-if="selectedAreaDisplay">
              <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                  <h3 class="text-2xl font-semibold text-slate-900">
                    {{ selectedAreaDisplay.number }}
                    <span v-if="selectedAreaDisplay.name" class="text-base font-medium text-slate-500">/ {{ selectedAreaDisplay.name }}</span>
                  </h3>
                  <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                    空き区域
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  <template v-if="selectedAreaDisplay.latest_visit?.end_date">
                    最終訪問日: {{ selectedAreaDisplay.latest_visit.end_date }}
                  </template>
                  <template v-else>
                    訪問履歴が登録されていません。
                  </template>
                </p>
              </div>

              <dl class="grid grid-cols-1 gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-2">
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">中心座標</dt>
                  <dd class="mt-1 font-semibold text-slate-900">{{ formatCenter(selectedAreaDisplay) }}</dd>
                </div>
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">メモ</dt>
                  <dd class="mt-1 min-h-[1.5rem] whitespace-pre-line text-slate-700">{{ selectedAreaDisplay.memo ?? '—' }}</dd>
                </div>
                <div v-if="formatDistance(selectedAreaDisplay.distance_meters)">
                  <dt class="text-xs uppercase tracking-wide text-slate-400">マップ中央からの距離</dt>
                  <dd class="mt-1 font-semibold text-slate-900">{{ formatDistance(selectedAreaDisplay.distance_meters) }}</dd>
                </div>
              </dl>

              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-semibold text-slate-900">区域地図</p>
                  <p v-if="state.isDetailLoading" class="text-xs text-slate-500">境界データを読み込み中…</p>
                </div>
                <div class="areas-pickup-map relative h-[360px] overflow-hidden rounded-2xl border border-slate-200">
                  <div
                    v-if="mapError"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 px-6 text-center text-sm text-red-600"
                  >
                    {{ mapError }}
                  </div>
                  <div ref="mapContainer" class="h-full w-full" :class="{ 'opacity-30': mapError }"></div>
                </div>
                <p v-if="state.detailError" class="text-xs text-red-600">{{ state.detailError }}</p>
              </div>

              <button
                type="button"
                class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                :data-hs-overlay="`#${modalId}`"
                :disabled="!selectedAreaDisplay"
                @click="openConfirm(selectedAreaDisplay)"
              >
                この区域を選んで奉仕する
              </button>
            </template>
          </div>
        </div>
      </div>
    </section>

    <div
      :id="modalId"
      class="hs-overlay hidden fixed top-0 left-0 z-[80] size-full overflow-y-auto bg-slate-900/70 p-4 sm:px-6 md:p-8"
    >
      <div class="mx-auto mt-10 max-w-xl rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/5 hs-overlay-open:animate-fade-in hs-overlay-close:animate-fade-out">
        <div class="border-b border-slate-100 px-6 py-4">
          <h3 class="text-lg font-semibold text-slate-900">区域選択の確認</h3>
          <p class="mt-1 text-sm text-slate-500">
            以下の区域を選んで奉仕を開始してもよろしいですか？
          </p>
        </div>
        <div class="px-6 py-5">
          <p v-if="!modalArea" class="text-sm text-slate-500">区域が選択されていません。</p>
          <div v-else class="space-y-3">
            <div>
              <p class="text-sm text-slate-500">対象区域</p>
              <p class="text-lg font-semibold text-slate-900">{{ selectedAreaLabel }}</p>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
          <button
            type="button"
            class="inline-flex items-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-800"
            :data-hs-overlay="`#${modalId}`"
          >
            キャンセル
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2"
            @click="confirmPickup"
          >
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { fetchAreaDetail, fetchAreas } from '../services/areasService';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { kmlParser } from '../utils/kmlParser';

const modalId = 'hs-area-pickup-confirm';
const SEARCH_MAP_MOVE_THRESHOLD_METERS = 100;

const state = reactive({
  isLoading: true,
  isLoadingMore: false,
  listError: '',
  isDetailLoading: false,
  detailError: '',
});

const areas = ref([]);
const pagination = reactive({
  page: 1,
  perPage: 40,
  hasMore: false,
});

const selectedAreaSummary = ref(null);
const selectedAreaDetail = ref(null);
const areaDetailsCache = reactive({});
const modalArea = ref(null);

const searchCenter = ref(null);
const selectedAreaDisplay = computed(() => selectedAreaDetail.value || selectedAreaSummary.value);
const selectedAreaLabel = computed(() => {
  if (!modalArea.value) {
    return '';
  }
  return `No. ${modalArea.value.number}${modalArea.value.name ? ` ${modalArea.value.name}` : ''}`;
});
const searchCenterDisplay = computed(() => {
  if (!searchCenter.value) {
    return '';
  }
  return `${searchCenter.value.lat.toFixed(5)}, ${searchCenter.value.lng.toFixed(5)}`;
});

const mapContainer = ref(null);
const searchMapContainer = ref(null);
const mapError = ref('');
const searchMapError = ref('');
const mapConfig = ref(null);
let mapInstance = null;
let mapInitPromise = null;
let searchMapInstance = null;
let searchMapInitPromise = null;
let searchMapIdleListener = null;
let advancedMarkerModule = null;
const polygonOverlays = [];
const pointMarkers = [];

const loadAreas = async ({ reset = false } = {}) => {
  if (state.isLoading || state.isLoadingMore) {
    if (!reset) {
      return;
    }
  }

  if (reset) {
    pagination.page = 1;
    pagination.hasMore = false;
    areas.value = [];
    selectedAreaSummary.value = null;
    selectedAreaDetail.value = null;
    state.isLoading = true;
  } else if (pagination.page > 1) {
    state.isLoadingMore = true;
  } else {
    state.isLoading = true;
  }

  try {
    const params = {
      status: 'available',
      page: pagination.page,
      per_page: pagination.perPage,
    };
    if (searchCenter.value) {
      params.near_lat = searchCenter.value.lat;
      params.near_lng = searchCenter.value.lng;
    }

    const { data, meta } = await fetchAreas(params);

    const items = Array.isArray(data) ? data : [];
    if (reset) {
      areas.value = items;
    } else {
      areas.value = [...areas.value, ...items];
    }

    pagination.hasMore = Boolean(meta?.has_more);
    pagination.page = meta?.current_page ?? pagination.page;
    state.listError = '';

    await nextTick();
    if (typeof window !== 'undefined' && window.HSStaticMethods?.autoInit) {
      window.HSStaticMethods.autoInit();
    }

    if (areas.value.length) {
      if (!selectedAreaSummary.value) {
        selectedAreaSummary.value = areas.value[0];
      } else {
        const refreshed = areas.value.find((area) => area.id === selectedAreaSummary.value?.id);
        selectedAreaSummary.value = refreshed || areas.value[0];
      }
    } else {
      selectedAreaSummary.value = null;
      selectedAreaDetail.value = null;
      mapError.value = '';
      clearOverlays();
    }
  } catch (error) {
    console.error('Failed to load available areas:', error);
    state.listError = '区域の取得に失敗しました。時間をおいて再度お試しください。';
  } finally {
    state.isLoading = false;
    state.isLoadingMore = false;
  }
};

const loadMore = () => {
  if (!pagination.hasMore || state.isLoadingMore) {
    return;
  }
  pagination.page += 1;
  loadAreas();
};

const refreshAreas = () => {
  loadAreas({ reset: true });
};

const selectArea = (area) => {
  selectedAreaSummary.value = area;
};

const openConfirm = async (area) => {
  if (!area) {
    return;
  }
  modalArea.value = area;
  selectedAreaSummary.value = area;
  await nextTick();
  if (typeof window !== 'undefined' && window.HSOverlay?.open) {
    window.HSOverlay.open(`#${modalId}`);
  }
};

const formatCenter = (area) => {
  if (!area) {
    return '—';
  }
  const lat = Number(area.center_lat);
  const lng = Number(area.center_lng);
  if (Number.isFinite(lat) && Number.isFinite(lng) && (lat !== 0 || lng !== 0)) {
    return `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
  }
  return '—';
};

const ensureAreaDetail = async (areaId) => {
  if (!areaId || areaDetailsCache[areaId]) {
    selectedAreaDetail.value = areaDetailsCache[areaId] || selectedAreaDetail.value;
    return;
  }

  state.isDetailLoading = true;
  state.detailError = '';

  try {
    const response = await fetchAreaDetail(areaId);
    const detail = response?.data || null;
    if (detail) {
      areaDetailsCache[areaId] = detail;
      if (selectedAreaSummary.value?.id === areaId) {
        selectedAreaDetail.value = detail;
      }
      mapError.value = '';
    } else {
      state.detailError = '区域詳細を取得できませんでした。';
    }
  } catch (error) {
    console.error('Failed to load area detail:', error);
    state.detailError = '区域詳細の取得に失敗しました。';
  } finally {
    state.isDetailLoading = false;
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

    const config = await ensureMapConfig();

    if (!config?.maps_api_key) {
      throw new Error('Google Maps APIキーが設定されていません。');
    }

    const defaultPosition = {
      lat: Number(config?.default_position?.lat) || 35.0238868,
      lng: Number(config?.default_position?.lng) || 135.760201,
    };

    await loadGoogleMaps(config.maps_api_key);
    if (google.maps?.importLibrary) {
      await google.maps.importLibrary('maps');
      if (!advancedMarkerModule) {
        advancedMarkerModule = await google.maps.importLibrary('marker');
      }
    }

    mapInstance = new google.maps.Map(mapContainer.value, {
      center: defaultPosition,
      zoom: 13,
      mapId: 'areas-pickup-map',
      zoomControl: true,
      mapTypeControl: false,
      streetViewControl: false,
      fullscreenControl: false,
    });
  })();

  return mapInitPromise;
};

const initializeSearchMap = async () => {
  if (searchMapInitPromise) {
    return searchMapInitPromise;
  }

  searchMapInitPromise = (async () => {
    if (typeof window === 'undefined' || !searchMapContainer.value) {
      return;
    }

    try {
      const config = await ensureMapConfig();
      if (!config?.maps_api_key) {
        throw new Error('Google Maps APIキーが設定されていません。');
      }

      const defaultPosition = {
        lat: Number(config?.default_position?.lat) || 35.0238868,
        lng: Number(config?.default_position?.lng) || 135.760201,
      };

      await loadGoogleMaps(config.maps_api_key);
      if (google.maps?.importLibrary) {
        await google.maps.importLibrary('maps');
      }

      searchMapInstance = new google.maps.Map(searchMapContainer.value, {
        center: defaultPosition,
        zoom: 13,
        mapId: 'areas-pickup-search-map',
        zoomControl: true,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
      });

      searchMapIdleListener = searchMapInstance.addListener('idle', handleSearchMapIdle);

      const center = searchMapInstance.getCenter();
      if (center) {
        searchCenter.value = {
          lat: center.lat(),
          lng: center.lng(),
        };
      }
    } catch (error) {
      console.error('Failed to initialize search map:', error);
      searchMapError.value = '位置検索マップの表示に失敗しました。';
    }
  })();

  return searchMapInitPromise;
};

const clearOverlays = () => {
  while (polygonOverlays.length) {
    const overlay = polygonOverlays.pop();
    overlay.setMap(null);
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
  if (!selectedAreaDetail.value || !mapContainer.value) {
    clearOverlays();
    return;
  }

  if (!selectedAreaDetail.value.boundary_kml) {
    mapError.value = 'この区域には境界データが登録されていません。';
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

  if (!mapInstance) {
    mapError.value = 'マップを表示できませんでした。';
    return;
  }

  mapError.value = '';
  clearOverlays();

  try {
    const xml = kmlParser.parseDocument(selectedAreaDetail.value.boundary_kml);
    if (!xml || xml.querySelector('parsererror')) {
      throw new Error('invalid_kml');
    }

    const styles = kmlParser.parseStyles(xml);
    const polygons = kmlParser.parsePolygons(xml);
    const points = kmlParser.parsePoints(xml);

    const resolveStyle = (styleUrl) => {
      if (!styleUrl) {
        return {};
      }
      return styles[styleUrl] || styles[styleUrl.replace(/^#/, '')] || {};
    };

    const bounds = new google.maps.LatLngBounds();
    let geometryCount = 0;

    polygons.forEach((polygonDatum) => {
      if (!Array.isArray(polygonDatum.coordinates) || polygonDatum.coordinates.length < 3) {
        return;
      }

      polygonDatum.coordinates.forEach((point) => bounds.extend(point));

      const style = resolveStyle(polygonDatum.styleUrl);
      const strokeEnabled = style.polyOutline !== '0';
      const fillEnabled = style.polyFill !== '0';
      const stroke = kmlParser.parseKmlColor(style.lineColor, '#0284c7', strokeEnabled ? 1 : 0);
      const fill = kmlParser.parseKmlColor(style.polyColor, '#38bdf8', fillEnabled ? 0.25 : 0);
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
      mapError.value = '境界データを描画できませんでした。';
      return;
    }

    if (!bounds.isEmpty()) {
      mapInstance.fitBounds(bounds, 40);
    }
  } catch (error) {
    console.error('Failed to render KML boundary:', error);
    mapError.value = '境界データの解析に失敗しました。';
    clearOverlays();
  }
};

const confirmPickup = () => {
  if (!modalArea.value) {
    return;
  }

  console.log('pickup: ', modalArea.value);
  if (typeof window !== 'undefined' && window.HSOverlay) {
    window.HSOverlay.close(`#${modalId}`);
  }
};

watch(selectedAreaSummary, (area) => {
  if (!area) {
    selectedAreaDetail.value = null;
    state.detailError = '';
    mapError.value = '';
    clearOverlays();
    return;
  }

  state.detailError = '';
  mapError.value = '';

  if (!area.has_boundary) {
    selectedAreaDetail.value = null;
    mapError.value = 'この区域には境界データが登録されていません。';
    clearOverlays();
    return;
  }

  if (areaDetailsCache[area.id]) {
    selectedAreaDetail.value = areaDetailsCache[area.id];
    return;
  }

  selectedAreaDetail.value = null;
  ensureAreaDetail(area.id);
});

const ensureMapConfig = async () => {
  if (mapConfig.value) {
    return mapConfig.value;
  }
  const { data } = await axios.get('/api/map/config');
  mapConfig.value = data;
  return mapConfig.value;
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

const handleSearchMapIdle = () => {
  if (!searchMapInstance) {
    return;
  }
  const center = searchMapInstance.getCenter();
  if (!center) {
    return;
  }
  const newCenter = {
    lat: center.lat(),
    lng: center.lng(),
  };

  if (!searchCenter.value) {
    searchCenter.value = newCenter;
    return;
  }

  const moved = distanceInMeters(
    searchCenter.value.lat,
    searchCenter.value.lng,
    newCenter.lat,
    newCenter.lng
  );

  if (moved >= SEARCH_MAP_MOVE_THRESHOLD_METERS) {
    searchCenter.value = newCenter;
  }
};

const formatDistance = (meters) => {
  if (!Number.isFinite(meters)) {
    return '';
  }
  if (meters >= 1000) {
    return `${(meters / 1000).toFixed(1)} km`;
  }
  return `${Math.round(meters)} m`;
};

watch(selectedAreaSummary, (area) => {
  if (!area) {
    selectedAreaDetail.value = null;
    state.detailError = '';
    mapError.value = '';
    clearOverlays();
    return;
  }

  state.detailError = '';
  mapError.value = '';

  if (!area.has_boundary) {
    selectedAreaDetail.value = null;
    mapError.value = 'この区域には境界データが登録されていません。';
    clearOverlays();
    return;
  }

  if (areaDetailsCache[area.id]) {
    selectedAreaDetail.value = areaDetailsCache[area.id];
    return;
  }

  selectedAreaDetail.value = null;
  ensureAreaDetail(area.id);
});

watch(selectedAreaDetail, () => {
  renderSelectedArea();
});

watch(mapContainer, (container, previous) => {
  if (!container) {
    clearOverlays();
    mapInstance = null;
    mapInitPromise = null;
    return;
  }
  if (container !== previous) {
    mapInstance = null;
    mapInitPromise = null;
  }
  if (selectedAreaDetail.value) {
    renderSelectedArea();
  }
});

watch(searchMapContainer, (container, previous) => {
  if (!container) {
    if (searchMapIdleListener) {
      searchMapIdleListener.remove();
      searchMapIdleListener = null;
    }
    searchMapInstance = null;
    searchMapInitPromise = null;
    return;
  }
  if (container !== previous) {
    if (searchMapIdleListener) {
      searchMapIdleListener.remove();
      searchMapIdleListener = null;
    }
    searchMapInstance = null;
    searchMapInitPromise = null;
  }
  initializeSearchMap();
});

watch(searchCenter, (center, previous) => {
  if (!center) {
    return;
  }
  if (previous) {
    const moved = distanceInMeters(previous.lat, previous.lng, center.lat, center.lng);
    if (moved < 1) {
      return;
    }
  }
  refreshAreas();
});

onMounted(() => {
  loadAreas({ reset: true });
  if (searchMapContainer.value) {
    initializeSearchMap();
  }
});

onBeforeUnmount(() => {
  clearOverlays();
  if (searchMapIdleListener) {
    searchMapIdleListener.remove();
    searchMapIdleListener = null;
  }
  mapInstance = null;
  mapInitPromise = null;
  searchMapInstance = null;
  searchMapInitPromise = null;
  advancedMarkerModule = null;
});
</script>
