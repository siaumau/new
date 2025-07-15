<template>
  <div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
      <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-teal-600">產品管理</h1>
      </div>
    </div>

    <div class="px-6 py-4">
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-teal-600 text-white">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">產品編號</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">產品名稱</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">庫存</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.code }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.stock }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="showProductDetails(product)" class="text-teal-600 hover:text-teal-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">產品詳細資訊</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div v-if="selectedProduct" class="space-y-2">
          <p><strong>產品編號:</strong> {{ selectedProduct.code }}</p>
          <p><strong>產品名稱:</strong> {{ selectedProduct.name }}</p>
          <p><strong>庫存:</strong> {{ selectedProduct.stock }}</p>
          <p><strong>描述:</strong> {{ selectedProduct.description }}</p>
          <!-- Add more product details here -->
        </div>
        <div class="flex justify-end mt-6">
          <button @click="closeModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">關閉</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const products = ref([]);
const showDetailsModal = ref(false);
const selectedProduct = ref(null);
const apiUrl = import.meta.env.VITE_APP_URL;

const fetchProducts = async () => {
  try {
    // 假設產品API端點為 /api/v1/products
    const response = await fetch(`${apiUrl}/api/v1/products`);
    if (!response.ok) {
      throw new Error('Failed to fetch products');
    }
    const data = await response.json();
    products.value = data.data || data; 
  } catch (error) {
    console.error('Error fetching products:', error);
  }
};

const showProductDetails = (product) => {
  selectedProduct.value = product;
  showDetailsModal.value = true;
};

const closeModal = () => {
  showDetailsModal.value = false;
  selectedProduct.value = null;
};

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
/* Add any component specific styles here */
</style>
