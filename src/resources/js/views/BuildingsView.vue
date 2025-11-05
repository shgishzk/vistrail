<template>
  <div class="space-y-4">
    <div v-if="error" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ error }}
    </div>
    <div v-if="isLoading" class="flex h-96 items-center justify-center">
      <svg class="h-10 w-10 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
      </svg>
    </div>
    <div v-show="!isLoading" ref="mapContainer" class="h-[600px] w-full rounded-lg shadow" />
    <div v-show="!isLoading" class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
      <div class="flex items-center gap-2">
        <span
          class="inline-flex h-3 w-3 rounded-full border border-current"
          :style="{ backgroundColor: legendStyles.hasLock.background, borderColor: legendStyles.hasLock.borderColor }"
        ></span>
        <span>オートロックのマンション</span>
      </div>
      <div class="flex items-center gap-2">
        <span
          class="inline-flex h-3 w-3 rounded-full border border-current"
          :style="{ backgroundColor: legendStyles.noLock.background, borderColor: legendStyles.noLock.borderColor }"
        ></span>
        <span>出入り可能なマンション</span>
      </div>
    </div>
    <section v-show="!isLoading" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5 text-sm shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-900">マンション名で検索</h2>
      </div>
      <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="handleSearch">
        <label for="building-search" class="sr-only">マンション名を入力</label>
        <input
          id="building-search"
          v-model="searchTerm"
          type="search"
          class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200"
          placeholder="マンション名を入力"
        />
        <button
          type="submit"
          :disabled="searchLoading"
          class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-indigo-400"
        >
          <svg v-if="searchLoading" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          検索
        </button>
      </form>
      <p v-if="searchError" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-red-600">
        {{ searchError }}
      </p>
      <div v-if="!searchLoading && searchPerformed && searchResults.length === 0" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-slate-500">
        該当するマンションは見つかりませんでした。
      </div>
      <ul v-if="searchResults.length" class="space-y-3">
        <li
          v-for="building in searchResults"
          :key="building.id"
          class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-900">{{ building.name }}</p>
              <p v-if="building.last_visit_date" class="text-xs text-slate-500">最終訪問日: {{ building.last_visit_date }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <RouterLink
                :to="`/buildings/${building.id}`"
                class="inline-flex items-center justify-center rounded-full border border-indigo-200 bg-white px-3 py-1 text-xs font-medium text-indigo-600 transition hover:border-indigo-400 hover:text-indigo-700"
              >
                詳細を見る
              </RouterLink>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1"
                @click="focusOnBuilding(building)"
              >
                地図で表示
              </button>
            </div>
          </div>
        </li>
      </ul>
    </section>
  </div>
</template>

<script>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { loadGoogleMaps } from '../utils/googleMapsLoader';

export default {
  name: 'BuildingsView',
  setup() {
    const mapContainer = ref(null);
    const isLoading = ref(true);
    const error = ref(null);

    let mapInstance = null;
    let infoWindow = null;
    let markers = [];
    let markerLibrary = null;
    let abortController = null;
    let pendingFetchTimeout = null;
    let lastFetchCenter = null;
    let markerStyles = {};
    let fallbackMarkerStyle = {
      background: '#607D8B',
      borderColor: '#455A64',
      glyphColor: '#455A64',
    };
    const legendStyles = reactive({
      hasLock: {
        background: fallbackMarkerStyle.background,
        borderColor: fallbackMarkerStyle.borderColor,
      },
      noLock: {
        background: fallbackMarkerStyle.background,
        borderColor: fallbackMarkerStyle.borderColor,
      },
    });

    const applyMarkerStyles = (styles = {}) => {
      markerStyles = styles || {};
      fallbackMarkerStyle = {
        background: markerStyles.default?.background || '#607D8B',
        borderColor: markerStyles.default?.borderColor || '#455A64',
        glyphColor: markerStyles.default?.glyphColor || markerStyles.default?.borderColor || '#455A64',
      };
      Object.assign(legendStyles.hasLock, {
        background: markerStyles.has_lock?.background || fallbackMarkerStyle.background,
        borderColor: markerStyles.has_lock?.borderColor || fallbackMarkerStyle.borderColor,
      });
      Object.assign(legendStyles.noLock, {
        background: markerStyles.no_lock?.background || fallbackMarkerStyle.background,
        borderColor: markerStyles.no_lock?.borderColor || fallbackMarkerStyle.borderColor,
      });
    };

    const clearMarkers = () => {
      markers.forEach(marker => {
        marker.map = null;
      });
      markers = [];
    };

    const getMarkerStyle = (type) => {
      const style = markerStyles?.[type] || markerStyles?.default || {};
      return {
        background: style.background || fallbackMarkerStyle.background,
        borderColor: style.borderColor || fallbackMarkerStyle.borderColor,
        glyphColor: style.glyphColor || fallbackMarkerStyle.glyphColor || fallbackMarkerStyle.borderColor,
      };
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

    const createInfoWindowContent = (building) => {
      const detailUrl = building.detail_url || `/buildings/${building.id}`;
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

    const renderMarkers = (buildings) => {
      if (!markerLibrary || !mapInstance) {
        return;
      }

      clearMarkers();

      buildings.forEach((building) => {
        if (typeof building.lat !== 'number' || typeof building.lng !== 'number') {
          return;
        }
        const position = { lat: building.lat, lng: building.lng };
        const style = getMarkerStyle(building.self_lock_type);

        const pin = new markerLibrary.PinElement({
          background: style.background,
          borderColor: style.borderColor,
          glyphColor: style.glyphColor,
        });

        const marker = new markerLibrary.AdvancedMarkerElement({
          map: mapInstance,
          position,
          title: building.name,
          content: pin.element,
        });
        marker.__buildingId = building.id;

        marker.addListener('click', () => {
          if (!infoWindow) {
            infoWindow = new google.maps.InfoWindow({
              disableAutoPan: true,
              shouldFocus: false,
            });
          }
          infoWindow.setContent(createInfoWindowContent(building));
          infoWindow.open({ anchor: marker, map: mapInstance });
        });

        markers.push(marker);
      });
    };

    const fetchBuildings = async (lat, lng) => {
      try {
        if (abortController) {
          abortController.abort('cancelled');
        }
        abortController = new AbortController();

        const { data } = await axios.get('/api/buildings', {
          params: { lat, lng },
          signal: abortController.signal,
        });

        error.value = null;
        renderMarkers(data.buildings || []);
      } catch (err) {
        const isCancelled = err?.name === 'CanceledError' || err?.message === 'canceled' || err?.message === 'cancelled' || err?.code === 'ERR_CANCELED';
        if (isCancelled) {
          return;
        }
        console.error(err);
        error.value = 'マンション情報の取得に失敗しました。時間をおいて再度お試しください。';
      } finally {
        abortController = null;
      }
    };

    const scheduleFetch = () => {
      if (!mapInstance) {
        return;
      }
      const center = mapInstance.getCenter();
      if (!center) {
        return;
      }
      const lat = center.lat();
      const lng = center.lng();

      if (lastFetchCenter && Math.abs(lastFetchCenter.lat - lat) < 1e-5 && Math.abs(lastFetchCenter.lng - lng) < 1e-5) {
        return;
      }
      lastFetchCenter = { lat, lng };

      if (pendingFetchTimeout) {
        clearTimeout(pendingFetchTimeout);
        pendingFetchTimeout = null;
      }

      pendingFetchTimeout = setTimeout(() => {
        pendingFetchTimeout = null;
        fetchBuildings(lat, lng);
      }, 250);
    };

    const searchTerm = ref('');
    const searchResults = ref([]);
    const searchLoading = ref(false);
    const searchError = ref('');
    const searchPerformed = ref(false);

    watch(searchTerm, () => {
      if (searchError.value) {
        searchError.value = '';
      }
    });

    const handleSearch = async () => {
      const keyword = searchTerm.value.trim();

      if (!keyword) {
        searchError.value = '検索キーワードを入力してください。';
        searchResults.value = [];
        searchPerformed.value = false;
        return;
      }

      searchLoading.value = true;
      searchError.value = '';

      try {
        const { data } = await axios.get('/api/buildings/search', {
          params: { q: keyword },
        });
        searchResults.value = Array.isArray(data.buildings) ? data.buildings : [];
        searchPerformed.value = true;
      } catch (err) {
        if (err?.response?.status === 422) {
          searchError.value = '検索キーワードは1文字以上で入力してください。';
        } else {
          console.error(err);
          searchError.value = 'マンションの検索に失敗しました。時間をおいて再度お試しください。';
        }
        searchResults.value = [];
        searchPerformed.value = false;
      } finally {
        searchLoading.value = false;
      }
    };

    const focusOnBuilding = (building) => {
      if (!mapInstance || !building) {
        return;
      }

      const lat = Number(building.lat);
      const lng = Number(building.lng);

      if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return;
      }

      const position = { lat, lng };

      mapInstance.panTo(position);
      if (mapInstance.getZoom() < 17) {
        mapInstance.setZoom(17);
      }

      const marker = markers.find((item) => item.__buildingId === building.id);
      if (!infoWindow) {
        infoWindow = new google.maps.InfoWindow({
          disableAutoPan: true,
          shouldFocus: false,
        });
      }
      infoWindow.setContent(createInfoWindowContent(building));
      if (marker) {
        infoWindow.open({ anchor: marker, map: mapInstance });
      } else {
        infoWindow.open({ position, map: mapInstance });
      }
    };

    const initialiseMap = async () => {
      try {
        const { data: config } = await axios.get('/api/map/config');

        applyMarkerStyles(config.marker_styles);

        const defaultPosition = {
          lat: Number(config.default_position?.lat) || 35.0238868,
          lng: Number(config.default_position?.lng) || 135.760201,
        };

        if (!config.maps_api_key) {
          throw new Error('Google Maps APIキーが設定されていません。');
        }

        await loadGoogleMaps(config.maps_api_key);
        await google.maps.importLibrary('maps');
        markerLibrary = await google.maps.importLibrary('marker');

        error.value = null;
        mapInstance = new google.maps.Map(mapContainer.value, {
          center: defaultPosition,
          zoom: 17,
          zoomControl: true,
          mapId: 'buildings-map',
        });

        await fetchBuildings(defaultPosition.lat, defaultPosition.lng);
        lastFetchCenter = { ...defaultPosition };

        google.maps.event.addListener(mapInstance, 'idle', scheduleFetch);
      } catch (err) {
        console.error(err);
        error.value = '地図の初期化に失敗しました。時間をおいて再度お試しください。';
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      initialiseMap();
    });

    onBeforeUnmount(() => {
      clearMarkers();
      if (pendingFetchTimeout) {
        clearTimeout(pendingFetchTimeout);
      }
      if (abortController) {
        abortController.abort('component-unmounted');
        abortController = null;
      }
      if (mapInstance && window.google && window.google.maps) {
        google.maps.event.clearInstanceListeners(mapInstance);
      }
    });

    return {
      mapContainer,
      isLoading,
      error,
      legendStyles,
      searchTerm,
      searchResults,
      searchLoading,
      searchError,
      searchPerformed,
      handleSearch,
      focusOnBuilding,
    };
  },
};
</script>
