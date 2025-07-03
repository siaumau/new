<script setup>
import SideMenu from './components/SideMenu.vue';
</script>

<template>
  <div class="flex h-screen">
    <!-- 側邊欄 -->
    <div class="h-screen fixed left-0 top-0 z-10 w-64">
      <SideMenu activePage="purchase-orders" class="h-full" />
    </div>

    <!-- 主內容區域 -->
    <div class="flex-1 overflow-auto w-full">
      <router-view />
    </div>
  </div>
</template>

<style>
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
