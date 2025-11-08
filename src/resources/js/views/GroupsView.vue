<template>
  <div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-semibold text-slate-900">グループマップ</h1>
          <p class="text-sm text-slate-500">グループを選択するとそのグループに紐づいたマンションだけが表示されます。</p>
        </div>
        <div ref="groupDropdownRef" class="relative inline-flex">
          <button
            type="button"
            class="inline-flex min-w-[12rem] items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-70"
            :disabled="groupLoading"
            aria-haspopup="menu"
            :aria-expanded="groupDropdownOpen"
            aria-controls="group-dropdown-menu"
            @click="toggleGroupDropdown"
          >
            <span>{{ selectedGroupLabel }}</span>
            <ChevronDown class="h-4 w-4 text-slate-400" />
          </button>
          <transition
            enter-active-class="duration-150 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="duration-100 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="groupDropdownOpen"
              id="group-dropdown-menu"
              class="absolute right-0 z-20 mt-2 min-w-[12rem] rounded-xl border border-slate-200 bg-white p-1 text-sm shadow-lg"
              role="menu"
            >
              <button
                type="button"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none"
                @click="selectGroup(null)"
              >
                <span>---</span>
                <Check v-if="selectedGroupId === null" class="h-4 w-4 text-indigo-500" />
              </button>
              <div v-if="groups.length" class="my-1 border-t border-slate-100"></div>
              <button
                v-for="group in groups"
                :key="group.id"
                type="button"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none"
                @click="selectGroup(group.id)"
              >
                <span>{{ group.name }}</span>
                <Check v-if="selectedGroupId === group.id" class="h-4 w-4 text-indigo-500" />
              </button>
            </div>
          </transition>
        </div>
      </div>
    </section>

    <div v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
      {{ error }}
    </div>
    <div v-else>
      <div class="relative">
        <div
          v-if="mapLoading || markerLoading"
          class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-sm"
        >
          <svg class="h-10 w-10 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div ref="mapContainer" class="h-[600px] w-full rounded-lg shadow"></div>
      </div>
      <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-600">
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
    </div>
  </div>
</template>

<script>
import { onBeforeUnmount, onMounted, reactive, ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ChevronDown, Check } from 'lucide-vue-next';
import { loadGoogleMaps } from '../utils/googleMapsLoader';
import { parseKmlPolygonCoordinates } from '../utils/kml';

