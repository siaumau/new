<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const purchaseOrders = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const statusFilter = ref('all');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalItems = ref(578);
const totalPages = ref(58);

const emit = defineEmits(['edit-purchase-order']);

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
    us_purchase_order_status: 'pending',
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
    us_purchase_order_status: 'generated',
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
    us_purchase_order_status: 'pending',
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
    us_purchase_order_status: 'reviewed',
    notes: 'SKU 9953 Anniversary GWP Bag_20250421'
  }
];

const fetchPurchaseOrders = async () => {
  loading.value = true;
  error.value = null;

  try {
    // 呼叫真實的 API
    const response = await axios.get('/api/v1/posin', {
      params: {
        page: currentPage.value,
        per_page: itemsPerPage.value,
        search: searchQuery.value,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
      }
    });

    purchaseOrders.value = response.data.data;
    totalItems.value = response.data.total;
    totalPages.value = response.data.last_page;
  } catch (err) {
    console.error('Error fetching purchase orders:', err);
    error.value = '無法載入進貨訂單資料。請稍後再試。';
  } finally {
    loading.value = false;
  }
};

// 下載進貨單模板
const downloadTemplate = () => {
  // 根據 purchase_order_template.csv 的格式創建模板
  const csvContent = [
    'order_number,user_name,order_date,expected_date,notes,item_id,item_batch,item_count,item_price,item_expireday,item_validyear,itemtype',
    '2025-0703【0001】,eric,2025-06-15,2026-01-20,pct101,10,PAT001,100,25.50,2026-12-31,2,1',
    '2025-0703【0001】,eric,2025-06-15,2026-01-20,pct102,22,PAT002,50,15.00,2026-06-30,1,1',
    '2025-0703【0002】,eric,2025-06-16,2026-01-25,pct103,23,PAT003,200,30.00,2026-12-31,2,1'
  ].join('\n');

  // 添加BOM以支援Excel正確顯示繁體中文
  const BOM = '\uFEFF';
  const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });

  // 創建下載連結
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'purchase_order_template.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// 處理CSV文件匯入
const handleFileImport = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // 檢查文件類型
  if (!file.name.toLowerCase().endsWith('.csv')) {
    alert('請選擇CSV格式的文件');
    return;
  }

  try {
    const text = await file.text();
    const lines = text.split('\n').filter(line => line.trim());

    if (lines.length < 2) {
      alert('CSV文件格式錯誤或沒有資料');
      return;
    }

    // 解析CSV數據（跳過標題行）
    const dataLines = lines.slice(1);
    const purchaseOrdersData = [];

    for (let i = 0; i < dataLines.length; i++) {
      const line = dataLines[i].trim();
      if (!line) continue;

      // 解析CSV行
      const columns = parseCSVLine(line);

      if (columns.length < 12) {
        alert(`第 ${i + 2} 行資料格式錯誤，請檢查CSV格式`);
        return;
      }

      // 根據模板格式解析資料
      const purchaseOrderData = {
        order_number: columns[0].trim(),
        user_name: columns[1].trim(),
        order_date: columns[2].trim(),
        expected_date: columns[3].trim(),
        notes: columns[4].trim() || null,
        item_id: parseInt(columns[5].trim()),
        item_batch: columns[6].trim(),
        item_count: parseInt(columns[7].trim()),
        item_price: parseFloat(columns[8].trim()),
        item_expireday: columns[9].trim(),
        item_validyear: parseInt(columns[10].trim()),
        itemtype: parseInt(columns[11].trim())
      };

      purchaseOrdersData.push(purchaseOrderData);
    }

    // 批量匯入進貨單資料
    await importPurchaseOrders(purchaseOrdersData);

    // 清空文件輸入
    event.target.value = '';

  } catch (error) {
    console.error('匯入文件失敗:', error);
    alert('匯入文件失敗: ' + error.message);
  }
};

// 解析CSV行（處理包含逗號的欄位）
const parseCSVLine = (line) => {
  const result = [];
  let current = '';
  let inQuotes = false;

  for (let i = 0; i < line.length; i++) {
    const char = line[i];

    if (char === '"') {
      inQuotes = !inQuotes;
    } else if (char === ',' && !inQuotes) {
      result.push(current);
      current = '';
    } else {
      current += char;
    }
  }

  result.push(current);
  return result;
};

