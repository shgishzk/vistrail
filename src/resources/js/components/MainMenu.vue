<template>
  <div class="grid grid-cols-1 gap-6">
    <div class="text-center">
      <h3 class="text-xl font-medium text-gray-900 mb-4">メインメニュー</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <RouterLink
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        class="flex items-center justify-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
      >
        {{ item.label }}
      </RouterLink>
    </div>
  </div>
</template>

<script>
import { RouterLink } from 'vue-router';

export default {
  name: 'MainMenu',
  components: { RouterLink },
  setup() {
    const menuItems = [
      { label: '区域', to: '/areas' },
      { label: 'マンション', to: '/buildings' },
      { label: 'グループ', to: '/groups' },
    ];

    return { menuItems };
  },
};
</script>
