<script setup>
import SideMenu from './components/SideMenu.vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

// 側邊欄的基礎收合狀態 (由使用者操作或螢幕寬度決定)
const isSidebarCollapsed = ref(false);

// 最終給模板使用的狀態，它會根據頁面和裝置強制覆寫基礎狀態
const finalSidebarState = computed(() => {
  const isMobile = window.innerWidth < 768;
  // 在行動裝置上且路徑為 /scan-place 時，強制收合
  if (isMobile && route.path === '/scan-place') {
    return true;
  }
  // 其他情況下，使用基礎狀態
  return isSidebarCollapsed.value;
});

// 檢查螢幕大小來設定預設的收合狀態
const checkScreenSize = () => {
  isSidebarCollapsed.value = window.innerWidth < 768; // md breakpoint
};

// 組件掛載時，設定監聽器
onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});

// 組件卸載時，移除監聽器，避免記憶體洩漏
onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize);
});

// 處理來自 SideMenu 的切換事件
const handleSidebarToggle = (collapsed) => {
  isSidebarCollapsed.value = collapsed;
};

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
  if (path.startsWith('/scan-place')) return 'scan-place';
  if (path.startsWith('/qr-codes')) return 'qrcode';
  if (path.startsWith('/movement-history')) return 'movement-history';
  return 'purchase-orders'; // 預設值
});
</script>

<template>
  <div class="flex h-screen">
    <!-- 側邊欄 -->
    <div class="h-screen fixed left-0 top-0 z-20 transition-all duration-300 ease-in-out"
         :class="finalSidebarState ? 'w-16' : 'w-64'">
      <SideMenu
        :activePage="activePage"
        :initialCollapsed="finalSidebarState"
        @toggle="handleSidebarToggle"
        class="h-full" />
    </div>

    <!-- 主內容區域 -->
    <div class="flex-1 overflow-auto w-full transition-all duration-300 ease-in-out"
         :class="finalSidebarState ? 'ml-16' : 'ml-64'">
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
