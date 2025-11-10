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
              v-if="showReloadButton && !shouldShowDesktop"
              type="button"
              :disabled="reloadInProgress"
              class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
              @click="reloadPage"
            >
              <RefreshCcw :class="['h-4 w-4', { 'animate-spin': reloadInProgress }]" />
              <span>再読み込み</span>
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:hidden"
              @click="toggleMobileNav"
              aria-controls="app-navbar"
              :aria-expanded="isMobileNavOpen"
              aria-label="Toggle navigation"
            >
              <svg
                v-if="!isMobileNavOpen"
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M4 7h16M4 12h16M4 17h16" />
              </svg>
              <svg
                v-else
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="m6 6 12 12M6 18 18 6" />
              </svg>
            </button>
          </div>
        </div>
        <Transition
          enter-active-class="duration-200 ease-out transition-all"
          enter-from-class="-translate-y-2 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="duration-150 ease-in transition-all"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="-translate-y-2 opacity-0"
        >
          <nav
            v-show="shouldShowNav"
            id="app-navbar"
            class="mt-4 pb-3 space-y-6 md:mt-0 md:flex md:items-center md:justify-between md:space-y-0"
          >
            <ul class="flex flex-col gap-2 text-sm md:flex-row md:items-center md:gap-6">
              <li v-for="item in routeItems" :key="item.name">
                <RouterLink
                  :to="item.to"
                  class="inline-flex items-center gap-3 rounded-lg px-3 py-2 font-medium transition"
                  :class="isActive(item)
                    ? 'bg-indigo-50 text-indigo-600 shadow-sm ring-1 ring-inset ring-indigo-600/20'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                >
                  <span class="inline-flex items-center gap-2">
                    <component
                      v-if="item.icon"
                      :is="item.icon"
                      class="h-4 w-4"
                    />
                    <span>{{ item.name }}</span>
                  </span>
                </RouterLink>
              </li>
            </ul>
            <div class="flex flex-col gap-4 border-t border-gray-200 pt-4 md:flex-row md:items-center md:gap-4 md:border-t-0 md:pt-0">
              <div v-if="user && !shouldShowDesktop" class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500/15 text-sm font-semibold uppercase text-indigo-600">
                  {{ userInitials }}
                </span>
                <div class="leading-tight">
                  <p class="text-sm font-semibold text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-500">ログイン中</p>
                </div>
              </div>
              <button
                v-if="showReloadButton && shouldShowDesktop"
                type="button"
                :disabled="reloadInProgress"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
                @click="reloadPage"
              >
                <RefreshCcw :class="['h-4 w-4', { 'animate-spin': reloadInProgress }]" />
                <span>再読み込み</span>
              </button>
              <button
                v-for="item in actionItems"
                :key="item.name"
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                @click="handleNavigation(item)"
              >
                <component
                  v-if="item.icon"
                  :is="item.icon"
                  class="h-4 w-4"
                />
                <span>{{ item.name }}</span>
              </button>
            </div>
          </nav>
        </Transition>
      </div>
    </header>
    
    <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="rounded-xl bg-white p-6 shadow-lg shadow-slate-200/60">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch, computed, nextTick } from 'vue';
import axios from 'axios';
import { RouterLink, useRoute } from 'vue-router';
import { FolderOpen, Building, Layers, LogOut, RefreshCcw } from 'lucide-vue-next';

export default {
  name: 'Layout',
  components: { RouterLink, RefreshCcw },
  setup() {
    const user = ref(null);
    const isMobileNavOpen = ref(false);
    const isDesktop = ref(false);
    const navigation = [
      { name: '区域', to: '/areas', type: 'route', icon: FolderOpen },
      { name: 'マンション', to: '/buildings', type: 'route', icon: Building },
      { name: 'グループ', to: '/groups', type: 'route', icon: Layers },
      { name: 'ログアウト', to: '/logout', type: 'action', icon: LogOut },
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
    const shouldShowDesktop = computed(() => isDesktop.value);
    const shouldShowNav = computed(() => isDesktop.value || isMobileNavOpen.value);
    const showReloadButton = computed(() => {
      if (route.path.startsWith('/buildings')) {
        return true;
      }
      if (route.path.startsWith('/areas/accept-reassign')) {
        return true;
      }
      return false;
    });
    const reloadInProgress = ref(false);
    
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

    const toggleMobileNav = () => {
      if (isDesktop.value) {
        return;
      }
      isMobileNavOpen.value = !isMobileNavOpen.value;
    };

    const closeMobileNav = () => {
      isMobileNavOpen.value = false;
    };
    
    const isActive = (item) => {
      if (item.type === 'action') {
        return false;
      }
      return route.path === item.to || route.path.startsWith(`${item.to}/`);
    };
    
    const handleNavigation = async (item) => {
      if (item.type === 'action' && item.to === '/logout') {
        await logout();
      }
      if (!isDesktop.value) {
        closeMobileNav();
      }
    };

    const reloadPage = () => {
      if (reloadInProgress.value) {
        return;
      }
      reloadInProgress.value = true;
      setTimeout(() => {
        window.location.reload();
      }, 150);
    };

    const logout = async () => {
      try {
        await axios.post('/logout');
        window.location.reload();
      } catch (error) {
        console.error('Failed to logout:', error);
      }
    };
    
    const updateViewport = () => {
      if (typeof window === 'undefined') {
        isDesktop.value = true;
        return;
      }
      isDesktop.value = window.matchMedia('(min-width: 768px)').matches;
      if (isDesktop.value) {
        isMobileNavOpen.value = false;
      }
    };

    watch(() => route.path, () => {
      if (!isDesktop.value) {
        closeMobileNav();
      }
    });

    onMounted(async () => {
      await fetchUser();
      await nextTick();
      initPreline();
      updateViewport();
      if (typeof window !== 'undefined') {
        window.addEventListener('resize', updateViewport);
      }
    });

    onBeforeUnmount(() => {
      if (typeof window !== 'undefined') {
        window.removeEventListener('resize', updateViewport);
      }
    });
    
    return {
      user,
      isMobileNavOpen,
      toggleMobileNav,
      logout,
      isActive,
      handleNavigation,
      routeItems,
      actionItems,
      userInitials,
      logoUrl,
      brandLabel,
      shouldShowNav,
      shouldShowDesktop,
      reloadInProgress,
      showReloadButton,
      reloadPage,
    };
  }
};
</script>
