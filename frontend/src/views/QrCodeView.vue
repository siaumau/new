<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SideMenu from '../components/SideMenu.vue';

const { t } = useI18n();

// 響應式資料
const qrCodes = ref([]);
const locations = ref([]);
const loading = ref(false);
const error = ref('');

// 搜尋和篩選
const searchTerm = ref('');
const statusFilter = ref('');
const locationFilter = ref('');
const inboxStatusFilter = ref('');

// 分頁
const currentPage = ref(1);
const perPage = ref(10);
const totalPages = ref(0);
const totalItems = ref(0);

// 統計資料
const statistics = ref({
  total: 0,
  by_status: {},
  by_inbox_status: {},
  recent_generated: 0,
  with_location: 0,
  without_location: 0
});

// 選中的項目
const selectedItems = ref([]);

// 模態框
const showLocationModal = ref(false);
const showStatusModal = ref(false);
const selectedQrCode = ref(null);

// 位置分配表單
const locationForm = ref({
  location_id: '',
  floor_level: ''
});

// 狀態更新表單
const statusForm = ref({
  status: ''
});

// 批次操作
const batchAction = ref('');

// 掃描歸位相關狀態
const showScanModal = ref(false);
const scanMode = ref(''); // 'box' 或 'location'
const scannedBoxQR = ref('');
const scannedLocationQR = ref('');
const scanResult = ref('');

// 計算屬性
const filteredQrCodes = computed(() => {
  let filtered = qrCodes.value;

  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    filtered = filtered.filter(qr =>
      qr.item_code.toLowerCase().includes(term) ||
      qr.item_name.toLowerCase().includes(term) ||
      qr.item_batch.toLowerCase().includes(term) ||
      qr.qr_content.toLowerCase().includes(term)
    );
  }

  if (statusFilter.value) {
    filtered = filtered.filter(qr => qr.status === statusFilter.value);
  }

  if (locationFilter.value) {
    filtered = filtered.filter(qr => qr.location_id == locationFilter.value);
  }

  if (inboxStatusFilter.value !== '') {
    filtered = filtered.filter(qr => qr.item_inbox_status == inboxStatusFilter.value);
  }

  return filtered;
});

const allSelected = computed(() => {
  return filteredQrCodes.value.length > 0 &&
         selectedItems.value.length === filteredQrCodes.value.length;
});

// 方法
const fetchQrCodes = async () => {
  loading.value = true;
  error.value = '';

  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      per_page: perPage.value
    });

    if (searchTerm.value) params.append('search', searchTerm.value);
    if (statusFilter.value) params.append('status', statusFilter.value);
    if (locationFilter.value) params.append('location_id', locationFilter.value);

    const response = await fetch(`/api/v1/qr-codes?${params}`);
    const data = await response.json();

    if (response.ok) {
      qrCodes.value = data.data;
      totalPages.value = data.last_page;
      totalItems.value = data.total;
    } else {
      error.value = data.message || t('qrCodes.messages.loadError');
    }
  } catch (err) {
    error.value = t('qrCodes.messages.loadError');
    console.error('Error fetching QR codes:', err);
  } finally {
    loading.value = false;
  }
};

const fetchLocations = async () => {
  try {
    const response = await fetch('/api/v1/locations');
    const data = await response.json();

    if (response.ok) {
      locations.value = data;
    }
  } catch (err) {
    console.error('Error fetching locations:', err);
  }
};

const fetchStatistics = async () => {
  try {
    const response = await fetch('/api/v1/qr-codes/statistics');
    const data = await response.json();

    if (response.ok) {
      statistics.value = data;
    }
  } catch (err) {
    console.error('Error fetching statistics:', err);
  }
};

const handleSearch = () => {
  currentPage.value = 1;
  fetchQrCodes();
};

const handleStatusFilter = () => {
  currentPage.value = 1;
  fetchQrCodes();
};

const handleLocationFilter = () => {
  currentPage.value = 1;
  fetchQrCodes();
};

const handleInboxStatusFilter = () => {
  currentPage.value = 1;
  fetchQrCodes();
};

const changePage = (page) => {
  currentPage.value = page;
  fetchQrCodes();
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedItems.value = [];
  } else {
    selectedItems.value = filteredQrCodes.value.map(qr => qr.qr_id);
  }
};

const toggleSelectItem = (qrId) => {
  const index = selectedItems.value.indexOf(qrId);
  if (index > -1) {
    selectedItems.value.splice(index, 1);
  } else {
    selectedItems.value.push(qrId);
  }
};

