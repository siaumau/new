<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頁面標題和設定區域 -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">商品庫存管理</h1>
            <p class="mt-1 text-sm text-gray-600">基於QR碼掃描的庫存追蹤與短效期商品監控</p>
          </div>
          <div class="flex space-x-3">
            <!-- 短效期設定按鈕 -->
            <button
              @click="openExpirySettings"
              class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>短效期設定</span>
            </button>
            
            <!-- Teams通知設定 -->
            <button
              @click="openTeamsSettings"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 1.5 17v-8A2.5 2.5 0 0 1 4 6.5h16a2.5 2.5 0 0 1 2.5 2.5v8a2.5 2.5 0 0 1-2.5 2.5H4z" />
              </svg>
              <span>Teams通知</span>
            </button>
            
            <!-- 重新整理 -->
            <button
              @click="refreshData"
              class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>重新整理</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 主要內容區域 -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- 統計卡片 -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">總商品數</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.totalItems }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">有庫存</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.inStockItems }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">即將到期</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.expiringItems }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">已過期</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ stats.expiredItems }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 搜尋和篩選 -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">搜尋商品</label>
            <input
              v-model="searchTerm"
              type="text"
              placeholder="商品名稱、序號、批號..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">庫存狀態</label>
            <select
              v-model="stockFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">全部</option>
              <option value="in_stock">有庫存</option>
              <option value="low_stock">低庫存</option>
              <option value="out_of_stock">缺貨</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">到期狀態</label>
            <select
              v-model="expiryFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">全部</option>
              <option value="expiring_soon">即將到期</option>
              <option value="expired">已過期</option>
              <option value="normal">正常</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">貨架位置</label>
            <select
              v-model="locationFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">全部位置</option>
              <option v-for="location in availableLocations" :key="location" :value="location">
                {{ location }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- 商品列表 -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- 表格標題 -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">商品清單</h2>
          <p class="text-sm text-gray-600 mt-1">共 {{ filteredItems.length }} 項商品</p>
        </div>

        <!-- 載入中狀態 -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">載入中...</span>
        </div>

        <!-- 商品表格 -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  商品資訊
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  批號統計
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  庫存位置
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  到期狀態
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  操作
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in filteredItems" :key="item.item_id" class="hover:bg-gray-50 transition-colors">
                <!-- 商品資訊 -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                      <div class="text-sm text-gray-500">序號: {{ item.item_sn }}</div>
                      <div class="text-sm text-gray-500">規格: {{ item.item_spec }}</div>
                    </div>
                  </div>
                </td>

                <!-- 批號統計 -->
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    <div v-for="batch in item.batchSummary" :key="batch.batch" class="text-sm">
                      <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-900">{{ batch.batch }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getBatchStatusClass(batch.quantity)">
                          {{ batch.quantity }} 個
                        </span>
                      </div>
                      <div class="text-xs text-gray-500">
                        到期: {{ formatDate(batch.expiry_date) }}
                      </div>
                    </div>
                    <div v-if="item.batchSummary.length === 0" class="text-sm text-gray-400">
                      無庫存資料
                    </div>
                  </div>
                </td>

                <!-- 庫存位置 -->
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    <div v-for="location in item.locations" :key="location.location" class="text-sm">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ location.location }}
                      </span>
                      <div class="text-xs text-gray-500 mt-1">
                        數量: {{ location.quantity }} | 批號: {{ location.batch }}
                      </div>
                    </div>
                    <div v-if="item.locations.length === 0" class="text-sm text-gray-400">
                      未掃描位置
                    </div>
                  </div>
                </td>

                <!-- 到期狀態 -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <span v-if="item.nearestExpiry" 
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getExpiryStatusClass(item.nearestExpiry.days_until_expiry)">
                      {{ item.nearestExpiry.days_until_expiry > 0 ? 
                          `${item.nearestExpiry.days_until_expiry} 天後到期` : 
                          `已過期 ${Math.abs(item.nearestExpiry.days_until_expiry)} 天` }}
                    </span>
                    <div v-if="item.nearestExpiry" class="text-xs text-gray-500">
                      {{ formatDate(item.nearestExpiry.expiry_date) }}
                    </div>
                    <div v-if="!item.nearestExpiry" class="text-sm text-gray-400">
                      無到期資料
                    </div>
                  </div>
                </td>

                <!-- 操作 -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="viewItemDetail(item)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="檢視詳細資訊"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    
                    <button
                      @click="editItem(item)"
                      class="text-yellow-600 hover:text-yellow-900 transition-colors"
                      title="編輯商品"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    
                    <button
                      v-if="item.nearestExpiry && item.nearestExpiry.days_until_expiry <= expirySettings.warningDays"
                      @click="sendExpiryAlert(item)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="發送到期提醒"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空狀態 -->
        <div v-if="!loading && filteredItems.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">沒有找到符合條件的商品</h3>
          <p class="mt-1 text-sm text-gray-500">請嘗試調整搜尋條件或篩選器</p>
        </div>
      </div>
    </div>

    <!-- 短效期設定彈窗 -->
    <ExpirySettingsModal
      v-if="showExpirySettings"
      :settings="expirySettings"
      @save="saveExpirySettings"
      @close="showExpirySettings = false"
    />

    <!-- Teams通知設定彈窗 -->
    <TeamsSettingsModal
      v-if="showTeamsSettings"
      :settings="teamsSettings"
      @save="saveTeamsSettings"
      @close="showTeamsSettings = false"
    />

    <!-- 商品詳細資訊彈窗 -->
    <ItemDetailModal
      v-if="showItemDetail"
      :item="selectedItem"
      @close="showItemDetail = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import ExpirySettingsModal from './ExpirySettingsModal.vue'
