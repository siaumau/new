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
    const formData = {
      ...form.value,
      purchase_date: form.value.purchase_date,
      expected_delivery_date: form.value.expected_delivery_date,
    };

    let response;

    if (isEditing.value) {
      // 更新現有訂單
      response = await axios.put(`/api/v1/purchase-orders/${props.purchaseOrder.id}`, formData);
    } else {
      // 創建新訂單
      response = await axios.post('/api/v1/purchase-orders', formData);
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

// 表單驗證
const validateForm = () => {
  // 驗證必填欄位
  if (!form.value.supplier) {
    alert('請選擇供應商');
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

    if (!item.price || item.price < 0) {
      alert('所有項目的價格不能為負數');
      return false;
    }
  }

  return true;
};

// 添加新商品項目
const addItem = () => {
  form.value.items.push({
    item_id: '',
    quantity: 1,
    price: 0,
    subtotal: 0
  });
};

// 移除商品項目
const removeItem = (index) => {
  form.value.items.splice(index, 1);
};

// 計算小計
const calculateSubtotal = (item) => {
  return item.quantity * item.price;
};

// 監聽數量和價格變化以更新小計
const updateSubtotal = (item) => {
  item.subtotal = calculateSubtotal(item);
};

// 計算總金額
const totalAmount = computed(() => {
  return form.value.items.reduce((total, item) => {
    return total + (item.subtotal || 0);
  }, 0);
});

// 重置表單
const resetForm = () => {
  form.value = {
    supplier: '',
    purchase_date: '',
    expected_delivery_date: '',
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
      status: props.purchaseOrder.status,
      notes: props.purchaseOrder.notes || '',
      items: (props.purchaseOrder.items || []).map(item => ({
        item_id: item.item_id,
        quantity: item.quantity,
        price: item.price,
        subtotal: item.subtotal || calculateSubtotal(item)
      }))
    };
  }
};

// 載入商品和供應商列表
const fetchItems = async () => {
  try {
    const response = await axios.get('/api/v1/items');
    availableItems.value = response.data.data || response.data;
  } catch (err) {
    console.error('Error fetching items:', err);
  }
};

const fetchSuppliers = async () => {
  try {
    // 這裡需要調整為實際的後端API端點
    const response = await axios.get('/api/v1/suppliers');
    suppliers.value = response.data.data || response.data;
  } catch (err) {
    console.error('Error fetching suppliers:', err);
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
            <label class="block text-sm font-medium text-gray-700 mb-1">供應商 <span class="text-red-500">*</span></label>
            <select v-model="form.supplier" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>選擇供應商</option>
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

          <div>
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
              class="bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded text-sm"
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
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">單價</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">小計</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in form.items" :key="index">
                  <td class="px-4 py-2">
                    <select
                      v-model="item.item_id"
                      class="w-full border rounded px-2 py-1"
                      required
                    >
                      <option value="" disabled>選擇商品</option>
                      <option v-for="availableItem in availableItems" :key="availableItem.id" :value="availableItem.id">
                        {{ availableItem.name }}
                      </option>
                    </select>
                  </td>
                  <td class="px-4 py-2">
                    <input
                      type="number"
                      v-model.number="item.quantity"
                      @input="updateSubtotal(item)"
                      min="1"
                      class="w-20 border rounded px-2 py-1"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    <input
                      type="number"
                      v-model.number="item.price"
                      @input="updateSubtotal(item)"
                      min="0"
                      step="0.01"
                      class="w-24 border rounded px-2 py-1"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    {{ item.subtotal ? item.subtotal.toFixed(2) : '0.00' }}
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
              <tfoot>
                <tr>
                  <td colspan="3" class="px-4 py-2 text-right font-medium">總金額：</td>
                  <td class="px-4 py-2 font-bold">${{ totalAmount.toFixed(2) }}</td>
                  <td></td>
                </tr>
              </tfoot>
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
            type="submit"
            class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded"
            :disabled="loading"
          >
            {{ loading ? '處理中...' : '保存' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
