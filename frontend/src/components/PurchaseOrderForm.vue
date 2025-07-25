<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  showForm: {
    type: Boolean,
    required: true
  },
  purchaseOrder: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

const isEditing = computed(() => !!props.purchaseOrder);
const formTitle = computed(() => isEditing.value ? '編輯進貨訂單' : '新增進貨訂單');

// 表單數據
const form = ref({
  supplier: '',
  purchase_date: '',
  expected_delivery_date: '',
  record_time: '',
  status: 'pending',
  notes: '',
  items: []
});

// 所有可選商品
const availableItems = ref([]);
const suppliers = ref([]);
const loading = ref(false);
const error = ref(null);

// 處理表單提交
const handleSubmit = async () => {
  if (!validateForm()) return;

  loading.value = true;
  error.value = null;

  try {
    // 將表單數據轉換為後端需要的格式
    const formData = {
      _users_id: 1, // 假設當前用戶 ID
      posin_sn: isEditing.value ? props.purchaseOrder.order_number : `${new Date().toISOString().split('T')[0]} [${Math.floor(Math.random() * 1000).toString().padStart(3, '0')}]`,
      posin_user: form.value.supplier,
      posin_dt: form.value.purchase_date,
      posin_note: form.value.notes || '',
      posin_items: form.value.items.map(item => {
        // 找到對應的商品
        const selectedItem = availableItems.value.find(i => i.item_id == item.item_id);

        return {
          itemtype: selectedItem?.item_type || 1,
          item_id: item.item_id,
          item_name: selectedItem?.item_name || '',
          item_sn: selectedItem?.item_sn || '',
          item_spec: selectedItem?.item_spec || '',
          item_batch: item.item_batch || new Date().toISOString().split('T')[0].replace(/-/g, ''),
          item_count: item.quantity,
          item_price: 0, // 預設為 0
          item_expireday: null,
          item_validyear: null
        };
      })
    };

    let response;

    if (isEditing.value) {
      // 更新現有訂單
      response = await axios.put(`/api/v1/posin/${props.purchaseOrder.id}`, formData);
    } else {
      // 創建新訂單
      response = await axios.post('/api/v1/posin', formData);
    }

    emit('saved', response.data);
    emit('close');
  } catch (err) {
    console.error('Error saving purchase order:', err);
    error.value = '保存進貨訂單時發生錯誤。請稍後再試。';
  } finally {
    loading.value = false;
  }
};

// 產生美國進貨單
const generateUsPurchaseOrder = async () => {
  // 顯示確認對話框
  const confirmed = confirm(
    '確定要產生美國進貨單嗎？\n\n' +
    '送出後無法再針對內容做修改調整。\n' +
    'DTC後台會自動產生一個筆美國進貨單供審查。'
  );

  if (!confirmed) return;

  loading.value = true;
  error.value = null;

  try {
    const response = await axios.patch(`/api/v1/posin/${props.purchaseOrder.id}/generate-us-purchase-order`);

    // 顯示成功訊息
    alert('美國進貨單已成功產生！\n\n此筆訂單將無法再進行編輯。');

    // 關閉表單並重新載入資料
    emit('saved', response.data);
    emit('close');
  } catch (err) {
    console.error('Error generating US purchase order:', err);
    if (err.response?.status === 422) {
      error.value = '此筆訂單已經產生過美國進貨單，無法重複產生。';
    } else {
      error.value = '產生美國進貨單時發生錯誤。請稍後再試。';
    }
  } finally {
    loading.value = false;
  }
};

// 表單驗證
const validateForm = () => {
  // 驗證必填欄位
  if (!form.value.supplier) {
    alert('請選擇建單人員');
    return false;
  }

  if (!form.value.purchase_date) {
    alert('請輸入進貨日期');
    return false;
  }

  if (form.value.items.length === 0) {
    alert('請至少添加一個商品項目');
    return false;
  }

  // 驗證每個商品項目
  for (const item of form.value.items) {
    if (!item.item_id) {
      alert('請為所有項目選擇商品');
      return false;
    }

    if (!item.quantity || item.quantity <= 0) {
      alert('所有項目的數量必須大於零');
      return false;
    }
  }

  return true;
};

// 添加新商品項目
const addItem = () => {
  form.value.items.push({
    posinitem_id: null,
    item_id: '',
    quantity: 1,
    item_batch: ''
  });
};

// 移除商品項目
const removeItem = (index) => {
  form.value.items.splice(index, 1);
};

// 重置表單
const resetForm = () => {
  form.value = {
    supplier: '',
    purchase_date: '',
    expected_delivery_date: '',
    record_time: '',
    status: 'pending',
    notes: '',
    items: []
  };
  error.value = null;
};

// 載入編輯數據
const loadEditData = () => {
  if (props.purchaseOrder) {
    form.value = {
      supplier: props.purchaseOrder.supplier,
      purchase_date: props.purchaseOrder.purchase_date,
      expected_delivery_date: props.purchaseOrder.expected_delivery_date || '',
      record_time: props.purchaseOrder.created_at || props.purchaseOrder.record_time || '',
      status: props.purchaseOrder.status,
      notes: props.purchaseOrder.notes || '',
      items: []
    };

    // 處理商品項目
    if (props.purchaseOrder.posin_items && props.purchaseOrder.posin_items.length > 0) {
      form.value.items = props.purchaseOrder.posin_items.map(item => ({
        posinitem_id: item.posinitem_id,
        item_id: item.item_id,
        quantity: item.item_count,
        item_batch: item.item_batch || ''
      }));
    } else if (props.purchaseOrder.items && props.purchaseOrder.items.length > 0) {
      form.value.items = props.purchaseOrder.items.map(item => ({
        item_id: item.item_id,
        quantity: item.quantity,
        item_batch: item.item_batch || ''
      }));
    } else {
      // 如果沒有項目，添加一個空白項目
      addItem();
    }
  }
};

