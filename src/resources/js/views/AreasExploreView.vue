<template>
  <div class="space-y-6 lg:space-y-10">
    <section class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-8 text-white shadow-2xl sm:p-10">
      <div class="grid gap-6 lg:grid-cols-2 lg:items-center">
        <div class="space-y-4">
          <p class="text-xs font-semibold uppercase tracking-[0.4em] text-white/70">Areas</p>
          <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">すべての区域を閲覧</h1>
          <p class="text-sm leading-relaxed text-white/80">
            検索とフィルターで目的の区域を探し、区域地図を確認できます。
          </p>
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-5">
      <div class="lg:col-span-2 space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">区域を検索</h2>
            <p class="text-xs text-slate-500">番号・名称・ステータスから目的の区域を絞り込めます。</p>
          </div>

          <div class="space-y-4 border-b border-slate-100 px-5 py-4">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="option in statusFilters"
                :key="option.value"
                type="button"
                class="rounded-full border px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition"
                :class="filters.status === option.value
                  ? 'border-sky-400 bg-sky-50 text-sky-700 shadow-sm'
                  : 'border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                @click="setStatusFilter(option.value)"
              >
                {{ option.label }}
              </button>
            </div>
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">区域番号・名称で検索</label>
              <div class="relative">
                <input
                  type="search"
                  v-model="filters.search"
                  class="block w-full rounded-xl border-slate-200 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-sky-400 focus:ring-sky-400"
                  placeholder="例: 12A, 北エリア"
                />
                <svg
                  class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 1 0-14 0 7 7 0 0 0 14 0z" />
                </svg>
              </div>
            </div>
          </div>

          <div class="max-h-[520px] divide-y divide-slate-100 overflow-y-auto">
            <div v-if="state.isListLoading && !areas.length" class="space-y-4 px-5 py-6">
              <div class="h-4 w-32 animate-pulse rounded bg-slate-200"></div>
              <div v-for="n in 6" :key="n" class="space-y-2">
                <div class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
                <div class="h-3 w-2/3 animate-pulse rounded bg-slate-200"></div>
              </div>
            </div>
            <div v-else-if="state.listError" class="px-5 py-6 text-sm text-red-600">
              {{ state.listError }}
            </div>
            <div v-else-if="!areas.length" class="px-5 py-6 text-sm text-slate-500">
              条件に一致する区域が見つかりませんでした。条件を変更して再検索してください。
            </div>
            <div
              v-else
              v-for="area in areas"
              :key="area.id"
              class="transition hover:bg-slate-50"
              :class="selectedAreaSummary?.id === area.id ? 'bg-slate-100' : ''"
            >
              <button type="button" class="flex w-full flex-col gap-2 px-5 py-4 text-left focus:outline-none" @click="selectArea(area)">
                <div class="flex items-center justify-between gap-3">
                  <p class="text-sm font-semibold text-slate-900">
                    {{ area.number }}
                    <span v-if="area.name" class="text-slate-500">/ {{ area.name }}</span>
                  </p>
                  <span
                    class="text-xs font-semibold uppercase tracking-wide"
                    :class="getStatusToneClass(getAreaStatusMeta(area).tone, 'list')"
                  >
                    {{ getAreaStatusMeta(area).label }}
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  <template v-if="area.latest_visit?.end_date">
                    最終訪問: {{ area.latest_visit.end_date }}
                  </template>
                  <template v-else>
                    まだ訪問記録がありません。
                  </template>
                </p>
              </button>
            </div>
          </div>

          <div v-if="pagination.hasMore" class="border-t border-slate-100 px-5 py-3">
            <button
              type="button"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-600 disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="state.isLoadingMore"
              @click="loadMoreAreas"
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
            <p class="text-xs text-slate-500">地図・境界線を確認できます。</p>
          </div>

          <div class="space-y-5 px-5 py-5">
            <div v-if="!selectedAreaDisplay && !state.isListLoading" class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
              左のリストから区域を選択してください。
            </div>
            <div v-else-if="state.isListLoading && !selectedAreaDisplay" class="h-[360px] animate-pulse rounded-2xl bg-slate-100"></div>
            <template v-else-if="selectedAreaDisplay">
              <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                  <h3 class="text-2xl font-semibold text-slate-900">
                    {{ selectedAreaDisplay.number }}
                    <span v-if="selectedAreaDisplay.name" class="text-base font-medium text-slate-500">/ {{ selectedAreaDisplay.name }}</span>
                  </h3>
                  <span
                    class="rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide"
                    :class="selectedAreaStatus.className"
                  >
                    {{ selectedAreaStatus.label }}
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  <template v-if="selectedAreaDisplay.latest_visit?.end_date">
                    最終訪問: {{ selectedAreaDisplay.latest_visit.end_date }}
                  </template>
                  <template v-else>
                    まだ訪問記録がありません。
                  </template>
                </p>
              </div>

              <dl class="grid grid-cols-1 gap-4 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600 sm:grid-cols-2">
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">訪問開始日</dt>
                  <dd class="mt-1 font-semibold text-slate-900">{{ selectedAreaDisplay.current_visit?.start_date ?? '—' }}</dd>
                </div>
                <div>
                  <dt class="text-xs uppercase tracking-wide text-slate-400">メモ</dt>
                  <dd class="mt-1 min-h-[1.5rem] whitespace-pre-line text-slate-700">{{ selectedAreaDisplay.memo ?? '—' }}</dd>
                </div>
              </dl>

              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-semibold text-slate-900">区域地図</p>
                  <p v-if="state.isDetailLoading" class="text-xs text-slate-500">境界データを読み込み中…</p>
                </div>
                <div class="areas-my-map relative h-[360px] overflow-hidden rounded-2xl border border-slate-200">
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
            </template>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { fetchAreaDetail, fetchAreas } from '../services/areasService';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { kmlParser } from '../utils/kmlParser';

