<script setup>
import SideMenu from './components/SideMenu.vue';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// 根據目前路由動態決定 activePage
const activePage = computed(() => {
  const path = route.path;
  if (path.startsWith('/purchase-orders')) return 'purchase-orders';
  if (path.startsWith('/locations')) return 'locations';
  if (path.startsWith('/items')) return 'items';
  if (path.startsWith('/inventory-records')) return 'inventory-records';
  if (path.startsWith('/qrcode')) return 'qrcode';
  if (path.startsWith('/batch-operations')) return 'batch-operations';
  if (path.startsWith('/permissions')) return 'permissions';
  return 'purchase-orders'; // 預設值
});
</script>

<template>
  <div class="flex h-screen">
    <!-- 側邊欄 -->
    <div class="h-screen fixed left-0 top-0 z-10 w-64">
      <SideMenu :activePage="activePage" class="h-full" />
    </div>

    <!-- 主內容區域 -->
    <div class="flex-1 overflow-auto w-full">
      <router-view />
    </div>
  </div>
</template>

<style scoped>
body {
  margin: 0;
  padding: 0;
  font-family: 'Noto Sans TC', sans-serif;
  background-color: #f9fafb;
  overflow: hidden;
  height: 100vh;
  width: 100vw;
}

#app {
  height: 100vh;
  width: 100vw;
  overflow: hidden;
}
</style>