// 載入商品和供應商列表
const fetchItems = async () => {
  try {
    const response = await axios.get('/api/v1/items');
    availableItems.value = response.data.data || response.data;
    console.log('Available items:', availableItems.value);
  } catch (err) {
    console.error('Error fetching items:', err);
  }
};

const fetchSuppliers = async () => {
  try {
    // 暫時使用模擬資料，因為後端沒有 suppliers 表
    // 從 posin 表中獲取唯一的用戶作為供應商
    const response = await axios.get('/api/v1/posin');
    const uniqueSuppliers = [...new Set(response.data.data.map(order => order.supplier))];
    suppliers.value = uniqueSuppliers.map((name, index) => ({ id: index + 1, name }));
  } catch (err) {
    console.error('Error fetching suppliers:', err);
    // 如果 API 失敗，使用預設供應商
    suppliers.value = [
      { id: 1, name: '涂宸菱' },
      { id: 2, name: '黃彥銓' },
      { id: 3, name: '黃威朝' }
    ];
  }
};

// 監聽表單顯示狀態
watch(() => props.showForm, (newVal) => {
  if (newVal) {
    if (isEditing.value) {
      loadEditData();
    } else {
      resetForm();
      // 預設添加一個空白項目
      addItem();
    }
  }
});

// 設置今天日期
const getCurrentDate = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

onMounted(() => {
  fetchItems();
  fetchSuppliers();

  // 如果是新建，設置今天日期
  if (!isEditing.value) {
    form.value.purchase_date = getCurrentDate();
  }
});
</script>

<template>
  <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center border-b p-4">
        <h2 class="text-xl font-bold">{{ formTitle }}</h2>
        <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-4">
        <div v-if="error" class="mb-4 p-3 bg-red-100 text-red-700 rounded">
          {{ error }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">建單人員 <span class="text-red-500">*</span></label>
            <select v-model="form.supplier" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>選擇建單人員</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.name">
                {{ supplier.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">狀態</label>
            <select v-model="form.status" class="w-full border rounded px-3 py-2">
              <option value="pending">待處理</option>
              <option value="processing">處理中</option>
              <option value="completed">已完成</option>
              <option value="cancelled">已取消</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">進貨日期 <span class="text-red-500">*</span></label>
            <input
              type="date"
              v-model="form.purchase_date"
              class="w-full border rounded px-3 py-2"
              required
            />
          </div>

          <div v-if="isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">紀錄時間</label>
            <input
              type="text"
              :value="form.record_time"
              class="w-full border rounded px-3 py-2 bg-gray-100"
              readonly
            />
          </div>

          <div v-if="!isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">預計到貨日期</label>
            <input
              type="date"
              v-model="form.expected_delivery_date"
              class="w-full border rounded px-3 py-2"
            />
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-1">備註</label>
          <textarea
            v-model="form.notes"
            class="w-full border rounded px-3 py-2"
            rows="2"
          ></textarea>
        </div>

        <div class="mb-4">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-medium">商品項目</h3>
            <button
              type="button"
              @click="addItem"
              class="bg-[#19A2B3] hover:bg-[#158293] text-white py-1 px-3 rounded text-sm"
            >
              添加項目
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">數量</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">批號</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in form.items" :key="index">
                  <td class="px-4 py-2">
                    <select
                      v-model="item.item_id"
                      class="w-full border rounded px-2 py-1 bg-gray-100"
                      required
                      disabled
                    >
                      <option value="" disabled>選擇商品</option>
                      <option v-for="availableItem in availableItems" :key="availableItem.item_id" :value="availableItem.item_id">
                        {{ availableItem.item_name || availableItem.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-4 py-2">
                    <input
                      type="number"
                      v-model.number="item.quantity"
                      min="1"
                      class="w-20 border rounded px-2 py-1 bg-gray-100"
                      required
                      disabled
                    />
                  </td>
                  <td class="px-4 py-2">
                    <input
                      type="text"
                      v-model="item.item_batch"
                      class="w-24 border rounded px-2 py-1"
                      placeholder="輸入批號"
                    />
                  </td>
                  <td class="px-4 py-2">
                    <button
                      type="button"
                      @click="removeItem(index)"
                      class="text-red-600 hover:text-red-800"
                      :disabled="form.items.length === 1"
                      :class="{ 'opacity-50 cursor-not-allowed': form.items.length === 1 }"
                    >
                      刪除
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="flex justify-end space-x-2 pt-4 border-t">
          <button
            type="button"
            @click="emit('close')"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded"
            :disabled="loading"
          >
            取消
          </button>
          <button
            v-if="isEditing && purchaseOrder?.us_purchase_order_status === 'pending'"
            type="button"
            @click="generateUsPurchaseOrder"
            class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded"
            :disabled="loading"
          >
            自動產生美國進貨單
          </button>
          <button
            type="submit"
            class="bg-[#19A2B3] hover:bg-[#158293] text-white py-2 px-4 rounded"
            :disabled="loading"
          >
            {{ loading ? '處理中...' : '保存' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
