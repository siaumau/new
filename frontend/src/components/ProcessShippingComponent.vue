<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <!-- 標題和返回按鈕 -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-lg font-semibold">{{ t('scanAndPlace.processShipping.title') }}</h2>
      <button @click="$emit('back')" class="text-gray-500 hover:text-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- 流程說明 -->
    <div class="mb-6 p-4 bg-green-50 rounded-lg">
      <p class="text-sm text-green-800">{{ t('scanAndPlace.processShipping.description') }}</p>
    </div>

    <!-- 當前狀態 -->
    <div class="mb-6">
      <div class="flex items-center space-x-2">
        <div :class="['w-4 h-4 rounded-full', boxScanned ? 'bg-green-500' : 'bg-gray-300']"></div>
        <span class="text-sm font-medium">{{ t('scanAndPlace.processShipping.scanBox') }}</span>
      </div>
    </div>

    <!-- 掃描商品箱子 -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.processShipping.boxCode') }}
      </label>
      <div class="relative">
        <input
          v-model="boxCode"
          type="text"
          :placeholder="t('scanAndPlace.processShipping.boxPlaceholder')"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          @input="handleBoxInput"
          @keyup.enter="handleBoxScan"
        />
        <button
          @click="handleBoxScan"
          class="absolute right-2 top-2 p-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
          </svg>
        </button>
      </div>
      <div v-if="boxScanned" class="mt-2 text-sm text-green-600">
        {{ t('scanAndPlace.processShipping.messages.boxScanned', { box: scannedBox }) }}
      </div>
    </div>

    <!-- 商品資訊 -->
    <div v-if="boxScanned && itemInfo" class="mb-6 p-4 bg-gray-50 rounded-lg">
      <h3 class="font-medium text-gray-900 mb-2">商品資訊</h3>
      <div class="text-sm text-gray-600 space-y-1">
        <p><span class="font-medium">商品代碼：</span>{{ itemInfo.itemCode }}</p>
        <p><span class="font-medium">商品名稱：</span>{{ itemInfo.itemName }}</p>
        <p><span class="font-medium">當前位置：</span>{{ itemInfo.currentLocation }}</p>
        <p><span class="font-medium">狀態：</span>{{ itemInfo.status }}</p>
      </div>
    </div>

    <!-- 出庫類型選擇 -->
    <div v-if="boxScanned" class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ t('scanAndPlace.processShipping.outboundType') }}
      </label>
      <div class="space-y-2">
        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
          <input
            v-model="outboundType"
            type="radio"
            value="processing"
            class="mr-3"
          />
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-sm font-medium">{{ t('scanAndPlace.processShipping.processing') }}</span>
          </div>
        </label>

        <label class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100">
          <input
            v-model="outboundType"
            type="radio"
            value="shipping"
            class="mr-3"
          />
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1-1V9a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h4a1 1 0 001-1m-6 0a1 1 0 01-1-1v-1" />
            </svg>
            <span class="text-sm font-medium">{{ t('scanAndPlace.processShipping.shipping') }}</span>
          </div>
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
        {{ t('scanAndPlace.processShipping.reset') }}
      </button>
      <button
        @click="handleOutbound"
        :disabled="!canOutbound || loading"
        class="flex-1 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:bg-gray-300"
      >
        <span v-if="loading">{{ t('scanAndPlace.common.loading') }}</span>
        <span v-else>{{ t('scanAndPlace.processShipping.confirm') }}</span>
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
const boxCode = ref('');
const boxScanned = ref(false);
const scannedBox = ref('');
const outboundType = ref('processing');
const errorMessage = ref('');
const loading = ref(false);
const itemInfo = ref(null);

// 計算屬性
const canOutbound = computed(() => {
  return boxScanned.value && outboundType.value;
});

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
    errorMessage.value = t('scanAndPlace.processShipping.messages.invalidBox');
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
        currentLocation: data.data.location_code || '未分配',
        status: data.data.inbox_status === 'completed' ? '已入庫' : '待入庫'
      };

      scannedBox.value = boxCode.value;
      boxScanned.value = true;
      errorMessage.value = '';
    } else {
      errorMessage.value = data.message || t('scanAndPlace.processShipping.messages.invalidBox');
    }

  } catch (error) {
    console.error('Box validation error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 處理出庫
const handleOutbound = async () => {
  if (!canOutbound.value) return;

  try {
    loading.value = true;

    // 實際API呼叫執行出庫
    const response = await fetch('/api/v1/scan-place/process-shipping', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        box_code: scannedBox.value,
        outbound_type: outboundType.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      // 發送完成事件
      emit('complete', data.message);
    } else {
      errorMessage.value = data.message || t('scanAndPlace.processShipping.messages.outboundError');
    }

  } catch (error) {
    console.error('Outbound error:', error);
    errorMessage.value = t('scanAndPlace.common.networkError');
  } finally {
    loading.value = false;
  }
};

// 重置
const handleReset = () => {
  boxCode.value = '';
  boxScanned.value = false;
  scannedBox.value = '';
  outboundType.value = 'processing';
  errorMessage.value = '';
  itemInfo.value = null;
};
</script>
