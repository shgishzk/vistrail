import { createRouter, createWebHistory } from 'vue-router';
import MainMenu from '../components/MainMenu.vue';

const AreasView = () => import('../views/AreasView.vue');
const BuildingsView = () => import('../views/BuildingsView.vue');
const GroupsView = () => import('../views/GroupsView.vue');

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

// Preline: Reinitialize components after each route change.
// router.afterEach(async (to, from, failure) => {
//   if (!failure) setTimeout(() => window.HSStaticMethods.autoInit(), 100);
// });

export default router;