// 批量匯入進貨單資料
const importPurchaseOrders = async (purchaseOrdersData) => {
  loading.value = true;

  try {
    const response = await axios.post('/api/v1/posin/batch', {
      purchase_orders: purchaseOrdersData
    });

    if (response.status === 200) {
      const { created_count, error_count, errors } = response.data;

      // 顯示匯入結果
      let message = `匯入完成！\n成功: ${created_count} 筆\n失敗: ${error_count} 筆`;

      if (errors && errors.length > 0) {
        // 生成錯誤報告 TXT 文件
        const errorReport = generateErrorReport(errors, created_count, error_count);
        downloadErrorReport(errorReport);

        message += '\n\n錯誤詳情:\n';
        errors.slice(0, 5).forEach(error => {
          message += `進貨單 ${error.order_number}: ${error.error}\n`;
        });
        if (errors.length > 5) {
          message += `... 還有 ${errors.length - 5} 個錯誤`;
        }
        message += '\n\n錯誤詳細報告已下載為 TXT 文件，請查閱。';
      }

      alert(message);

      // 重新載入進貨單資料
      if (created_count > 0) {
        await fetchPurchaseOrders();
      }
    }
  } catch (error) {
    console.error('匯入進貨單失敗:', error);

    // 如果是伺服器回應的錯誤，也生成錯誤報告
    if (error.response?.data) {
      const errorReport = generateServerErrorReport(error.response.data);
      downloadErrorReport(errorReport);
      alert('匯入進貨單失敗，錯誤詳細報告已下載為 TXT 文件，請查閱。\n\n' + (error.response?.data?.message || error.message));
    } else {
      alert('匯入進貨單失敗: ' + error.message);
    }
  } finally {
    loading.value = false;
  }
};

// 生成錯誤報告內容
const generateErrorReport = (errors, createdCount, errorCount) => {
  const now = new Date();
  const timestamp = now.toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });

  let report = `進貨單批量匯入錯誤報告\n`;
  report += `生成時間: ${timestamp}\n`;
  report += `${'='.repeat(50)}\n\n`;

  report += `匯入統計:\n`;
  report += `- 成功匯入: ${createdCount} 筆\n`;
  report += `- 失敗: ${errorCount} 筆\n`;
  report += `- 總計: ${createdCount + errorCount} 筆\n\n`;

  if (errors && errors.length > 0) {
    report += `錯誤詳情:\n`;
    report += `${'='.repeat(50)}\n`;

    errors.forEach((error, index) => {
      report += `\n${index + 1}. 進貨單號: ${error.order_number}\n`;
      report += `   錯誤原因: ${error.error}\n`;
      report += `   ${'-'.repeat(30)}\n`;
    });
  }

    report += `\n\n建議處理方式:\n`;
  report += `1. 檢查進貨單是否已提交為美國進貨單（已提交的無法重複匯入）\n`;
  report += `2. 確認商品ID是否存在於系統中（若不存在，整個進貨單都不會創建）\n`;
  report += `3. 檢查是否有重複的項目（相同商品ID、批次、到期日）\n`;
  report += `4. 檢查日期格式是否正確 (YYYY-MM-DD)\n`;
  report += `5. 確認數值欄位格式是否正確\n`;
  report += `6. 修正錯誤後重新匯入\n\n`;

  report += `重複匯入說明:\n`;
  report += `- 相同進貨單號可以重複匯入，會新增項目到現有進貨單\n`;
  report += `- 只有當「商品ID + 批次 + 到期日」完全相同時才視為重複項目\n`;
  report += `- 進貨單已提交為美國進貨單時，無法重複匯入\n`;
  report += `- 如果商品ID不存在，整個進貨單都不會創建\n`;

  return report;
};