import TeamsSettingsModal from './TeamsSettingsModal.vue'
import ItemDetailModal from './ItemDetailModal.vue'

// Reactive data
const loading = ref(false)
const items = ref([])
const searchTerm = ref('')
const stockFilter = ref('')
const expiryFilter = ref('')
const locationFilter = ref('')
const availableLocations = ref([])

// Modal states
const showExpirySettings = ref(false)
const showTeamsSettings = ref(false)
const showItemDetail = ref(false)
const selectedItem = ref(null)

// Settings
const expirySettings = ref({
  warningDays: 180, // 6個月
  criticalDays: 90,  // 3個月
  enableAutoNotification: true,
  notificationTime: '09:00'
})

const teamsSettings = ref({
  webhookUrl: '',
  channelName: '',
  enableNotifications: true,
  notificationTypes: {
    expiring: true,
    expired: true,
    lowStock: true
  }
})

// Computed properties
const stats = computed(() => {
  const totalItems = items.value.length
  const inStockItems = items.value.filter(item => 
    item.batchSummary && item.batchSummary.some(batch => batch.quantity > 0)
  ).length
  
  const expiringItems = items.value.filter(item => 
    item.nearestExpiry && 
    item.nearestExpiry.days_until_expiry <= expirySettings.value.warningDays &&
    item.nearestExpiry.days_until_expiry > 0
  ).length
  
  const expiredItems = items.value.filter(item => 
    item.nearestExpiry && item.nearestExpiry.days_until_expiry <= 0
  ).length

  return {
    totalItems,
    inStockItems,
    expiringItems,
    expiredItems
  }
})

const filteredItems = computed(() => {
  let filtered = items.value

  // 搜尋篩選
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(item => 
      item.item_name.toLowerCase().includes(term) ||
      item.item_sn.toLowerCase().includes(term) ||
      (item.batchSummary && item.batchSummary.some(batch => 
        batch.batch.toLowerCase().includes(term)
      ))
    )
  }

  // 庫存狀態篩選
  if (stockFilter.value) {
    filtered = filtered.filter(item => {
      const totalStock = item.batchSummary ? 
        item.batchSummary.reduce((sum, batch) => sum + batch.quantity, 0) : 0
      
      switch (stockFilter.value) {
        case 'in_stock':
          return totalStock > 0
        case 'low_stock':
          return totalStock > 0 && totalStock <= 10 // 假設低庫存為10以下
        case 'out_of_stock':
          return totalStock === 0
        default:
          return true
      }
    })
  }

  // 到期狀態篩選
  if (expiryFilter.value) {
    filtered = filtered.filter(item => {
      if (!item.nearestExpiry) return expiryFilter.value === 'normal'
      
      const daysUntilExpiry = item.nearestExpiry.days_until_expiry
      
      switch (expiryFilter.value) {
        case 'expiring_soon':
          return daysUntilExpiry <= expirySettings.value.warningDays && daysUntilExpiry > 0
        case 'expired':
          return daysUntilExpiry <= 0
        case 'normal':
          return daysUntilExpiry > expirySettings.value.warningDays
        default:
          return true
      }
    })
  }

  // 位置篩選
  if (locationFilter.value) {
    filtered = filtered.filter(item => 
      item.locations && item.locations.some(loc => 
        loc.location === locationFilter.value
      )
    )
  }

  return filtered
})

