<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <!-- 標題和返回按鈕 -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-lg font-semibold">{{ t('scanAndPlace.firstBinding.title') }}</h2>
      <button @click="$emit('back')" class="text-gray-500 hover:text-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- 流程說明 -->
    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
      <p class="text-sm text-blue-800">{{ t('scanAndPlace.firstBinding.description') }}</p>
      <div class="mt-2 text-sm text-blue-700">
        <p>{{ t('scanAndPlace.firstBinding.step1') }}</p>
        <p>{{ t('scanAndPlace.firstBinding.step2') }}</p>
      </div>
    </div>

    <!-- 當前狀態 -->
    <div class="mb-6">
      <div class="flex items-center space-x-2 mb-2">
        <div :class="['w-4 h-4 rounded-full', locationScanned ? 'bg-green-500' : 'bg-gray-300']"></div>
        <span class="text-sm font-medium">{{ t('scanAndPlace.firstBinding.scanLocation') }}</span>
      </div>
      <div class="flex items-center space-x-2">
        <div :class="['w-4 h-4 rounded-full', boxScanned ? 'bg-green-500' : 'bg-gray-300']"></div>
        <span class="text-sm font-medium">{{ t('scanAndPlace.firstBinding.scanBox') }}</span>
      </div>
    </div>

    <!-- 掃描櫃位 -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.firstBinding.locationCode') }}
      </label>
      <div class="relative">
        <input
          v-model="locationCode"
          type="text"
          :placeholder="t('scanAndPlace.firstBinding.locationPlaceholder')"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          @input="handleLocationInput"
          @keyup.enter="handleLocationScan"
          @click="openScanner('location')"
        />
        <button
          @click="openScanner('location')"
          class="absolute right-2 top-2 p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zM3 21h8v-8H3v8zm2-6h4v4H5v-4zM13 3v8h8V3h-8zm6 6h-4V5h4v4zM13 21h8v-8h-8v8zm2-6h4v4h-4v-4z"/>
          </svg>
        </button>
      </div>
      <div v-if="locationScanned" class="mt-2 text-sm text-green-600">
        {{ t('scanAndPlace.firstBinding.messages.locationScanned', { location: scannedLocation }) }}
      </div>
    </div>

    <!-- 掃描商品箱子 -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.firstBinding.boxCode') }}
      </label>
      <div class="relative">
        <input
          v-model="boxCode"
          type="text"
          :placeholder="t('scanAndPlace.firstBinding.boxPlaceholder')"
          :disabled="!locationScanned"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
          @input="handleBoxInput"
          @keyup.enter="handleBoxScan"
          @click="openScanner('box')"
        />
        <button
          @click="openScanner('box')"
          :disabled="!locationScanned"
          class="absolute right-2 top-2 p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-300"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zM3 21h8v-8H3v8zm2-6h4v4H5v-4zM13 3v8h8V3h-8zm6 6h-4V5h4v4zM13 21h8v-8h-8v8zm2-6h4v4h-4v-4z"/>
          </svg>
        </button>
      </div>
      <div v-if="boxScanned" class="mt-2 text-sm text-green-600">
        {{ t('scanAndPlace.firstBinding.messages.boxScanned', { box: scannedBox }) }}
      </div>
    </div>

    <!-- 綁定選項 -->
    <div v-if="locationScanned && boxScanned" class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.firstBinding.bindingOptions') }}
      </label>
      <div class="space-y-2">
        <label class="flex items-center">
          <input
            v-model="bindingOption"
            type="radio"
            value="bind-and-inbox"
            class="mr-2"
          />
          <span class="text-sm">{{ t('scanAndPlace.firstBinding.bindAndInboxOption') }}</span>
        </label>
        <label class="flex items-center">
          <input
            v-model="bindingOption"
            type="radio"
            value="bind-only"
            class="mr-2"
          />
          <span class="text-sm">{{ t('scanAndPlace.firstBinding.bindOnlyOption') }}</span>
        </label>
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
        {{ t('scanAndPlace.firstBinding.reset') }}
      </button>
      <button
        @click="handleBinding"
        :disabled="!canBind || loading"
        class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-300"
      >
        <span v-if="loading">{{ t('scanAndPlace.common.loading') }}</span>
        <span v-else>{{ t('scanAndPlace.firstBinding.confirm') }}</span>
      </button>
    </div>
    <QrCodeScanner v-if="showScanner" @close="closeScanner" @scanned="handleScanned" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import QrCodeScanner from './QrCodeScanner.vue';

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
const bindingOption = ref('bind-and-inbox');
const errorMessage = ref('');
const loading = ref(false);
const showScanner = ref(false);
const scannedDataType = ref(null);

// 計算屬性
const canBind = computed(() => {
  return locationScanned.value && boxScanned.value;
});

// 開啟掃描器
const openScanner = (type) => {
  scannedDataType.value = type;
  showScanner.value = true;
};

// 關閉掃描器
const closeScanner = () => {
  showScanner.value = false;
};

// 處理掃描結果
const handleScanned = (data) => {
  if (scannedDataType.value === 'location') {
    locationCode.value = data;
    handleLocationScan();
  } else if (scannedDataType.value === 'box') {
    boxCode.value = data;
    handleBoxScan();
  }
  closeScanner();
};


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
    errorMessage.value = t('scanAndPlace.firstBinding.messages.invalidLocation');
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
      errorMessage.value = data.message || t('scanAndPlace.firstBinding.messages.invalidLocation');
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
    errorMessage.value = '';
  }
};

// 處理箱子掃描
const handleBoxScan = async () => {
  if (!boxCode.value.trim()) {
    errorMessage.value = t('scanAndPlace.firstBinding.messages.invalidBox');
    return;
  }

  try {
    loading.value = true;

    // 實際API呼叫驗證箱子
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
      scannedBox.value = boxCode.value;
      boxScanned.value = true;
      errorMessage.value = '';
    } else {
      errorMessage.value = data.message || t('scanAndPlace.firstBinding.messages.invalidBox');
    }

  } catch (error) {
    console.error('Box validation error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 處理綁定
const handleBinding = async () => {
  if (!canBind.value) return;

  try {
    loading.value = true;

    // 實際API呼叫執行綁定
    const response = await fetch('/api/v1/scan-place/first-binding', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        location_code: scannedLocation.value,
        box_code: scannedBox.value,
        binding_option: bindingOption.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // 發送完成事件
      emit('complete', data.message);
    } else {
      errorMessage.value = data.message || t('scanAndPlace.firstBinding.messages.bindingError');
    }

  } catch (error) {
    console.error('Binding error:', error);
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
  bindingOption.value = 'bind-and-inbox';
  errorMessage.value = '';
};
</script>
