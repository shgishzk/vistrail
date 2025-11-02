<template>
  <div class="grid grid-cols-1 gap-6">
    <div class="text-center">
      <h3 class="text-xl font-medium text-gray-900 mb-4">メインメニュー</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <a
        v-for="item in menuItems"
        :key="item.href"
        :href="item.href"
        class="flex items-center justify-center px-4 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
      >
        {{ item.label }}
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MainMenu',
  setup() {
    const menuItems = [
      { label: '区域', href: '/areas' },
      { label: 'マンション', href: '/buildings' },
      { label: 'グループ', href: '/groups' },
    ];

    return { menuItems };
  },
};
</script>