// Methods
const fetchItems = async () => {
  loading.value = true
  try {
    // 獲取商品基本資料
    const itemsResponse = await axios.get('/api/v1/items')
    
    // 獲取QR碼掃描資料並整合
    const qrDataResponse = await axios.get('/api/v1/qr-inventory-summary')
    
    // 整合資料
    const itemsData = itemsResponse.data.data || itemsResponse.data
    const qrData = qrDataResponse.data.data || qrDataResponse.data
    
    // 為每個商品加上庫存和位置資訊
    items.value = itemsData.map(item => {
      const itemQrData = qrData.filter(qr => qr.item_sn === item.item_sn)
      
      // 批號統計
      const batchMap = new Map()
      const locationMap = new Map()
      
      itemQrData.forEach(qr => {
        // 批號統計
        const batchKey = qr.item_batch
        if (batchMap.has(batchKey)) {
          const existing = batchMap.get(batchKey)
          existing.quantity += parseInt(qr.quantity || 1)
        } else {
          batchMap.set(batchKey, {
            batch: qr.item_batch,
            quantity: parseInt(qr.quantity || 1),
            expiry_date: qr.item_expireday
          })
        }
        
        // 位置統計
        if (qr.location) {
          const locationKey = `${qr.location}-${qr.item_batch}`
          if (locationMap.has(locationKey)) {
            const existing = locationMap.get(locationKey)
            existing.quantity += parseInt(qr.quantity || 1)
          } else {
            locationMap.set(locationKey, {
              location: qr.location,
              batch: qr.item_batch,
              quantity: parseInt(qr.quantity || 1)
            })
          }
        }
      })
      
      const batchSummary = Array.from(batchMap.values())
      const locations = Array.from(locationMap.values())
      
      // 計算最近到期日
      let nearestExpiry = null
      if (batchSummary.length > 0) {
        const sortedBatches = batchSummary
          .filter(batch => batch.expiry_date)
          .sort((a, b) => new Date(a.expiry_date) - new Date(b.expiry_date))
        
        if (sortedBatches.length > 0) {
          const expiryDate = new Date(sortedBatches[0].expiry_date)
          const today = new Date()
          const diffTime = expiryDate - today
          const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
          
          nearestExpiry = {
            expiry_date: sortedBatches[0].expiry_date,
            days_until_expiry: diffDays
          }
        }
      }
      
      return {
        ...item,
        batchSummary,
        locations,
        nearestExpiry
      }
    })
    
    // 更新可用位置列表
    const allLocations = new Set()
    items.value.forEach(item => {
      if (item.locations) {
        item.locations.forEach(loc => allLocations.add(loc.location))
      }
    })
    availableLocations.value = Array.from(allLocations).sort()
    
  } catch (error) {
    console.error('Error fetching items:', error)
    // 如果API失敗，使用示例數據
    items.value = []
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  fetchItems()
}

const openExpirySettings = () => {
  showExpirySettings.value = true
}

const openTeamsSettings = () => {
  showTeamsSettings.value = true
}

const saveExpirySettings = async (settings) => {
  try {
    await axios.post('/api/v1/expiry-settings', settings)
    expirySettings.value = { ...settings }
    showExpirySettings.value = false
    
    // 重新整理數據以更新到期狀態
    fetchItems()
    
    alert('短效期設定已儲存')
  } catch (error) {
    console.error('Error saving expiry settings:', error)
    alert('儲存設定時發生錯誤')
  }
}

const saveTeamsSettings = async (settings) => {
  try {
    await axios.post('/api/v1/teams-settings', settings)
    teamsSettings.value = { ...settings }
    showTeamsSettings.value = false
    
    alert('Teams通知設定已儲存')
  } catch (error) {
    console.error('Error saving Teams settings:', error)
    alert('儲存設定時發生錯誤')
  }
}

const viewItemDetail = (item) => {
  selectedItem.value = item
  showItemDetail.value = true
}

const editItem = (item) => {
  // 這裡可以導航到編輯頁面或開啟編輯模態框
  console.log('Edit item:', item)
  // router.push(`/items/${item.item_id}/edit`)
}

const sendExpiryAlert = async (item) => {
  try {
    const alertData = {
      item: item,
      alert_type: 'expiry_warning',
      message: `商品 ${item.item_name} (${item.item_sn}) 即將於 ${item.nearestExpiry.days_until_expiry} 天後到期`
    }
    
    await axios.post('/api/v1/send-teams-alert', alertData)
    alert('到期提醒已發送到Teams!')
  } catch (error) {
    console.error('Error sending alert:', error)
    alert('發送提醒時發生錯誤')
  }
}

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric'
  })
}

const getBatchStatusClass = (quantity) => {
  if (quantity === 0) return 'bg-red-100 text-red-800'
  if (quantity <= 10) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getExpiryStatusClass = (daysUntilExpiry) => {
  if (daysUntilExpiry <= 0) return 'bg-red-100 text-red-800'
  if (daysUntilExpiry <= expirySettings.value.criticalDays) return 'bg-red-100 text-red-800'
  if (daysUntilExpiry <= expirySettings.value.warningDays) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

// 載入設定
const loadSettings = async () => {
  try {
    const [expiryRes, teamsRes] = await Promise.all([
      axios.get('/api/v1/expiry-settings'),
      axios.get('/api/v1/teams-settings')
    ])
    
    if (expiryRes.data) {
      expirySettings.value = { ...expirySettings.value, ...expiryRes.data }
    }
    
    if (teamsRes.data) {
      teamsSettings.value = { ...teamsSettings.value, ...teamsRes.data }
    }
  } catch (error) {
    console.error('Error loading settings:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadSettings()
  fetchItems()
})
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
