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
const totalItems = ref(0);
const totalPages = ref(0);

const emit = defineEmits(['add-new', 'edit-purchase-order']);

// 為了模擬資料，暫時使用靜態數據
const mockPurchaseOrders = [
  {
    id: 1,
    order_number: '2025-05-27 [001]',
    supplier: '涂宸菱',
    purchase_date: '2025/5/27',
    created_at: '2025/5/27',
    status: 'processing',
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
    status: 'processing',
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
    status: 'processing',
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
    status: 'processing',
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
    totalItems.value = mockPurchaseOrders.length;
    totalPages.value = Math.ceil(mockPurchaseOrders.length / itemsPerPage.value);
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
  <div class="bg-white rounded-md shadow">
    <!-- 標題與新增按鈕 -->
    <div class="flex justify-between items-center p-4 border-b">
      <h2 class="text-xl font-bold">進貨產品建單</h2>
      <button
        @click="handleAddNew"
        class="bg-[#19A2B3] hover:bg-[#158293] text-white py-2 px-4 rounded flex items-center"
      >
        <span class="mr-2">+</span>
        <span>新增進貨單</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <!-- 搜索欄與篩選器 -->
    <div class="p-4 flex flex-col md:flex-row gap-4">
      <div class="relative flex-grow">
        <input
          v-model="searchQuery"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="搜尋進貨單號、用戶或備註..."
          class="w-full pl-10 pr-4 py-2 border rounded-md"
        />
        <div class="absolute left-3 top-2.5 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
      <div class="w-full md:w-40">
        <select
          v-model="statusFilter"
          @change="handleStatusChange"
          class="w-full py-2 px-3 border rounded-md"
        >
          <option value="all">全部狀態</option>
          <option value="pending">待處理</option>
          <option value="processing">處理中</option>
          <option value="completed">已完成</option>
          <option value="cancelled">已取消</option>
        </select>
      </div>
      <button
        @click="handleSearch"
        class="flex items-center justify-center bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded-md"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
        </svg>
        <span class="ml-1">刷新</span>
      </button>
    </div>

    <!-- 數據顯示區域 -->
    <div class="p-4">
      <div v-if="loading" class="p-4 text-center">
        載入中...
      </div>

      <div v-else-if="error" class="p-4 text-center text-red-500">
        {{ error }}
      </div>

      <div v-else-if="purchaseOrders.length === 0" class="p-4 text-center">
        沒有找到任何進貨訂單
      </div>

      <div v-else>
        <!-- 數據表格 -->
        <div class="overflow-x-auto border rounded-md">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">進貨單號</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">用戶</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">進貨日期</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">記錄時間</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">狀態</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品項目數量</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">總金額</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備註</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in purchaseOrders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">{{ order.order_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.supplier }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.purchase_date }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.created_at }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 rounded-full text-xs font-medium bg-[#caeef3] text-[#19A2B3]"
                    class="px-2 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800"
                  >
                    進行中
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.items_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap">${{ order.total_amount }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.notes }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="handleEdit(order)"
                    class="bg-teal-500 text-white px-3 py-1 rounded text-xs"
                  >
                    顯項
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 分頁 -->
        <div class="mt-4 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            顯示 1-10 項，共 {{ totalItems }} 項
          </div>
          <div class="flex space-x-1">
            <button
              class="border border-gray-300 px-2 py-1 rounded text-gray-600"
            >
              上一頁
            </button>
            <button
              class="bg-teal-500 text-white px-2 py-1 rounded"
            >
              第 1 頁，共 58 頁
            </button>
            <button
              class="border border-gray-300 px-2 py-1 rounded text-gray-600"
            >
              下一頁
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
