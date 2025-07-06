<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PosinItemsTable from '../components/PosinItemsTable.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const posinId = ref(null)
const posinInfo = ref(null)
const loading = ref(false)

// 從路由參數獲取進貨單ID
onMounted(() => {
  posinId.value = route.params.id ? parseInt(route.params.id) : null
  if (posinId.value) {
    fetchPosinInfo()
  }
})

const fetchPosinInfo = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/v1/posin/${posinId.value}`)
    posinInfo.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching posin info:', error)
    // 如果 API 失敗，使用模擬數據
    posinInfo.value = {
      posin_id: posinId.value,
      posin_sn: '2025-05-05 [005]',
      posin_user: '涂宸菱',
      posin_dt: '2025-05-05',
      posin_note: 'PCT342A海運',
      posin_items: []
    }
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/purchase-orders')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頂部導航 -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center space-x-4">
            <button
              @click="goBack"
              class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              <span class="font-medium">返回進貨單列表</span>
            </button>

            <div class="flex items-center text-sm text-gray-500">
              <span>進貨單管理</span>
              <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-gray-900 font-medium">商品項目</span>
            </div>
          </div>
        </div>

        <!-- 進貨單基本信息 -->
        <div v-if="posinInfo && !loading" class="pb-6">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">進貨單基本信息</h2>
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                  ID: {{ posinInfo.posin_id }}
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="text-sm font-medium text-gray-600 mb-1">進貨單號</div>
                <div class="text-lg font-semibold text-gray-900">{{ posinInfo.posin_sn }}</div>
              </div>

              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="text-sm font-medium text-gray-600 mb-1">建單人員</div>
                <div class="text-lg font-semibold text-gray-900">{{ posinInfo.posin_user }}</div>
              </div>

              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="text-sm font-medium text-gray-600 mb-1">進貨日期</div>
                <div class="text-lg font-semibold text-gray-900">
                  {{ posinInfo.posin_dt ? new Date(posinInfo.posin_dt).toLocaleDateString('zh-TW') : '' }}
                </div>
              </div>

              <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="text-sm font-medium text-gray-600 mb-1">項目數量</div>
                <div class="text-lg font-semibold text-indigo-600">{{ posinInfo.posin_items?.length || 0 }} 項</div>
              </div>
            </div>

            <div v-if="posinInfo.posin_note" class="mt-4 bg-white rounded-lg p-4 shadow-sm">
              <div class="text-sm font-medium text-gray-600 mb-1">備註</div>
              <div class="text-gray-900">{{ posinInfo.posin_note }}</div>
            </div>
          </div>
        </div>

        <!-- 載入中狀態 -->
        <div v-if="loading" class="text-center py-8">
          <div class="inline-flex items-center">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">載入中...</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 商品項目表格 -->
    <PosinItemsTable v-if="posinId" :posinId="posinId" />
  </div>
</template>

<style scoped>
/* 自定義樣式 */
.transition-colors {
  transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

button:hover {
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
