<template>
  <div class="mx-auto max-w-4xl space-y-6">
    <RouterLink
      to="/buildings"
      class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-500"
    >
      ← マンションマップへ戻る
    </RouterLink>

    <div class="rounded-3xl bg-gradient-to-r from-indigo-500 via-indigo-400 to-purple-500 px-6 py-10 text-white shadow-xl">
      <div class="space-y-3">
        <p class="text-xs uppercase tracking-[0.35em] text-indigo-100">Building Detail</p>
        <div class="flex flex-wrap items-center gap-3">
          <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
            {{ building?.name || 'マンション詳細' }}
          </h1>
          <span
            v-if="isAutoLock"
            class="inline-flex items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white backdrop-blur"
          >
            オートロック
          </span>
        </div>
        <p v-if="building?.memo" class="max-w-2xl text-sm leading-relaxed text-indigo-50/90">
          {{ building.memo }}
        </p>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,_2fr)_minmax(0,_1.2fr)]">
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="grid gap-6 px-6 py-6 sm:grid-cols-2">
          <div class="space-y-2">
            <h2 class="text-sm font-semibold text-slate-500">最終訪問日</h2>
            <p class="text-lg font-semibold text-slate-900">
              {{ building?.last_visit_date || '―' }}
            </p>
          </div>
          <div class="space-y-2">
            <h2 class="text-sm font-semibold text-slate-500">1年間の訪問率</h2>
            <p class="text-lg font-semibold text-slate-900">
              {{ formatVisitRate(building?.visit_rate_year) }}
            </p>
          </div>
          <div class="space-y-2">
            <h2 class="text-sm font-semibold text-slate-500">登録部屋数</h2>
            <p class="text-lg font-semibold text-slate-900">
              {{ building?.total_rooms ?? '―' }}
            </p>
          </div>
          <div class="space-y-2">
            <h2 class="text-sm font-semibold text-slate-500">1年以内に訪問した部屋</h2>
            <p class="text-lg font-semibold text-slate-900">
              {{ building?.recent_rooms_count ?? 0 }}
            </p>
          </div>
        </div>
      </div>
      <div v-if="building?.memo" class="rounded-2xl border border-indigo-200 bg-indigo-50 px-6 py-4 text-sm text-indigo-900 shadow-sm">
        <p class="font-semibold text-indigo-700">メモ</p>
        <p class="mt-2 whitespace-pre-wrap leading-relaxed text-indigo-900/90">
          {{ building.memo }}
        </p>
      </div>
    </div>

    <div v-if="loading" class="flex items-center justify-center py-10 text-slate-500">
      <svg class="h-10 w-10 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      {{ error }}
    </div>
    <div v-else>
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
          <h2 class="text-base font-semibold text-slate-900">部屋一覧</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
              <tr>
                <th class="px-6 py-3">部屋番号</th>
                <th class="px-6 py-3">注意</th>
                <th class="px-6 py-3">ステータス</th>
                <th class="px-6 py-3">最終更新日時</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="room in building?.rooms ?? []" :key="room.id">
                <td class="px-6 py-3 font-medium text-slate-900">{{ room.number }}</td>
                <td class="px-6 py-3">
                  <div class="flex items-center">
                    <template v-if="room.alert">
                      <div class="hs-tooltip inline-flex [--placement:top]">
                        <button
                          type="button"
                          class="hs-tooltip-toggle inline-flex h-8 w-8 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                          :class="alertButtonClass(room.alert)"
                          :aria-label="room.alert.message"
                        >
                          <component :is="alertIcon(room.alert.icon)" class="h-4 w-4" />
                        </button>
                        <div
                          class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible hidden w-64 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 opacity-0 shadow-xl transition-[opacity,transform] duration-200"
                          role="tooltip"
                        >
                          {{ room.alert.message }}
                        </div>
                      </div>
                    </template>
                    <span v-else class="text-slate-400">―</span>
                  </div>
                </td>
                <td class="px-6 py-3">
                  <template v-if="room.selectable">
                    <select
                      class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 disabled:bg-slate-50"
                      :value="room.status"
                      @change="(event) => handleStatusChange(room, event.target.value)"
                      :disabled="room.updating"
                    >
                      <option v-for="(label, value) in selectableStatuses" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                  </template>
                  <template v-else>
                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600">
                      {{ statuses[room.status] || room.status }}
                    </span>
                  </template>
                </td>
                <td class="px-6 py-3 text-slate-600">
                  {{ room.updated_at ? formatDateTime(room.updated_at) : '―' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, nextTick, computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from '../utils/toast';
import { AlertCircle, AlertTriangle } from 'lucide-vue-next';

export default {
  name: 'BuildingDetailView',
  components: {
    RouterLink,
    AlertCircle,
    AlertTriangle,
  },
  setup() {
    const route = useRoute();
    const loading = ref(true);
    const error = ref('');
    const building = ref(null);
    const statuses = ref({});
    const selectableStatuses = ref({});

    const fetchBuilding = async () => {
      loading.value = true;
      error.value = '';
      try {
        const { data } = await axios.get(`/api/buildings/${route.params.id}`);
        building.value = {
          ...data.building,
          rooms: (data.building.rooms || []).map((room) => ({
            ...room,
            updating: false,
          })),
        };
        statuses.value = data.statuses || {};
        selectableStatuses.value = data.selectable_statuses || {};
        await nextTick();
        window.HSStaticMethods?.autoInit?.('tooltip');
      } catch (err) {
        console.error(err);
        error.value = 'マンション情報の取得に失敗しました。';
      } finally {
        loading.value = false;
      }
    };

    const formatVisitRate = (value) => {
      if (typeof value !== 'number' || Number.isNaN(value)) {
        return '―';
      }
      return `${value.toFixed(1)}%`;
    };

    const formatDateTime = (value) => {
      if (!value) {
        return '―';
      }
      try {
        return new Intl.DateTimeFormat('ja-JP', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
        }).format(new Date(value));
      } catch {
        return value;
      }
    };

    const updateRoomInState = (updatedRoom) => {
      if (!building.value?.rooms) {
        return;
      }

      building.value.rooms = building.value.rooms.map((room) =>
        room.id === updatedRoom.id
          ? {
              ...room,
              ...updatedRoom,
              status_label: updatedRoom.status_label ?? room.status_label,
              updating: false,
            }
          : room,
      );
    };

    const handleStatusChange = async (room, newStatus) => {
      const originalStatus = room.status;
      const originalAlert = room.alert;
      room.status = newStatus;
      room.updating = true;

      try {
        const { data } = await axios.patch(`/api/buildings/${route.params.id}/rooms/${room.id}`, {
          status: newStatus,
        });

        updateRoomInState({
          id: room.id,
          status: data.room.status,
          status_label: data.room.status_label,
          updated_at: data.room.updated_at,
          alert: data.room.alert,
        });
        await nextTick();
        window.HSStaticMethods?.autoInit?.('tooltip');
        toast.success('ステータスを更新しました。');
      } catch (err) {
        console.error(err);
        room.status = originalStatus;
        room.alert = originalAlert;
        toast.error('ステータスの更新に失敗しました。');
      } finally {
        room.updating = false;
      }
    };

    onMounted(() => {
      fetchBuilding();
    });

    const alertButtonClass = (alert) => {
      if (!alert) {
        return 'bg-slate-100 text-slate-500';
      }

      if (alert.type === 'danger') {
        return 'bg-red-50 text-red-500 hover:bg-red-100';
      }

      if (alert.type === 'warning') {
        return 'bg-amber-50 text-amber-600 hover:bg-amber-100';
      }

      return 'bg-slate-100 text-slate-500';
    };

    const iconMap = {
      'alert-circle': AlertCircle,
      'alert-triangle': AlertTriangle,
    };

    const alertIcon = (icon) => iconMap[icon] || AlertCircle;

    const isAutoLock = computed(() => building.value?.self_lock_type === 'has_lock');

    return {
      building,
      loading,
      error,
      formatVisitRate,
      formatDateTime,
      statuses,
      selectableStatuses,
      handleStatusChange,
      alertButtonClass,
      alertIcon,
      isAutoLock,
    };
  },
};
</script>
