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
  </div>
</template>

<script>
import { onBeforeUnmount, onMounted, ref } from 'vue';
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

    const applyMarkerStyles = (styles = {}) => {
      markerStyles = styles || {};
      fallbackMarkerStyle = {
        background: markerStyles.default?.background || '#607D8B',
        borderColor: markerStyles.default?.borderColor || '#455A64',
        glyphColor: markerStyles.default?.glyphColor || markerStyles.default?.borderColor || '#455A64',
      };
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

        marker.addListener('click', () => {
          if (!infoWindow) {
            infoWindow = new google.maps.InfoWindow();
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
          zoom: 14,
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
    };
  },
};
</script>
