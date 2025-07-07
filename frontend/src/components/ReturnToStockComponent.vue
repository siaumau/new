<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <!-- 標題和返回按鈕 -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-lg font-semibold">{{ t('scanAndPlace.returnToStock.title') }}</h2>
      <button @click="$emit('back')" class="text-gray-500 hover:text-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- 流程說明 -->
    <div class="mb-6 p-4 bg-purple-50 rounded-lg">
      <p class="text-sm text-purple-800">{{ t('scanAndPlace.returnToStock.description') }}</p>
      <div class="mt-2 text-sm text-purple-700">
        <p>{{ t('scanAndPlace.returnToStock.step1') }}</p>
        <p>{{ t('scanAndPlace.returnToStock.step2') }}</p>
      </div>
    </div>

    <!-- 當前狀態 -->
    <div class="mb-6">
      <div class="flex items-center space-x-2 mb-2">
        <div :class="['w-4 h-4 rounded-full', locationScanned ? 'bg-green-500' : 'bg-gray-300']"></div>
        <span class="text-sm font-medium">{{ t('scanAndPlace.returnToStock.scanLocation') }}</span>
      </div>
      <div class="flex items-center space-x-2">
        <div :class="['w-4 h-4 rounded-full', boxScanned ? 'bg-green-500' : 'bg-gray-300']"></div>
        <span class="text-sm font-medium">{{ t('scanAndPlace.returnToStock.scanBox') }}</span>
      </div>
    </div>

    <!-- 掃描目標櫃位 -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.returnToStock.locationCode') }}
      </label>
      <div class="relative">
        <input
          v-model="locationCode"
          type="text"
          :placeholder="t('scanAndPlace.returnToStock.locationPlaceholder')"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          @input="handleLocationInput"
          @keyup.enter="handleLocationScan"
        />
        <button
          @click="handleLocationScan"
          class="absolute right-2 top-2 p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
          </svg>
        </button>
      </div>
      <div v-if="locationScanned" class="mt-2 text-sm text-green-600">
        {{ t('scanAndPlace.returnToStock.messages.locationScanned', { location: scannedLocation }) }}
      </div>
    </div>

    <!-- 掃描商品箱子 -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.returnToStock.boxCode') }}
      </label>
      <div class="relative">
        <input
          v-model="boxCode"
          type="text"
          :placeholder="t('scanAndPlace.returnToStock.boxPlaceholder')"
          :disabled="!locationScanned"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
          @input="handleBoxInput"
          @keyup.enter="handleBoxScan"
        />
        <button
          @click="handleBoxScan"
          :disabled="!locationScanned"
          class="absolute right-2 top-2 p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-300"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
          </svg>
        </button>
      </div>
      <div v-if="boxScanned" class="mt-2 text-sm text-green-600">
        {{ t('scanAndPlace.returnToStock.messages.boxScanned', { box: scannedBox }) }}
      </div>
    </div>

    <!-- 商品資訊 -->
    <div v-if="boxScanned && itemInfo" class="mb-6 p-4 bg-gray-50 rounded-lg">
      <h3 class="font-medium text-gray-900 mb-2">商品資訊</h3>
      <div class="text-sm text-gray-600 space-y-1">
        <p><span class="font-medium">商品代碼：</span>{{ itemInfo.itemCode }}</p>
        <p><span class="font-medium">商品名稱：</span>{{ itemInfo.itemName }}</p>
        <p><span class="font-medium">原位置：</span>{{ itemInfo.originalLocation }}</p>
        <p><span class="font-medium">當前位置：</span>
          <span class="text-blue-600 font-medium">{{ itemInfo.currentLocation }}</span>
        </p>
        <p><span class="font-medium">狀態：</span>{{ itemInfo.status }}</p>
      </div>
    </div>

    <!-- 歸還摘要 -->
    <div v-if="locationScanned && boxScanned" class="mb-6 p-4 bg-blue-50 rounded-lg">
      <h3 class="font-medium text-blue-900 mb-2">歸還摘要</h3>
      <div class="text-sm text-blue-800 space-y-1">
        <p><span class="font-medium">商品：</span>{{ scannedBox }}</p>
        <p><span class="font-medium">從：</span>{{ itemInfo?.currentLocation || 'CH七樓加工區' }}</p>
        <p><span class="font-medium">到：</span>{{ scannedLocation }}</p>
      </div>
    </div>

    <!-- 錯誤訊息 -->
    <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
      <p class="text-sm text-red-800">{{ errorMessage }}</p>
    </div>

    <!-- 操作按鈕 -->
    <div class="flex space-x-3">
      <button
        @click="handleReset"
        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
      >
        {{ t('scanAndPlace.returnToStock.reset') }}
      </button>
      <button
        @click="handleReturn"
        :disabled="!canReturn || loading"
        class="flex-1 px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600 disabled:bg-gray-300"
      >
        <span v-if="loading">{{ t('scanAndPlace.common.loading') }}</span>
        <span v-else>{{ t('scanAndPlace.returnToStock.confirm') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// 定義 emit
const emit = defineEmits(['back', 'complete']);

// 響應式資料
const locationCode = ref('');
const boxCode = ref('');
const locationScanned = ref(false);
const boxScanned = ref(false);
const scannedLocation = ref('');
const scannedBox = ref('');
const errorMessage = ref('');
const loading = ref(false);
const itemInfo = ref(null);

// 計算屬性
const canReturn = computed(() => {
  return locationScanned.value && boxScanned.value;
});

// 處理櫃位輸入
const handleLocationInput = () => {
  if (locationCode.value.length > 0) {
    locationScanned.value = false;
    errorMessage.value = '';
  }
};

// 處理櫃位掃描
const handleLocationScan = async () => {
  if (!locationCode.value.trim()) {
    errorMessage.value = t('scanAndPlace.returnToStock.messages.invalidLocation');
    return;
  }

  try {
    loading.value = true;

    // 實際API呼叫驗證櫃位
    const response = await fetch('/api/v1/scan-place/validate-location', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        location_code: locationCode.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      scannedLocation.value = locationCode.value;
      locationScanned.value = true;
      errorMessage.value = '';
    } else {
      errorMessage.value = data.message || t('scanAndPlace.returnToStock.messages.invalidLocation');
    }

  } catch (error) {
    console.error('Location validation error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 處理箱子輸入
const handleBoxInput = () => {
  if (boxCode.value.length > 0) {
    boxScanned.value = false;
    itemInfo.value = null;
    errorMessage.value = '';
  }
};

// 處理箱子掃描
const handleBoxScan = async () => {
  if (!boxCode.value.trim()) {
    errorMessage.value = t('scanAndPlace.returnToStock.messages.invalidBox');
    return;
  }

  try {
    loading.value = true;

    // 實際API呼叫驗證箱子並獲取商品資訊
    const response = await fetch('/api/v1/scan-place/validate-box', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        box_code: boxCode.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // 設定商品資訊
      itemInfo.value = {
        itemCode: data.data.item_code,
        itemName: data.data.item_name,
        originalLocation: '原櫃位',
        currentLocation: data.data.location_code || 'CH七樓加工區',
        status: data.data.location_code === 'CH七樓加工區' ? '加工中' : '其他'
      };

      scannedBox.value = boxCode.value;
      boxScanned.value = true;
      errorMessage.value = '';
    } else {
      errorMessage.value = data.message || t('scanAndPlace.returnToStock.messages.invalidBox');
    }

  } catch (error) {
    console.error('Box validation error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 處理歸還
const handleReturn = async () => {
  if (!canReturn.value) return;

  try {
    loading.value = true;

    // 實際API呼叫執行歸還
    const response = await fetch('/api/v1/scan-place/return-to-stock', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        location_code: scannedLocation.value,
        box_code: scannedBox.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // 發送完成事件
      emit('complete', data.message);
    } else {
      errorMessage.value = data.message || t('scanAndPlace.returnToStock.messages.returnError');
    }

  } catch (error) {
    console.error('Return error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 重置
const handleReset = () => {
  locationCode.value = '';
  boxCode.value = '';
  locationScanned.value = false;
  boxScanned.value = false;
  scannedLocation.value = '';
  scannedBox.value = '';
  errorMessage.value = '';
  itemInfo.value = null;
};
</script>