export default {
  name: 'GroupsView',
  components: {
    ChevronDown,
    Check,
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const mapContainer = ref(null);
    const groupDropdownRef = ref(null);
    const groupDropdownOpen = ref(false);
    const mapLoading = ref(true);
    const markerLoading = ref(false);
    const groupLoading = ref(true);
    const error = ref('');
    const groups = ref([]);
    const parseGroupId = (value) => {
      if (value === undefined || value === null || value === '') {
        return null;
      }

      const number = Number(value);
      return Number.isNaN(number) ? null : number;
    };
    const selectedGroupId = ref(parseGroupId(route.params.groupId));
    const selectedGroupLabel = computed(() => {
      if (selectedGroupId.value === null) {
        return '---';
      }
      const group = groups.value.find((item) => item.id === selectedGroupId.value);
      return group?.name ?? '---';
    });

    let mapInstance = null;
    let infoWindow = null;
    let markerLibrary = null;
    let markers = [];
    let markerStyles = {};
    let fallbackMarkerStyle = {
      background: '#607D8B',
      borderColor: '#455A64',
      glyphColor: '#455A64',
    };
    let territoryPolygon = null;
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
    let defaultPosition = { lat: 35.0238868, lng: 135.760201 };
    let buildingsAbortController = null;

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
      markers.forEach((marker) => {
        marker.map = null;
      });
      markers = [];
    };

    const renderTerritoryBoundary = (kmlString) => {
      if (territoryPolygon) {
        territoryPolygon.setMap(null);
        territoryPolygon = null;
      }

      if (!mapInstance || typeof window === 'undefined' || !window.google?.maps) {
        return;
      }

      const coordinates = parseKmlPolygonCoordinates(kmlString);

      if (!coordinates.length) {
        return;
      }

      territoryPolygon = new google.maps.Polygon({
        paths: coordinates,
        strokeColor: '#d81b60',
        strokeOpacity: 0.9,
        strokeWeight: 2,
        fillColor: '#f8bbd0',
        fillOpacity: 0.15,
        geodesic: true,
        clickable: false,
      });

      territoryPolygon.setMap(mapInstance);
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

    const formatMemo = (value) => {
      if (!value) {
        return '―';
      }
      return escapeHtml(value).replace(/\n/g, '<br>');
    };

    const createInfoWindowContent = (building) => {
      const detailUrl = appendQueryParams(building.detail_url || `/buildings/${building.id}`, {
        from: 'groups',
        groupId: selectedGroupId.value ?? undefined,
      });
      const safeName = escapeHtml(building.name || 'マンション');
      const lastVisit = building.last_visit_date ? escapeHtml(building.last_visit_date) : '―';
      const visitRate = formatVisitRate(building.visit_rate);
      const memo = formatMemo(building.memo);
      const groupName = building.group_name ? escapeHtml(building.group_name) : '―';

      return `
        <div class="min-w-[220px] max-w-xs space-y-2">
          <a href="${detailUrl}" class="block text-base font-semibold text-indigo-600 hover:text-indigo-500 hover:underline" target="_self">
            ${safeName}
          </a>
          <div class="space-y-1 text-sm text-slate-600">
            <div><span class="font-medium text-slate-700">グループ:</span> ${groupName}</div>
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

      const bounds = new google.maps.LatLngBounds();
      let hasBounds = false;

      buildings.forEach((building) => {
        if (typeof building.lat !== 'number' || typeof building.lng !== 'number') {
          return;
        }
        const position = { lat: building.lat, lng: building.lng };
        const style = getMarkerStyle(building.self_lock_type);

        const pinOptions = {
          background: style.background,
          borderColor: style.borderColor,
          glyphColor: '#000000',
        };

        if (building.group_initial) {
          pinOptions.glyph = building.group_initial;
        }

        const pin = new markerLibrary.PinElement(pinOptions);
        const glyphElement = pin.element?.querySelector('span');
        if (glyphElement) {
          glyphElement.style.fontWeight = '900';
          glyphElement.style.color = '#000000';
        }

        const marker = new markerLibrary.AdvancedMarkerElement({
          map: mapInstance,
          position,
          title: building.name,
          content: pin.element,
        });
        marker.__building = building;
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
        bounds.extend(position);
        hasBounds = true;
      });

      if (hasBounds) {
        if (markers.length === 1) {
          const marker = markers[0];
          mapInstance.setZoom(17);
          mapInstance.panTo(marker.position);
        } else {
          mapInstance.fitBounds(bounds, 60);
        }
      } else {
        mapInstance.setCenter(defaultPosition);
        mapInstance.setZoom(14);
      }
    };

    const fetchGroupBuildings = async (groupId = null) => {
      if (!mapInstance) {
        return;
      }

      if (buildingsAbortController) {
        buildingsAbortController.abort('cancelled');
      }
      buildingsAbortController = new AbortController();

      markerLoading.value = true;
      error.value = '';

      try {
        const params = {};
        if (groupId !== null) {
          params.group_id = groupId;
        }

        const { data } = await axios.get('/api/groups/buildings', {
          params,
          signal: buildingsAbortController.signal,
        });

        renderMarkers(data.buildings || []);
      } catch (err) {
        const isCancelled =
          err?.name === 'CanceledError' ||
          err?.message === 'canceled' ||
          err?.message === 'cancelled' ||
          err?.code === 'ERR_CANCELED';

        if (isCancelled) {
          return;
        }

        console.error(err);
        error.value = 'マンション情報の取得に失敗しました。時間をおいて再度お試しください。';
      } finally {
        markerLoading.value = false;
        buildingsAbortController = null;
      }
    };

    const fetchGroups = async () => {
      groupLoading.value = true;
      try {
        const { data } = await axios.get('/api/groups');
        groups.value = (data.groups || []).map((group) => ({
          ...group,
          id: Number(group.id),
        }));
        if (selectedGroupId.value !== null) {
          const exists = groups.value.some((group) => group.id === selectedGroupId.value);
          if (!exists) {
            selectGroup(null);
          }
        }
      } catch (err) {
        console.error(err);
        error.value = 'グループ情報の取得に失敗しました。時間をおいて再度お試しください。';
      } finally {
        groupLoading.value = false;
      }
    };

    const initialiseMap = async () => {
      try {
        const { data: config } = await axios.get('/api/map/config');

        applyMarkerStyles(config.marker_styles);

        defaultPosition = {
          lat: Number(config.default_position?.lat) || defaultPosition.lat,
          lng: Number(config.default_position?.lng) || defaultPosition.lng,
        };

        if (!config.maps_api_key) {
          throw new Error('Google Maps APIキーが設定されていません。');
        }

        await loadGoogleMaps(config.maps_api_key);
        await google.maps.importLibrary('maps');
        markerLibrary = await google.maps.importLibrary('marker');

        mapInstance = new google.maps.Map(mapContainer.value, {
          center: defaultPosition,
          zoom: 14,
          zoomControl: true,
          mapId: 'groups-map',
          fullscreenControl: false,
        });

        renderTerritoryBoundary(config.assigned_boundary_kml);
      } catch (err) {
        console.error(err);
        error.value = '地図の初期化に失敗しました。時間をおいて再度お試しください。';
      } finally {
        mapLoading.value = false;
      }
    };

    const selectGroup = (groupId, { skipNavigation } = {}) => {
      const normalized = parseGroupId(groupId);
      if (selectedGroupId.value === normalized) {
        groupDropdownOpen.value = false;
        return;
      }
      selectedGroupId.value = normalized;
      groupDropdownOpen.value = false;
      fetchGroupBuildings(selectedGroupId.value);

      if (!skipNavigation) {
        const params = normalized === null ? {} : { groupId: normalized };
        const query = { ...route.query };
        router.push({ name: 'groups', params, query }).catch(() => {});
      }
    };

    const toggleGroupDropdown = (event) => {
      if (groupLoading.value) {
        return;
      }
      groupDropdownOpen.value = !groupDropdownOpen.value;
      event?.stopPropagation();
    };

    const handleClickOutside = (event) => {
      if (!groupDropdownRef.value) {
        return;
      }
      if (!groupDropdownRef.value.contains(event.target)) {
        groupDropdownOpen.value = false;
      }
    };

    onMounted(async () => {
      if (typeof document !== 'undefined') {
        document.addEventListener('click', handleClickOutside);
      }
      await initialiseMap();
      await fetchGroups();
      if (!error.value) {
        await fetchGroupBuildings(selectedGroupId.value);
      }
    });

    watch(
      () => route.params.groupId,
      (value) => {
        const normalized = parseGroupId(value);
        if (selectedGroupId.value !== normalized) {
          selectedGroupId.value = normalized;
          fetchGroupBuildings(selectedGroupId.value);
        }
      }
    );

    onBeforeUnmount(() => {
      if (typeof document !== 'undefined') {
        document.removeEventListener('click', handleClickOutside);
      }
      if (buildingsAbortController) {
        buildingsAbortController.abort('component-unmounted');
      }
      clearMarkers();
      if (mapInstance && window.google && window.google.maps) {
        google.maps.event.clearInstanceListeners(mapInstance);
      }
      if (territoryPolygon) {
        territoryPolygon.setMap(null);
        territoryPolygon = null;
      }
    });

    return {
      mapContainer,
      groupDropdownRef,
      groupDropdownOpen,
      mapLoading,
      markerLoading,
      groupLoading,
      groups,
      selectedGroupId,
      selectedGroupLabel,
      legendStyles,
      error,
      selectGroup,
      toggleGroupDropdown,
    };
  },
};
</script>
