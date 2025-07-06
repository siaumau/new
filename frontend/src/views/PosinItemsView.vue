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
  router.go(-1)
}
</script>

<template>
  <div class="h-full w-full bg-[#f9fafb]">
    <!-- 頂部導航 -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center space-x-4">
        <button
          @click="goBack"
          class="flex items-center space-x-2 text-gray-600 hover:text-gray-800 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          <span>返回</span>
        </button>

        <div class="text-sm text-gray-500">
          <span>進貨單管理</span>
          <svg class="w-4 h-4 inline mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          <span>商品項目</span>
        </div>
      </div>

      <!-- 進貨單基本信息 -->
      <div v-if="posinInfo && !loading" class="mt-4 bg-gray-50 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">進貨單基本信息</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="font-medium text-gray-600">進貨單號:</span>
            <span class="ml-2 text-gray-800">{{ posinInfo.posin_sn }}</span>
          </div>
          <div>
            <span class="font-medium text-gray-600">建單人員:</span>
            <span class="ml-2 text-gray-800">{{ posinInfo.posin_user }}</span>
          </div>
          <div>
            <span class="font-medium text-gray-600">進貨日期:</span>
            <span class="ml-2 text-gray-800">{{ posinInfo.posin_dt ? new Date(posinInfo.posin_dt).toLocaleDateString('zh-TW') : '' }}</span>
          </div>
          <div>
            <span class="font-medium text-gray-600">項目數量:</span>
            <span class="ml-2 text-gray-800">{{ posinInfo.posin_items?.length || 0 }} 項</span>
          </div>
        </div>
        <div v-if="posinInfo.posin_note" class="mt-2">
          <span class="font-medium text-gray-600">備註:</span>
          <span class="ml-2 text-gray-800">{{ posinInfo.posin_note }}</span>
        </div>
      </div>

      <!-- 載入中狀態 -->
      <div v-if="loading" class="mt-4 text-center text-gray-500">
        載入中...
      </div>
    </div>

    <!-- 商品項目表格 -->
    <div class="p-6">
      <PosinItemsTable v-if="posinId" :posinId="posinId" />
    </div>
  </div>
</template>

<style scoped>
/* 自定義樣式 */
</style>
