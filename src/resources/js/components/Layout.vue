<template>
  <div class="min-h-screen bg-gray-100">
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
      <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
          <RouterLink to="/" class="inline-flex items-center gap-3">
            <span class="flex items-center justify-center rounded-full bg-indigo-600/10 text-base font-semibold text-white shadow-sm">
              <img
                :src="logoUrl"
                alt="オンライン区域"
                class="avatar-logo"
              >
            </span>
            <span class="text-lg font-semibold text-gray-900">{{ brandLabel }}</span>
          </RouterLink>
          <div class="flex items-center gap-3">
            <div
              v-if="user"
              class="hidden min-w-[180px] items-center gap-3 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-left shadow-sm md:flex"
            >
              <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500/15 text-sm font-semibold uppercase text-indigo-600">
                {{ userInitials }}
              </span>
              <div class="leading-tight">
                <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">ログイン中</p>
              </div>
            </div>
            <button
              type="button"
              class="hs-collapse-toggle inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:hidden"
              data-hs-collapse="#app-navbar"
              aria-controls="app-navbar"
              aria-label="Toggle navigation"
            >
              <svg class="h-5 w-5 hs-collapse-open:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16" />
              </svg>
              <svg class="hidden h-5 w-5 hs-collapse-open:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="m6 6 12 12M6 18 18 6" />
              </svg>
            </button>
          </div>
        </div>
        <nav
          id="app-navbar"
          class="hs-collapse hidden h-0 grow basis-full overflow-hidden transition-all duration-300 md:block md:h-auto md:overflow-visible"
        >
          <div class="flex flex-col gap-6 pb-6 md:flex-row md:items-center md:justify-between md:pb-4">
            <ul class="flex flex-col gap-2 text-sm md:flex-row md:items-center md:gap-6">
              <li v-for="item in routeItems" :key="item.name">
                <RouterLink
                  :to="item.to"
                  class="inline-flex items-center gap-2 rounded-lg px-3 py-2 font-medium transition"
                  :class="isActive(item)
                    ? 'bg-indigo-50 text-indigo-600 shadow-sm ring-1 ring-inset ring-indigo-600/20'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                >
                  {{ item.name }}
                </RouterLink>
              </li>
            </ul>
            <div class="flex flex-col gap-4 border-t border-gray-200 pt-4 md:flex-row md:items-center md:gap-4 md:border-t-0 md:pt-0">
              <div v-if="user" class="flex items-center gap-3 md:hidden">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500/15 text-sm font-semibold uppercase text-indigo-600">
                  {{ userInitials }}
                </span>
                <div class="leading-tight">
                  <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">ログイン中</p>
                </div>
              </div>
              <button
                v-for="item in actionItems"
                :key="item.name"
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                @click="handleNavigation(item)"
              >
                {{ item.name }}
              </button>
            </div>
          </div>
        </nav>
      </div>
    </header>
    
    <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="rounded-xl bg-white p-6 shadow-lg shadow-slate-200/60">
        <div
          v-if="user"
          class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-900"
        >
          ようこそ、{{ user.name }}さん
        </div>
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import axios from 'axios';
import { RouterLink, useRoute } from 'vue-router';

export default {
  name: 'Layout',
  components: { RouterLink },
  setup() {
    const user = ref(null);
    const navigation = [
      { name: '区域', to: '/areas', type: 'route' },
      { name: 'マンション', to: '/buildings', type: 'route' },
      { name: 'グループ', to: '/groups', type: 'route' },
      { name: 'ログアウト', to: '/logout', type: 'action' },
    ];
    const route = useRoute();
    const routeItems = computed(() => navigation.filter(item => item.type === 'route'));
    const actionItems = computed(() => navigation.filter(item => item.type === 'action'));
    const userInitials = computed(() => {
      if (!user.value?.name) {
        return 'U';
      }
      const trimmed = user.value.name.trim();
      if (!trimmed) {
        return 'U';
      }
      return trimmed.slice(0, 2).toUpperCase();
    });
    const logoUrl = computed(() => '/images/common/logo.png');
    const brandLabel = computed(() => {
      const prefix = import.meta.env.VITE_APP_BRAND_PREFIX || '';
      const base = 'オンライン区域';
      return prefix ? `${prefix} ${base}` : base;
    });
    
    const fetchUser = async () => {
      try {
        const response = await axios.get('/api/user');
        user.value = response.data.user;
      } catch (error) {
        console.error('Failed to fetch user data:', error);
      }
    };

    const initPreline = () => {
      if (window?.HSStaticMethods?.autoInit) {
        window.HSStaticMethods.autoInit();
      }
    };

    const closeMobileNav = () => {
      const collapseTarget = document.querySelector('#app-navbar');
      if (!collapseTarget) {
        return;
      }
      if (window?.HSCollapse) {
        const instance = window.HSCollapse.getInstance(collapseTarget) || new window.HSCollapse(collapseTarget);
        instance?.hide();
      } else {
        collapseTarget.classList.remove('open');
        collapseTarget.style.height = null;
      }
    };
    
    const isActive = (item) => {
      if (item.type === 'action') {
        return false;
      }
      return route.path === item.to || route.path.startsWith(`${item.to}/`);
    };
    
    watch(() => route.path, async () => {
      closeMobileNav();
      await nextTick();
      initPreline();
    });
    
    const handleNavigation = async (item) => {
      if (item.type === 'action' && item.to === '/logout') {
        await logout();
      }
      closeMobileNav();
    };

    const logout = async () => {
      try {
        await axios.post('/logout');
        window.location.reload();
      } catch (error) {
        console.error('Failed to logout:', error);
      }
    };
    
    onMounted(async () => {
      await fetchUser();
      await nextTick();
      initPreline();
    });
    
    return {
      user,
      logout,
      isActive,
      handleNavigation,
      routeItems,
      actionItems,
      userInitials,
      logoUrl,
      brandLabel,
    };
  }
};
</script>
