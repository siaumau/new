<script setup>
import SideMenu from './components/SideMenu.vue';
</script>

<template>
  <div class="flex h-screen bg-gray-100">
    <!-- 側邊欄 -->
    <SideMenu activePage="purchase-orders" />

    <!-- 主內容區域 -->
    <div class="flex-1 overflow-auto p-6">
      <router-view />
    </div>
  </div>
</template>

<style>
body {
  margin: 0;
  font-family: 'Noto Sans TC', sans-serif;
}

#app {
  height: 100vh;
}
</style>