const statusFilters = [
  { value: 'all', label: 'すべて' },
  { value: 'active', label: '訪問中' },
  { value: 'available', label: '空き区域' },
];

const visitStatusLabels = {
  in_progress: '訪問中',
  pending_reassignment: '再割当待機',
  reassigned: '再割当済み',
  completed: '完了',
  canceled: '返却済み',
};

const state = reactive({
  isListLoading: true,
  isLoadingMore: false,
  isDetailLoading: false,
  listError: '',
  detailError: '',
});

const filters = reactive({
  status: 'all',
  search: '',
});

const pagination = reactive({
  page: 1,
  perPage: 40,
  hasMore: false,
  total: 0,
});

const areas = ref([]);
const selectedAreaSummary = ref(null);
const selectedAreaDetail = ref(null);
const areaDetailsCache = reactive({});

const selectedAreaDisplay = computed(() => selectedAreaDetail.value || selectedAreaSummary.value);

const getVisitStatusLabel = (status) => visitStatusLabels[status] || '状況不明';

const statusToneClasses = {
  active: {
    list: 'text-emerald-600',
    pill: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  },
  available: {
    list: 'text-slate-400',
    pill: 'border-slate-200 bg-slate-50 text-slate-600',
  },
  cooldown: {
    list: 'text-amber-600',
    pill: 'border-amber-200 bg-amber-50 text-amber-700',
  },
  unknown: {
    list: 'text-slate-400',
    pill: 'border-slate-200 bg-slate-50 text-slate-600',
  },
};

const getStatusToneClass = (tone, variant = 'list') => {
  return statusToneClasses[tone]?.[variant] ?? statusToneClasses.unknown[variant];
};

const getAreaStatusMeta = (area) => {
  if (!area) {
    return { label: '未選択', tone: 'unknown' };
  }
  if (area.is_available) {
    return { label: '空き区域', tone: 'available' };
  }
  if (area.current_visit) {
    return {
      label: getVisitStatusLabel(area.current_visit.status),
      tone: 'active',
    };
  }
  if (area.latest_visit) {
    return {
      label: getVisitStatusLabel(area.latest_visit.status),
      tone: 'cooldown',
    };
  }
  return { label: '状況確認中', tone: 'unknown' };
};

const selectedAreaStatus = computed(() => {
  const meta = getAreaStatusMeta(selectedAreaDisplay.value);
  return {
    ...meta,
    className: getStatusToneClass(meta.tone, 'pill'),
  };
});