// 生成伺服器錯誤報告內容
const generateServerErrorReport = (errorData) => {
  const now = new Date();
  const timestamp = now.toLocaleString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });

  let report = `進貨單批量匯入系統錯誤報告\n`;
  report += `生成時間: ${timestamp}\n`;
  report += `${'='.repeat(50)}\n\n`;

  report += `錯誤類型: 系統錯誤\n`;
  report += `錯誤訊息: ${errorData.message || '未知錯誤'}\n\n`;

  if (errorData.errors) {
    report += `詳細錯誤:\n`;
    report += `${'='.repeat(50)}\n`;

    Object.keys(errorData.errors).forEach(key => {
      report += `\n欄位: ${key}\n`;
      if (Array.isArray(errorData.errors[key])) {
        errorData.errors[key].forEach(error => {
          report += `- ${error}\n`;
        });
      } else {
        report += `- ${errorData.errors[key]}\n`;
      }
    });
  }

  report += `\n\n建議處理方式:\n`;
  report += `1. 檢查 CSV 文件格式是否正確\n`;
  report += `2. 確認所有必填欄位都有填寫\n`;
  report += `3. 檢查資料格式是否符合系統要求\n`;
  report += `4. 如問題持續，請聯繫系統管理員\n`;

  return report;
};

// 下載錯誤報告 TXT 文件
const downloadErrorReport = (reportContent) => {
  const now = new Date();
  const dateStr = now.toISOString().slice(0, 10); // YYYY-MM-DD
  const timeStr = now.toTimeString().slice(0, 8).replace(/:/g, ''); // HHMMSS

  // 添加 BOM 以支援 Excel 正確顯示繁體中文
  const BOM = '\uFEFF';
  const blob = new Blob([BOM + reportContent], { type: 'text/plain;charset=utf-8;' });

  // 創建下載連結
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `進貨單匯入錯誤報告_${dateStr}_${timeStr}.txt`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

const handleEdit = (purchaseOrder) => {
  // 導航到商品項目頁面
  router.push(`/posin/${purchaseOrder.id}/items`);
};

const handleGenerateQR = (purchaseOrder) => {
  // 導航到商品項目頁面進行QR碼生成
  router.push(`/posin/${purchaseOrder.id}/items`);
};

const handleDelete = async (id) => {
  if (!confirm('確定要刪除此進貨訂單？')) return;

  try {
    // 呼叫真實的 API
    await axios.delete(`/api/v1/posin/${id}`);

    // 重新載入資料
    await fetchPurchaseOrders();
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
        <div class="flex space-x-2">
          <!-- 下載模板按鈕 -->
          <button
            @click="downloadTemplate"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>下載模板</span>
          </button>

          <!-- 匯入按鈕 -->
          <label class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            <span>匯入</span>
            <input
              type="file"
              accept=".csv"
              class="hidden"
              @change="handleFileImport"
            />
          </label>
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
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">狀態</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">項目數</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">美國進貨單</th>
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
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  {{ order.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                {{ order.items_count }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                <span v-if="order.us_purchase_order_status === 'generated'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                  已產生
                </span>
                <span v-else-if="order.us_purchase_order_status === 'reviewed'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                  已審查
                </span>
                <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                  待處理
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <!-- 編輯按鈕 - 只在美國進貨單未轉換時顯示 -->
                  <button
                    v-if="order.us_purchase_order_status === 'pending'"
                    @click="handleEdit(order)"
                    class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded text-xs transition-colors"
                    :title="'編輯進貨單'"
                  >
                    編輯
                  </button>

                  <!-- 查看按鈕 - 美國進貨單已轉換時顯示 -->
                  <button
                    v-if="order.us_purchase_order_status === 'generated' || order.us_purchase_order_status === 'reviewed'"
                    @click="handleEdit(order)"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition-colors"
                    :title="'查看商品項目'"
                  >
                    查看
                  </button>

                  <!-- 刪除按鈕 - 只在美國進貨單未轉換時顯示 -->
                  <button
                    v-if="order.us_purchase_order_status === 'pending'"
                    @click="handleDelete(order.id)"
                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition-colors"
                    :title="'刪除進貨單'"
                  >
                    刪除
                  </button>

                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                {{ order.notes && order.notes.length > 10 ? order.notes.substring(0, 10) + '...' : order.notes }}
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
  @apply p-6 bg-gray-50 min-h-screen  ;
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
