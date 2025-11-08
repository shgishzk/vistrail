import { createRouter, createWebHistory } from 'vue-router';
import MainMenu from '../components/MainMenu.vue';

const AreasView = () => import('../views/AreasView.vue');
const AreasMyView = () => import('../views/AreasMyView.vue');
const AreasVisitModeView = () => import('../views/AreasVisitModeView.vue');
const BuildingsView = () => import('../views/BuildingsView.vue');
const BuildingDetailView = () => import('../views/BuildingDetailView.vue');
const GroupsView = () => import('../views/GroupsView.vue');

const titleMap = {
  home: 'メニュー',
  areas: '区域一覧',
  areasMy: '自分の区域',
  areasMyVisit: '訪問モード',
  buildings: 'マンション',
  buildingDetail: 'マンション詳細',
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
    path: '/areas/my',
    name: 'areasMy',
    component: AreasMyView,
  },
  {
    path: '/areas/my/:visitId/visit',
    name: 'areasMyVisit',
    component: AreasVisitModeView,
  },
  {
    path: '/buildings',
    name: 'buildings',
    component: BuildingsView,
  },
  {
    path: '/buildings/:id',
    name: 'buildingDetail',
    component: BuildingDetailView,
  },
  {
    path: '/groups/:groupId?',
    name: 'groups',
    component: GroupsView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const defaultTitle = (typeof document !== 'undefined' && document.title) ? document.title : 'オンライン区域';

router.afterEach((to, from, failure) => {
  const prefix = import.meta.env.VITE_APP_BRAND_PREFIX || '';
  const brand = prefix ? `${prefix}オンライン区域` : 'オンライン区域';
  const pageTitle = titleMap[to.name] || defaultTitle;
  if (typeof document !== 'undefined') {
    document.title = `${pageTitle} - ${brand}`;
  }
  if (!failure) setTimeout(() => window.HSStaticMethods.autoInit(), 100);
});

export default router;