const mapContainer = ref(null);
const mapError = ref('');
const mapConfig = ref(null);

let listRequestId = 0;
let detailRequestId = 0;
let searchDebounceId = null;
let mapInstance = null;
let mapInitPromise = null;
let advancedMarkerModule = null;
const polygonOverlays = [];
const pointMarkers = [];

const setStatusFilter = (value) => {
  if (filters.status === value) {
    return;
  }
  filters.status = value;
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

const selectArea = (area) => {
  selectedAreaSummary.value = area;
};

const sortAreasByNumber = (list) => {
  return [...list].sort((a, b) => {
    const aNum = a?.number ?? '';
    const bNum = b?.number ?? '';
    return String(aNum).localeCompare(String(bNum), 'ja', { numeric: true, sensitivity: 'base' });
  });
};

const loadAreas = async ({ reset = false } = {}) => {
  if (!reset && (state.isListLoading || state.isLoadingMore)) {
    return;
  }

  const requestId = ++listRequestId;

  if (reset) {
    pagination.page = 1;
    pagination.hasMore = false;
    areas.value = [];
    selectedAreaSummary.value = null;
    selectedAreaDetail.value = null;
    state.isListLoading = true;
  } else if (pagination.page > 1) {
    state.isLoadingMore = true;
  } else {
    state.isListLoading = true;
  }

  try {
    const params = {
      page: pagination.page,
      per_page: pagination.perPage,
    };
    if (filters.status !== 'all') {
      params.status = filters.status;
    }
    const trimmedSearch = filters.search.trim();
    if (trimmedSearch) {
      params.search = trimmedSearch;
    }

    const response = await fetchAreas(params);
    if (requestId !== listRequestId) {
      return;
    }

    const items = Array.isArray(response?.data) ? response.data : [];
    if (reset) {
      areas.value = sortAreasByNumber(items);
    } else {
      areas.value = sortAreasByNumber([...areas.value, ...items]);
    }

    const meta = response?.meta || {};
    pagination.total = meta.total ?? areas.value.length;
    pagination.hasMore = Boolean(meta.has_more);
    pagination.page = meta.current_page ?? pagination.page;

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
    }

    state.listError = '';
  } catch (error) {
    if (requestId !== listRequestId) {
      return;
    }
    console.error('Failed to load areas:', error);
    state.listError = '区域の取得に失敗しました。時間をおいて再度お試しください。';
  } finally {
    if (requestId === listRequestId) {
      state.isListLoading = false;
      state.isLoadingMore = false;
    }
  }
};

const loadMoreAreas = () => {
  if (!pagination.hasMore || state.isLoadingMore) {
    return;
  }
  pagination.page += 1;
  loadAreas({ reset: false });
};

const ensureAreaDetail = async (areaId) => {
  if (!areaId || areaDetailsCache[areaId]) {
    return;
  }

  const requestId = ++detailRequestId;
  state.isDetailLoading = true;
  state.detailError = '';

  try {
    const response = await fetchAreaDetail(areaId);
    if (requestId !== detailRequestId) {
      return;
    }

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
    if (requestId !== detailRequestId) {
      return;
    }
    console.error('Failed to load area detail:', error);
    state.detailError = '区域詳細の取得に失敗しました。';
  } finally {
    if (requestId === detailRequestId) {
      state.isDetailLoading = false;
    }
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
      mapId: 'areas-explore-map',
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

watch(
  () => filters.status,
  () => {
    pagination.page = 1;
    loadAreas({ reset: true });
  }
);

watch(
  () => filters.search,
  () => {
    if (searchDebounceId) {
      clearTimeout(searchDebounceId);
    }
    searchDebounceId = setTimeout(() => {
      pagination.page = 1;
      loadAreas({ reset: true });
    }, 350);
  }
);

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

watch(mapContainer, () => {
  if (selectedAreaDetail.value) {
    renderSelectedArea();
  }
});

onMounted(() => {
  loadAreas({ reset: true });
});

onBeforeUnmount(() => {
  clearOverlays();
  if (searchDebounceId) {
    clearTimeout(searchDebounceId);
  }
  mapInstance = null;
  mapInitPromise = null;
  advancedMarkerModule = null;
});
</script>