const openLocationModal = (qrCode) => {
  selectedQrCode.value = qrCode;
  locationForm.value = {
    location_id: qrCode.location_id || '',
    floor_level: qrCode.floor_level || ''
  };
  showLocationModal.value = true;
};

const openStatusModal = (qrCode) => {
  selectedQrCode.value = qrCode;
  statusForm.value = {
    status: qrCode.status
  };
  showStatusModal.value = true;
};

const assignLocation = async () => {
  try {
    const response = await fetch(`/api/v1/qr-codes/${selectedQrCode.value.qr_id}/assign-location`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(locationForm.value)
    });

    const data = await response.json();

    if (response.ok) {
      showLocationModal.value = false;
      fetchQrCodes();
      fetchStatistics();
    } else {
      error.value = data.message || t('qrCodes.messages.assignLocationError');
    }
  } catch (err) {
    error.value = t('qrCodes.messages.assignLocationError');
    console.error('Error assigning location:', err);
  }
};

const updateStatus = async () => {
  try {
    const response = await fetch(`/api/v1/qr-codes/${selectedQrCode.value.qr_id}/update-status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(statusForm.value)
    });

    const data = await response.json();

    if (response.ok) {
      showStatusModal.value = false;
      fetchQrCodes();
      fetchStatistics();
    } else {
      error.value = data.message || t('qrCodes.messages.updateStatusError');
    }
  } catch (err) {
    error.value = t('qrCodes.messages.updateStatusError');
    console.error('Error updating status:', err);
  }
};

const executeBatchAction = async () => {
  if (!batchAction.value || selectedItems.value.length === 0) {
    return;
  }

  if (!confirm(t('qrCodes.messages.batchActionConfirm', { count: selectedItems.value.length }))) {
    return;
  }

  try {
    const response = await fetch('/api/v1/qr-codes/batch-update-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        qr_ids: selectedItems.value,
        status: batchAction.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      selectedItems.value = [];
      batchAction.value = '';
      fetchQrCodes();
      fetchStatistics();
    } else {
      error.value = data.message || t('qrCodes.messages.batchActionError');
    }
  } catch (err) {
    error.value = t('qrCodes.messages.batchActionError');
    console.error('Error executing batch action:', err);
  }
};

const getStatusColor = (status) => {
  switch (status) {
    case 'generated': return 'bg-blue-100 text-blue-800';
    case 'printed': return 'bg-green-100 text-green-800';
    case 'used': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};

const getInboxStatusColor = (status) => {
  return status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('zh-TW');
};

// 掃描歸位功能
const openScanModal = (mode) => {
  scanMode.value = mode;
  showScanModal.value = true;
  scannedBoxQR.value = '';
  scannedLocationQR.value = '';
  scanResult.value = '';
};

const handleScan = async () => {
  try {
    if (scanMode.value === 'box') {
      // 掃描箱子QR Code
      scannedBoxQR.value = '模擬掃描結果'; // 這裡應該整合實際的掃描器
      scanResult.value = '已掃描箱子QR Code';
    } else if (scanMode.value === 'location') {
      // 掃描位置QR Code
      scannedLocationQR.value = '模擬掃描結果'; // 這裡應該整合實際的掃描器
      scanResult.value = '已掃描位置QR Code';
    }
  } catch (error) {
    scanResult.value = '掃描失敗: ' + error.message;
  }
};

const assignBoxToLocation = async () => {
  if (!scannedBoxQR.value || !scannedLocationQR.value) {
    scanResult.value = '請先掃描箱子和位置QR Code';
    return;
  }

  try {
    scanResult.value = '正在分配位置...';

    const response = await fetch('/api/v1/qr-codes/scan-assign', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        box_qr_content: scannedBoxQR.value,
        location_qr_content: scannedLocationQR.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      scanResult.value = `箱子已成功歸位到 ${data.location.location_code}！`;
      showScanModal.value = false;

      // 重新載入資料
      await fetchQrCodes();
      await fetchStatistics();
    } else {
      scanResult.value = '歸位失敗: ' + (data.message || '未知錯誤');
    }
  } catch (error) {
    scanResult.value = '歸位失敗: ' + error.message;
  }
};

// 生命週期
onMounted(() => {
  fetchQrCodes();
  fetchLocations();
  fetchStatistics();
});
</script>

<template>
  <div class="flex h-screen bg-gray-100">


    <!-- 主要內容 -->
    <div class="flex-1 overflow-y-auto">
      <!-- 頁面標題 -->
      <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ t('qrCodes.title') }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ t('qrCodes.description') }}</p>
          </div>
        </div>
      </div>

      <!-- 統計卡片 -->
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">{{ t('qrCodes.stats.total') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ statistics.total }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">{{ t('qrCodes.stats.withLocation') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ statistics.with_location }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">{{ t('qrCodes.stats.withoutLocation') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ statistics.without_location }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-500">{{ t('qrCodes.stats.recentGenerated') }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ statistics.recent_generated }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 搜尋和篩選 -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('qrCodes.search.placeholder') }}</label>
              <input
                v-model="searchTerm"
                type="text"
                :placeholder="t('qrCodes.search.placeholder')"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keyup.enter="handleSearch"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('qrCodes.filters.status') }}</label>
              <select
                v-model="statusFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="handleStatusFilter"
              >
                <option value="">{{ t('qrCodes.filters.allStatus') }}</option>
                <option value="generated">{{ t('qrCodes.status.generated') }}</option>
                <option value="printed">{{ t('qrCodes.status.printed') }}</option>
                <option value="used">{{ t('qrCodes.status.used') }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('qrCodes.filters.location') }}</label>
              <select
                v-model="locationFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="handleLocationFilter"
              >
                <option value="">{{ t('qrCodes.filters.allLocations') }}</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.location_code }} - {{ location.location_name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('qrCodes.filters.inboxStatus') }}</label>
              <select
                v-model="inboxStatusFilter"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @change="handleInboxStatusFilter"
              >
                <option value="">{{ t('qrCodes.filters.allInboxStatus') }}</option>
                <option value="0">{{ t('qrCodes.inboxStatus.pending') }}</option>
                <option value="1">{{ t('qrCodes.inboxStatus.completed') }}</option>
              </select>
            </div>

            <div class="flex items-end space-x-2">
              <button
                @click="handleSearch"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                {{ t('qrCodes.search.button') }}
              </button>
              <button
                @click="openScanModal('box')"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                title="掃描歸位"
              >
                📱
              </button>
            </div>
          </div>
        </div>

        <!-- 批次操作 -->
        <div v-if="selectedItems.length > 0" class="bg-white rounded-lg shadow p-4 mb-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <span class="text-sm font-medium text-gray-700">
                {{ t('qrCodes.batch.selected', { count: selectedItems.length }) }}
              </span>
              <select
                v-model="batchAction"
                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">{{ t('qrCodes.batch.selectAction') }}</option>
                <option value="printed">{{ t('qrCodes.batch.markAsPrinted') }}</option>
                <option value="used">{{ t('qrCodes.batch.markAsUsed') }}</option>
              </select>
            </div>
            <button
              @click="executeBatchAction"
              :disabled="!batchAction"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('qrCodes.batch.execute') }}
            </button>
          </div>
        </div>

        <!-- 錯誤訊息 -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ error }}</p>
            </div>
          </div>
        </div>

        <!-- QR Code 列表 -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.itemCode') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.itemName') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.batch') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.boxNumber') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.location') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.status') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.inboxStatus') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.generatedAt') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ t('qrCodes.table.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                  <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                    {{ t('qrCodes.messages.loading') }}
                  </td>
                </tr>
                <tr v-else-if="filteredQrCodes.length === 0">
                  <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                    {{ t('qrCodes.messages.noData') }}
                  </td>
                </tr>
                <tr v-else v-for="qrCode in filteredQrCodes" :key="qrCode.qr_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      :checked="selectedItems.includes(qrCode.qr_id)"
                      @change="toggleSelectItem(qrCode.qr_id)"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ qrCode.item_code }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ qrCode.item_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ qrCode.item_batch }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ qrCode.box_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div v-if="qrCode.location">
                      {{ qrCode.location.location_code }}
                      <span v-if="qrCode.floor_level" class="text-gray-500">
                        ({{ qrCode.floor_level }})
                      </span>
                    </div>
                    <span v-else class="text-gray-400">{{ t('qrCodes.table.noLocation') }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusColor(qrCode.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ t(`qrCodes.status.${qrCode.status}`) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getInboxStatusColor(qrCode.item_inbox_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ t(`qrCodes.inboxStatus.${qrCode.item_inbox_status ? 'completed' : 'pending'}`) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(qrCode.generated_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        @click="openLocationModal(qrCode)"
                        class="text-blue-600 hover:text-blue-900"
                        :title="t('qrCodes.actions.assignLocation')"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                      </button>
                      <button
                        @click="openStatusModal(qrCode)"
                        class="text-green-600 hover:text-green-900"
                        :title="t('qrCodes.actions.updateStatus')"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 分頁 -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <button
                  @click="changePage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ t('qrCodes.pagination.prev') }}
                </button>
                <button
                  @click="changePage(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ t('qrCodes.pagination.next') }}
                </button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    {{ t('qrCodes.pagination.showing') }}
                    <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                    {{ t('qrCodes.pagination.to') }}
                    <span class="font-medium">{{ Math.min(currentPage * perPage, totalItems) }}</span>
                    {{ t('qrCodes.pagination.of') }}
                    <span class="font-medium">{{ totalItems }}</span>
                    {{ t('qrCodes.pagination.results') }}
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button
                      @click="changePage(currentPage - 1)"
                      :disabled="currentPage === 1"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <button
                      v-for="page in Math.min(totalPages, 10)"
                      :key="page"
                      @click="changePage(page)"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        page === currentPage
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                      ]"
                    >
                      {{ page }}
                    </button>
                    <button
                      @click="changePage(currentPage + 1)"
                      :disabled="currentPage === totalPages"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 位置分配模態框 -->
    <div v-if="showLocationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ t('qrCodes.modals.assignLocation.title') }}</h3>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('qrCodes.modals.assignLocation.location') }}
              </label>
              <select
                v-model="locationForm.location_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">{{ t('qrCodes.modals.assignLocation.selectLocation') }}</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.location_code }} - {{ location.location_name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('qrCodes.modals.assignLocation.floorLevel') }}
              </label>
              <input
                v-model="locationForm.floor_level"
                type="text"
                :placeholder="t('qrCodes.modals.assignLocation.floorLevelPlaceholder')"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="showLocationModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            {{ t('qrCodes.modals.cancel') }}
          </button>
          <button
            @click="assignLocation"
            :disabled="!locationForm.location_id"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ t('qrCodes.modals.assign') }}
          </button>
        </div>
      </div>
    </div>

    <!-- 狀態更新模態框 -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ t('qrCodes.modals.updateStatus.title') }}</h3>
        </div>
        <div class="px-6 py-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('qrCodes.modals.updateStatus.status') }}
            </label>
            <select
              v-model="statusForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="generated">{{ t('qrCodes.status.generated') }}</option>
              <option value="printed">{{ t('qrCodes.status.printed') }}</option>
              <option value="used">{{ t('qrCodes.status.used') }}</option>
            </select>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="showStatusModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            {{ t('qrCodes.modals.cancel') }}
          </button>
          <button
            @click="updateStatus"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
          >
            {{ t('qrCodes.modals.update') }}
          </button>
        </div>
      </div>
    </div>

    <!-- 掃描歸位模態框 -->
    <div v-if="showScanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ t('qrCodes.modals.scan.title') }}</h3>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('qrCodes.modals.scan.mode') }}
              </label>
              <select
                v-model="scanMode"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="box">{{ t('qrCodes.modals.scan.box') }}</option>
                <option value="location">{{ t('qrCodes.modals.scan.location') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('qrCodes.modals.scan.qrCode') }}
              </label>
              <input
                v-model="scannedBoxQR"
                type="text"
                :placeholder="t('qrCodes.modals.scan.boxPlaceholder')"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('qrCodes.modals.scan.locationQRCode') }}
              </label>
              <input
                v-model="scannedLocationQR"
                type="text"
                :placeholder="t('qrCodes.modals.scan.locationPlaceholder')"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="showScanModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            {{ t('qrCodes.modals.cancel') }}
          </button>
          <button
            @click="handleScan"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
          >
            {{ t('qrCodes.modals.scan.scan') }}
          </button>
        </div>
      </div>
    </div>

    <!-- 掃描結果模態框 -->
    <div v-if="scanResult" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">{{ t('qrCodes.modals.scanResult.title') }}</h3>
        </div>
        <div class="px-6 py-4">
          <p class="text-sm text-gray-700">{{ scanResult }}</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="showScanModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            {{ t('qrCodes.modals.close') }}
          </button>
          <button
            @click="assignBoxToLocation"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700"
          >
            {{ t('qrCodes.modals.assign') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* 自定義樣式 */
.table-container {
  overflow-x: auto;
}

@media (max-width: 768px) {
  .table-container {
    font-size: 0.875rem;
  }
}
</style>
