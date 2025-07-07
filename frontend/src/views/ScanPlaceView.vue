<template>
  <div class="min-h-screen bg-gray-100">
    <!-- 響應式側邊選單 -->


    <!-- 移動端頂部選單 -->
    <div class="md:hidden bg-[#19A2B3] text-white p-4 flex items-center justify-between">
      <div class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
        </svg>
        <h1 class="text-lg font-bold">{{ t('scanAndPlace.title') }}</h1>
      </div>
      <button @click="showMobileMenu = !showMobileMenu" class="p-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- 主要內容區域 -->
    <div class="md:ml-64 p-4">
      <!-- 桌面版標題 -->
      <div class="hidden md:block mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ t('scanAndPlace.title') }}</h1>
        <p class="text-gray-600 mt-2">{{ t('scanAndPlace.description') }}</p>
      </div>

      <!-- 主要操作選項 -->
      <div v-if="currentMode === 'menu'" class="max-w-md mx-auto space-y-4">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4 text-center">選擇操作模式</h2>

          <div class="space-y-3">
            <button
              @click="selectMode('first-binding')"
              class="w-full p-4 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
              </svg>
              <span>{{ t('scanAndPlace.options.firstBinding') }}</span>
            </button>

            <button
              @click="selectMode('process-shipping')"
              class="w-full p-4 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              <span>{{ t('scanAndPlace.options.processShipping') }}</span>
            </button>

            <button
              @click="selectMode('return-to-stock')"
              class="w-full p-4 bg-purple-500 hover:bg-purple-600 text-white rounded-lg flex items-center justify-center space-x-2 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
              </svg>
              <span>{{ t('scanAndPlace.returnToStock.title') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 商品歸位綁定流程 -->
      <div v-if="currentMode === 'first-binding'" class="max-w-md mx-auto">
        <FirstBindingComponent @back="backToMenu" @complete="handleComplete" />
      </div>

      <!-- 加工/出貨流程 -->
      <div v-if="currentMode === 'process-shipping'" class="max-w-md mx-auto">
        <ProcessShippingComponent @back="backToMenu" @complete="handleComplete" />
      </div>

      <!-- 加工完成後歸還流程 -->
      <div v-if="currentMode === 'return-to-stock'" class="max-w-md mx-auto">
        <ReturnToStockComponent @back="backToMenu" @complete="handleComplete" />
      </div>
    </div>

    <!-- 移動端側邊選單覆蓋 -->
    <div v-if="showMobileMenu" class="md:hidden fixed inset-0 z-50 bg-black bg-opacity-50" @click="showMobileMenu = false">
      <div class="bg-white w-64 h-full" @click.stop>
        <div class="p-4 border-b">
          <h2 class="text-lg font-semibold">選單</h2>
        </div>
        <div class="p-4">
          <button @click="backToMenu" class="w-full text-left p-2 hover:bg-gray-100 rounded">
            回到主選單
          </button>
        </div>
      </div>
    </div>

    <!-- 成功提示 -->
    <div v-if="showSuccessMessage" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
      {{ successMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

import FirstBindingComponent from '../components/FirstBindingComponent.vue';
import ProcessShippingComponent from '../components/ProcessShippingComponent.vue';
import ReturnToStockComponent from '../components/ReturnToStockComponent.vue';

const { t } = useI18n();

// 響應式資料
const currentMode = ref('menu');
const showMobileMenu = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref('');

// 選擇模式
const selectMode = (mode) => {
  currentMode.value = mode;
  showMobileMenu.value = false;
};

// 返回主選單
const backToMenu = () => {
  currentMode.value = 'menu';
  showMobileMenu.value = false;
};

// 處理完成事件
const handleComplete = (message) => {
  successMessage.value = message;
  showSuccessMessage.value = true;
  setTimeout(() => {
    showSuccessMessage.value = false;
    currentMode.value = 'menu';
  }, 3000);
};

// 生命週期
onMounted(() => {
  // 初始化
});
</script>
