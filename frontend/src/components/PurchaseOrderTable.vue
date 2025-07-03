<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const purchaseOrders = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const statusFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(578);
const totalPages = ref(58);

const emit = defineEmits(['add-new', 'edit-purchase-order']);

// 為了模擬資料，暫時使用靜態數據
const mockPurchaseOrders = [
  {
    id: 1,
    order_number: '2025-05-27 [001]',
    supplier: '涂宸菱',
    purchase_date: '2025/5/27',
    created_at: '2025/5/27',
    status: '進行中',
    items_count: 2,
    total_amount: 0,
    notes: 'PCT349空運'
  },
  {
    id: 2,
    order_number: '2025-05-05 [004]',
    supplier: '涂宸菱',
    purchase_date: '2025/5/5',
    created_at: '2025/5/14',
    status: '進行中',
    items_count: 10,
    total_amount: 0,
    notes: 'PCT340海運'
  },
  {
    id: 3,
    order_number: '2025-05-05 [003]',
    supplier: '涂宸菱',
    purchase_date: '2025/5/5',
    created_at: '2025/5/6',
    status: '進行中',
    items_count: 59,
    total_amount: 0,
    notes: 'PCT342A海運'
  },
  {
    id: 4,
    order_number: '2025-04-21 [001]',
    supplier: '黃彥銓',
    purchase_date: '2025/4/21',
    created_at: '2025/4/22',
    status: '進行中',
    items_count: 1,
    total_amount: 0,
    notes: 'SKU 9953 Anniversary GWP Bag_20250421'
  }
];

const fetchPurchaseOrders = async () => {
  loading.value = true;
  error.value = null;

  try {
    // 在實際環境中，這裡會呼叫真實的 API
    // const response = await axios.get('/api/v1/purchase-orders', {
    //   params: {
    //     page: currentPage.value,
    //     per_page: itemsPerPage.value,
    //     search: searchQuery.value,
    //     status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    //   }
    // });

    // 使用模擬資料
    await new Promise(resolve => setTimeout(resolve, 300)); // 模擬網絡延遲

    purchaseOrders.value = mockPurchaseOrders;
    totalItems.value = 578;
    totalPages.value = 58;
  } catch (err) {
    console.error('Error fetching purchase orders:', err);
    error.value = '無法載入進貨訂單資料。請稍後再試。';
  } finally {
    loading.value = false;
  }
};

const handleAddNew = () => {
  emit('add-new');
};

const handleEdit = (purchaseOrder) => {
  emit('edit-purchase-order', purchaseOrder);
};

const handleDelete = async (id) => {
  if (!confirm('確定要刪除此進貨訂單？')) return;

  try {
    // 在實際環境中，這裡會呼叫真實的 API
    // await axios.delete(`/api/v1/purchase-orders/${id}`);

    // 使用模擬資料
    await new Promise(resolve => setTimeout(resolve, 300)); // 模擬網絡延遲

    purchaseOrders.value = purchaseOrders.value.filter(order => order.id !== id);
    totalItems.value = purchaseOrders.value.length;
    totalPages.value = Math.ceil(purchaseOrders.value.length / itemsPerPage.value);
  } catch (err) {
    console.error('Error deleting purchase order:', err);
    alert('刪除進貨訂單時發生錯誤。請稍後再試。');
  }
};

const handleSearch = () => {
  currentPage.value = 1;
  fetchPurchaseOrders();
};

const handleStatusChange = () => {
  currentPage.value = 1;
  fetchPurchaseOrders();
};

const changePage = (page) => {
  currentPage.value = page;
  fetchPurchaseOrders();
};

onMounted(() => {
  fetchPurchaseOrders();
});
</script>

<template>
  <div class="purchase-order-container">
    <!-- 標題區域 -->
    <div class="header-section mb-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">進貨產品建單</h1>
        <div class="relative">
          <button
            @click="handleAddNew"
            class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
          >
            <span class="text-lg">+</span>
            <span>新增進貨單</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- 搜尋和篩選區域 -->
    <div class="search-filter-section mb-6">
      <div class="flex flex-col md:flex-row gap-4">
        <!-- 搜尋欄 -->
        <div class="flex-1 relative">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="搜尋進貨單號、用戶或備註..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
          <button
            @click="handleSearch"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </button>
        </div>

        <!-- 狀態篩選 -->
        <div class="w-full md:w-48">
          <select
            v-model="statusFilter"
            @change="handleStatusChange"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
          >
            <option value="all">全部狀態</option>
            <option value="進行中">進行中</option>
            <option value="已完成">已完成</option>
            <option value="已取消">已取消</option>
          </select>
        </div>

        <!-- 刷新按鈕 -->
        <button
          @click="fetchPurchaseOrders"
          class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          <span>刷新</span>
        </button>
      </div>
    </div>

    <!-- 資料統計 -->
    <div class="stats-section mb-4">
      <div class="flex justify-between items-center">
        <div class="text-sm text-gray-600">
          顯示 {{ (currentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(currentPage * itemsPerPage, totalItems) }} 項，共 {{ totalItems }} 項
        </div>
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-600">每頁顯示</span>
          <select
            v-model="itemsPerPage"
            @change="fetchPurchaseOrders"
            class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-teal-500"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
    </div>

    <!-- 載入中狀態 -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-teal-500"></div>
      <p class="mt-2 text-gray-600">載入中...</p>
    </div>

    <!-- 錯誤狀態 -->
    <div v-else-if="error" class="text-center py-8">
      <div class="text-red-500 mb-4">{{ error }}</div>
      <button
        @click="fetchPurchaseOrders"
        class="px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-lg transition-colors"
      >
        重試
      </button>
    </div>

    <!-- 資料表格 -->
    <div v-else class="table-section">
      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
          <thead class="bg-teal-500 text-white">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">進貨單號</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">用戶</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">進貨日期</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">紀錄時間</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">狀態</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">商品項目數量</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">總金額</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">操作</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">備註</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="order in purchaseOrders" :key="order.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ order.order_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ order.supplier }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ order.purchase_date }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ order.created_at }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  {{ order.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                {{ order.items_count }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${{ order.total_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="handleEdit(order)"
                  class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded text-xs transition-colors"
                >
                  編輯
                </button>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                {{ order.notes }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 分頁控制 -->
    <div class="pagination-section mt-6">
      <div class="flex justify-center items-center space-x-2">
        <button
          @click="changePage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 rounded-lg transition-colors"
        >
          上一頁
        </button>

        <div class="flex items-center space-x-1">
          <span class="text-sm text-gray-600">第</span>
          <span class="font-medium">{{ currentPage }}</span>
          <span class="text-sm text-gray-600">頁，共</span>
          <span class="font-medium">{{ totalPages }}</span>
          <span class="text-sm text-gray-600">頁</span>
        </div>

        <button
          @click="changePage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 rounded-lg transition-colors"
        >
          下一頁
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.purchase-order-container {
  @apply p-6 bg-gray-50 min-h-screen;
}

.table-section {
  @apply bg-white rounded-lg shadow-sm;
}

.header-section h1 {
  @apply text-gray-800;
}

/* 響應式設計 */
@media (max-width: 768px) {
  .purchase-order-container {
    @apply p-4;
  }

  .header-section {
    @apply mb-4;
  }

  .header-section h1 {
    @apply text-xl;
  }

  .search-filter-section {
    @apply mb-4;
  }

  .table-section {
    @apply overflow-x-auto;
  }

  .table-section table {
    @apply min-w-full;
  }
}
</style>
