import { createRouter, createWebHistory } from 'vue-router';
import MainMenu from '../components/MainMenu.vue';

const AreasView = () => import('../views/AreasView.vue');
const BuildingsView = () => import('../views/BuildingsView.vue');
const GroupsView = () => import('../views/GroupsView.vue');

const titleMap = {
  home: 'メニュー',
  areas: '区域一覧',
  buildings: 'マンション',
  groups: 'グループ',
};

const routes = [
  {
    path: '/',
    name: 'home',
    component: MainMenu,
  },
  {
    path: '/areas',
    name: 'areas',
    component: AreasView,
  },
  {
    path: '/buildings',
    name: 'buildings',
    component: BuildingsView,
  },
  {
    path: '/groups',
    name: 'groups',
    component: GroupsView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const defaultTitle = (typeof document !== 'undefined' && document.title) ? document.title : 'オンライン区域';

router.afterEach((to) => {
  const prefix = import.meta.env.VITE_APP_BRAND_PREFIX || '';
  const brand = prefix ? `${prefix}オンライン区域` : 'オンライン区域';
  const pageTitle = titleMap[to.name] || defaultTitle;
  if (typeof document !== 'undefined') {
    document.title = `${pageTitle} - ${brand}`;
  }
});

export default router;
