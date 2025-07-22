<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
      <!-- 標題 -->
      <div class="flex justify-between items-center mb-6 pb-4 border-b">
        <div>
          <h3 class="text-xl font-bold text-gray-900">{{ item.item_name }}</h3>
          <p class="text-sm text-gray-600">商品序號: {{ item.item_sn }} | 規格: {{ item.item_spec }}</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 左側：基本資訊和統計 -->
        <div class="space-y-6">
          <!-- 基本資訊 -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">基本資訊</h4>
            <dl class="grid grid-cols-1 gap-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">商品ID</dt>
                <dd class="text-sm text-gray-900">{{ item.item_id }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">商品名稱</dt>
                <dd class="text-sm text-gray-900">{{ item.item_name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">商品序號</dt>
                <dd class="text-sm text-gray-900">{{ item.item_sn }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">規格</dt>
                <dd class="text-sm text-gray-900">{{ item.item_spec }}</dd>
              </div>
              <div v-if="item.item_inbox">
                <dt class="text-sm font-medium text-gray-500">箱入數</dt>
                <dd class="text-sm text-gray-900">{{ item.item_inbox }} 個/箱</dd>
              </div>
            </dl>
          </div>

          <!-- 庫存統計 -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">庫存統計</h4>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ totalQuantity }}</div>
                <div class="text-sm text-gray-500">總庫存</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ totalBatches }}</div>
                <div class="text-sm text-gray-500">批號數量</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ totalLocations }}</div>
                <div class="text-sm text-gray-500">存放位置</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold" :class="expiryStatusColor">{{ nearestExpiryDays }}</div>
                <div class="text-sm text-gray-500">最近到期</div>
              </div>
            </div>
          </div>

          <!-- 到期警告 -->
          <div v-if="item.nearestExpiry" class="rounded-lg p-4" :class="getExpiryAlertClass(item.nearestExpiry.days_until_expiry)">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <div>
                <h4 class="font-medium">
                  {{ item.nearestExpiry.days_until_expiry > 0 ? '到期提醒' : '已過期警告' }}
                </h4>
                <p class="text-sm">
                  {{ item.nearestExpiry.days_until_expiry > 0 ? 
                      `此商品將在 ${item.nearestExpiry.days_until_expiry} 天後到期` : 
                      `此商品已過期 ${Math.abs(item.nearestExpiry.days_until_expiry)} 天` }}
                </p>
                <p class="text-xs mt-1">
                  到期日: {{ formatDate(item.nearestExpiry.expiry_date) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 右側：詳細清單 -->
        <div class="space-y-6">
          <!-- 批號詳細資訊 -->
          <div class="bg-white border rounded-lg">
            <div class="px-4 py-3 border-b bg-gray-50">
              <h4 class="text-lg font-semibold text-gray-900">批號詳細資訊</h4>
            </div>
            <div class="p-4">
              <div v-if="item.batchSummary && item.batchSummary.length > 0" class="space-y-3">
                <div v-for="batch in item.batchSummary" :key="batch.batch" 
                     class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="font-medium text-gray-900">批號: {{ batch.batch }}</div>
                      <div class="text-sm text-gray-500 mt-1">
                        到期日: {{ formatDate(batch.expiry_date) }}
                      </div>
                      <div class="text-sm text-gray-500">
                        剩餘天數: {{ calculateDaysUntilExpiry(batch.expiry_date) }} 天
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                            :class="getBatchStatusClass(batch.quantity)">
                        {{ batch.quantity }} 個
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center text-gray-500 py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">沒有批號資料</h3>
                <p class="mt-1 text-sm text-gray-500">此商品尚未掃描入庫</p>
              </div>
            </div>
          </div>

          <!-- 位置分佈 -->
          <div class="bg-white border rounded-lg">
            <div class="px-4 py-3 border-b bg-gray-50">
              <h4 class="text-lg font-semibold text-gray-900">存放位置</h4>
            </div>
            <div class="p-4">
              <div v-if="item.locations && item.locations.length > 0" class="space-y-3">
                <div v-for="location in item.locations" :key="`${location.location}-${location.batch}`" 
                     class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                  <div class="flex justify-between items-center">
                    <div class="flex-1">
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                          {{ location.location }}
                        </span>
                        <span class="text-sm text-gray-500">批號: {{ location.batch }}</span>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="text-sm font-medium text-gray-900">{{ location.quantity }} 個</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center text-gray-500 py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">沒有位置資料</h3>
                <p class="mt-1 text-sm text-gray-500">此商品尚未指定存放位置</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 底部操作按鈕 -->
      <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          關閉
        </button>
        <button
          v-if="item.nearestExpiry && item.nearestExpiry.days_until_expiry <= 180"
          @click="sendExpiryAlert"
          class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
        >
          發送到期提醒
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import axios from 'axios'

// Props
const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close'])

// Computed properties
const totalQuantity = computed(() => {
  if (!props.item.batchSummary) return 0
  return props.item.batchSummary.reduce((sum, batch) => sum + batch.quantity, 0)
})

const totalBatches = computed(() => {
  return props.item.batchSummary ? props.item.batchSummary.length : 0
})

const totalLocations = computed(() => {
  return props.item.locations ? props.item.locations.length : 0
})

const nearestExpiryDays = computed(() => {
  if (!props.item.nearestExpiry) return 'N/A'
  const days = props.item.nearestExpiry.days_until_expiry
  return days > 0 ? `${days} 天` : days === 0 ? '今天' : `已過期`
})

const expiryStatusColor = computed(() => {
  if (!props.item.nearestExpiry) return 'text-gray-500'
  const days = props.item.nearestExpiry.days_until_expiry
  if (days <= 0) return 'text-red-600'
  if (days <= 90) return 'text-red-600'
  if (days <= 180) return 'text-yellow-600'
  return 'text-green-600'
})

// Methods
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric'
  })
}

const calculateDaysUntilExpiry = (expiryDate) => {
  if (!expiryDate) return 'N/A'
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffTime = expiry - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays
}

const getBatchStatusClass = (quantity) => {
  if (quantity === 0) return 'bg-red-100 text-red-800'
  if (quantity <= 10) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getExpiryAlertClass = (daysUntilExpiry) => {
  if (daysUntilExpiry <= 0) return 'bg-red-50 border border-red-200 text-red-800'
  if (daysUntilExpiry <= 90) return 'bg-red-50 border border-red-200 text-red-800'
  if (daysUntilExpiry <= 180) return 'bg-yellow-50 border border-yellow-200 text-yellow-800'
  return 'bg-green-50 border border-green-200 text-green-800'
}

const sendExpiryAlert = async () => {
  try {
    const alertData = {
      item: props.item,
      alert_type: 'expiry_warning',
      message: `商品 ${props.item.item_name} (${props.item.item_sn}) 即將於 ${props.item.nearestExpiry.days_until_expiry} 天後到期`
    }
    
    await axios.post('/api/v1/send-teams-alert', alertData)
    alert('到期提醒已發送到Teams!')
  } catch (error) {
    console.error('Error sending alert:', error)
    alert('發送提醒時發生錯誤')
  }
}
</script>

<style scoped>
/* Modal specific styles */
</style>