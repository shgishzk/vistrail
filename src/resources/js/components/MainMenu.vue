<template>
  <div class="space-y-7 lg:space-y-10">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-500 text-white shadow-2xl">
      <div class="absolute inset-0 opacity-30 mix-blend-overlay">
        <div class="absolute -top-24 -left-16 h-72 w-72 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute -bottom-28 right-0 h-72 w-72 rounded-full bg-white/30 blur-3xl"></div>
      </div>
      <div class="relative px-6 py-10 sm:px-10 lg:px-14 lg:py-14">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div class="space-y-4">
            <p class="text-sm uppercase tracking-[0.35em] text-indigo-100/90">Welcome Back</p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
              {{ brandLabel }}
            </h1>
            <p class="max-w-2xl text-base text-indigo-50/90">
              区域・マンション・グループの情報へ素早くアクセスし、最新のお知らせをいつでも確認できます。
            </p>
          </div>
          <RouterLink
            to="/buildings"
            class="inline-flex items-center justify-center rounded-full bg-white/10 px-6 py-3 text-sm font-semibold shadow-lg shadow-black/10 backdrop-blur transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/80 focus:ring-offset-2 focus:ring-offset-indigo-600"
          >
            <span class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
              <Compass class="h-5 w-5" />
            </span>
            すぐにマンションマップへ移動
          </RouterLink>
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-3 lg:gap-8">
      <div class="lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white/70 shadow-sm backdrop-blur">
          <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-slate-900">最新のお知らせ</h2>
            <p class="text-sm text-slate-500">会衆からの最新情報とお知らせです。</p>
          </div>
          <div class="px-6 py-5">
            <div v-if="newsState.isLoading" class="space-y-3">
              <div class="h-4 w-1/3 animate-pulse rounded bg-slate-200"></div>
              <div class="space-y-2">
                <div v-for="n in 3" :key="n" class="h-3 w-full animate-pulse rounded bg-slate-200"></div>
              </div>
            </div>
            <div v-else-if="newsState.error" class="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600">
              {{ newsState.error }}
            </div>
            <div v-else>
              <ul v-if="newsItems.length" class="divide-y divide-slate-200">
                <li
                  v-for="item in newsItems"
                  :key="item.id"
                  class="py-4 first:pt-0 last:pb-0"
                >
                  <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <h3 v-if="item.title" class="text-base font-semibold text-slate-900">
                        {{ item.title }}
                      </h3>
                      <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        {{ item.excerpt }}
                      </p>
                    </div>
                    <time class="shrink-0 text-xs font-medium uppercase tracking-wide text-slate-400">
                      {{ item.formattedDate }}
                    </time>
                  </div>
                </li>
              </ul>
              <div v-else class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-5 py-6 text-center text-sm text-slate-500">
                公開中のお知らせはまだありません。
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white/70 shadow-sm backdrop-blur">
          <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-base font-semibold text-slate-900">クイックアクセス</h2>
            <p class="mt-1 text-xs text-slate-500">よく使う機能へすばやく移動できます。</p>
          </div>
          <div class="grid gap-3 px-6 py-5">
            <RouterLink
              v-for="item in menuItems"
              :key="item.to"
              :to="item.to"
              class="group flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-indigo-400 hover:bg-indigo-50/80 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
              <span class="inline-flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                  <component :is="item.icon" class="h-4 w-4" />
                </span>
                {{ item.label }}
              </span>
              <ChevronRight class="h-4 w-4 text-slate-300 transition group-hover:text-indigo-400" />
            </RouterLink>
          </div>
        </div>

        <div class="rounded-2xl border border-indigo-200 bg-indigo-50/70 px-6 py-6 text-sm text-indigo-900 shadow-sm backdrop-blur">
          <h3 class="text-base font-semibold">ヒント</h3>
          <p class="mt-2 leading-relaxed">
            Google マップを利用したマンションマップでは、中心位置から半径{{ mapRadiusKmText }}km以内のピンが表示されます。地図をゆっくり移動して周辺情報を確認しましょう。
          </p>
          <RouterLink
            to="/buildings"
            class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-indigo-700 hover:text-indigo-600"
          >
            <span>マンションマップを開く</span>
            <ArrowRight class="h-4 w-4" />
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { RouterLink } from 'vue-router';
import axios from 'axios';
import { computed, onMounted, reactive } from 'vue';
import { FolderOpen, Building, Layers, Compass, ArrowRight, ChevronRight } from 'lucide-vue-next';

export default {
  name: 'MainMenu',
  components: { RouterLink, FolderOpen, Building, Layers, Compass, ArrowRight, ChevronRight },
  setup() {
    const menuItems = [
      { label: '区域', to: '/areas', icon: FolderOpen },
      { label: 'マンション', to: '/buildings', icon: Building },
      { label: 'グループ', to: '/groups', icon: Layers },
    ];

    const newsState = reactive({
      isLoading: true,
      items: [],
      error: '',
    });

    const mapRadiusRaw = Number.parseFloat(import.meta.env.VITE_BUILDING_MAP_HALF_SIDE_KM ?? '1.0');
    const mapRadiusKmText = Number.isFinite(mapRadiusRaw)
      ? (Number.isInteger(mapRadiusRaw) ? mapRadiusRaw.toFixed(0) : mapRadiusRaw.toFixed(1))
      : '1.0';

    const brandLabel = computed(() => {
      const prefix = import.meta.env.VITE_APP_BRAND_PREFIX || '';
      const base = 'オンライン区域ダッシュボード';
      return prefix ? `${prefix} ${base}` : base;
    });

    const newsItems = computed(() => newsState.items);

    const loadNews = async () => {
      try {
        const { data } = await axios.get('/api/news', { params: { limit: 5 } });
        newsState.items = (data.news || []).map((item) => ({
          ...item,
          title: item.title,
          formattedDate: formatDate(item.created_at || item.updated_at),
          excerpt: buildExcerpt(item.content),
        }));
        newsState.error = '';
      } catch (error) {
        console.error(error);
        newsState.error = 'お知らせの取得に失敗しました。時間をおいて再度お試しください。';
        newsState.items = [];
      } finally {
        newsState.isLoading = false;
      }
    };

    const formatDate = (value) => {
      if (!value) {
        return '';
      }
      try {
        const date = new Date(value);
        return new Intl.DateTimeFormat('ja-JP', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit',
        }).format(date);
      } catch {
        return value;
      }
    };

    const buildExcerpt = (content) => {
      if (!content) {
        return '';
      }
      const plain = content.replace(/<[^>]+>/g, '').replace(/\\s+/g, ' ').trim();
      return plain.length > 80 ? `${plain.slice(0, 80)}…` : plain;
    };

    onMounted(() => {
      loadNews();
    });

    return {
      menuItems,
      brandLabel,
      newsState,
      newsItems,
      mapRadiusKmText,
    };
  },
};
</script>
