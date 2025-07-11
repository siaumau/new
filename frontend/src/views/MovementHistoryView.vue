<template>
  <div class="min-h-screen bg-gray-100">


    <div class="p-8">
      <!-- 頁面標題 -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ t('movementHistory.title') }}</h1>
        <p class="text-gray-600 mt-2">{{ t('movementHistory.description') }}</p>
      </div>

      <!-- 統計卡片 -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">{{ t('movementHistory.stats.total') }}</p>
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
              <p class="text-sm font-medium text-gray-500">{{ t('movementHistory.stats.assignments') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ statistics.assignments }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-500">{{ t('movementHistory.stats.moves') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ statistics.moves }}</p>
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
              <p class="text-sm font-medium text-gray-500">{{ t('movementHistory.stats.returns') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ statistics.returns }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 搜尋和篩選 -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('movementHistory.search.itemCode') }}</label>
            <input
              v-model="searchTerm"
              type="text"
              :placeholder="t('movementHistory.search.itemCodePlaceholder')"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @keyup.enter="handleSearch"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('movementHistory.filters.movementType') }}</label>
            <select
              v-model="movementTypeFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="handleMovementTypeFilter"
            >
              <option value="">{{ t('movementHistory.filters.allTypes') }}</option>
              <option value="assign">{{ t('movementHistory.types.assign') }}</option>
              <option value="move">{{ t('movementHistory.types.move') }}</option>
              <option value="return">{{ t('movementHistory.types.return') }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('movementHistory.filters.dateRange') }}</label>
            <input
              v-model="dateRange"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="handleDateFilter"
            />
          </div>

          <div class="flex items-end">
            <button
              @click="handleSearch"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {{ t('movementHistory.search.button') }}
            </button>
          </div>
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

      <!-- 移動記錄列表 -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.itemCode') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.itemName') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.boxNumber') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.fromLocation') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.toLocation') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.movementType') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.reason') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.operator') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('movementHistory.table.movedAt') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading" class="hover:bg-gray-50">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                  {{ t('movementHistory.loading') }}
                </td>
              </tr>
              <tr v-else-if="movementLogs.length === 0" class="hover:bg-gray-50">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                  {{ t('movementHistory.noRecords') }}
                </td>
              </tr>
              <tr v-else v-for="log in movementLogs" :key="log.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ log.item_code }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ log.item_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ log.box_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span v-if="log.from_location_code" class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded">
                    {{ log.from_location_code }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <span v-if="log.to_location_code" class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">
                    {{ log.to_location_code }}
                  </span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getMovementTypeColor(log.movement_type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ t(`movementHistory.types.${log.movement_type}`) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ log.reason || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ log.operator }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(log.moved_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 分頁 -->
      <div v-if="totalPages > 1" class="bg-white rounded-lg shadow p-4 mt-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-700">{{ t('movementHistory.pagination.showing') }}</span>
            <span class="text-sm font-medium text-gray-900">{{ (currentPage - 1) * perPage + 1 }}</span>
            <span class="text-sm text-gray-700">{{ t('movementHistory.pagination.to') }}</span>
            <span class="text-sm font-medium text-gray-900">{{ Math.min(currentPage * perPage, totalItems) }}</span>
            <span class="text-sm text-gray-700">{{ t('movementHistory.pagination.of') }}</span>
            <span class="text-sm font-medium text-gray-900">{{ totalItems }}</span>
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('movementHistory.pagination.prev') }}
            </button>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ t('movementHistory.pagination.next') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';


const { t } = useI18n();

// 響應式資料
const movementLogs = ref([]);
const loading = ref(false);
const error = ref('');

// 搜尋和篩選
const searchTerm = ref('');
const movementTypeFilter = ref('');
const dateRange = ref('');

// 分頁
const currentPage = ref(1);
const perPage = ref(20);
const totalPages = ref(0);
const totalItems = ref(0);

// 統計資料
const statistics = ref({
  total: 0,
  assignments: 0,
  moves: 0,
  returns: 0
});

// 載入移動記錄
const fetchMovementLogs = async () => {
  loading.value = true;
  error.value = '';

  try {
    const params = new URLSearchParams({
      page: currentPage.value,
      per_page: perPage.value,
      search: searchTerm.value,
      movement_type: movementTypeFilter.value,
      date_range: dateRange.value
    });

    const response = await fetch(`/api/v1/movement-logs?${params}`);
    const data = await response.json();

    if (response.ok) {
      movementLogs.value = data.data || [];
      totalItems.value = data.total || 0;
      totalPages.value = Math.ceil(totalItems.value / perPage.value);
      statistics.value = data.statistics || {
        total: 0,
        assignments: 0,
        moves: 0,
        returns: 0
      };
    } else {
      error.value = data.message || t('movementHistory.errors.fetchFailed');
    }
  } catch (err) {
    error.value = t('movementHistory.errors.fetchFailed');
    console.error('Error fetching movement logs:', err);
  } finally {
    loading.value = false;
  }
};

// 搜尋處理
const handleSearch = () => {
  currentPage.value = 1;
  fetchMovementLogs();
};

// 篩選處理
const handleMovementTypeFilter = () => {
  currentPage.value = 1;
  fetchMovementLogs();
};

const handleDateFilter = () => {
  currentPage.value = 1;
  fetchMovementLogs();
};

// 分頁處理
const changePage = (page) => {
  currentPage.value = page;
  fetchMovementLogs();
};

// 格式化日期時間
const formatDateTime = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString('zh-TW');
};

// 獲取移動類型顏色
const getMovementTypeColor = (type) => {
  switch (type) {
    case 'assign':
      return 'bg-green-100 text-green-800';
    case 'move':
      return 'bg-blue-100 text-blue-800';
    case 'return':
      return 'bg-purple-100 text-purple-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// 生命週期
onMounted(() => {
  fetchMovementLogs();
});
</script>
