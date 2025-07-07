<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頁面標題區域 -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $t('posinItems.title') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $t('posinItems.messages.pageDescription') }}</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="convertToUsPurchaseOrder"
              class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ $t('posinItems.actions.convertToUsPurchaseOrder') }}</span>
            </button>
            <button
              @click="refreshData"
              class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>{{ $t('posinItems.actions.refresh') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 主要內容區域 -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- 載入中狀態 -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">{{ $t('posinItems.messages.loading') }}</span>
      </div>

      <!-- 商品項目表格 -->
      <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- 表格標題 -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">{{ $t('posinItems.messages.itemList') }}</h2>
          <p class="text-sm text-gray-600 mt-1">{{ $t('posinItems.messages.totalItems', { count: posinItems.length }) }}</p>
        </div>

        <!-- 表格內容 -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.itemSN') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.itemName') }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.spec') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.quantity') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.packageSpec') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.boxCount') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.expiryDate') }}
                </th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ $t('posinItems.table.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in posinItems" :key="item.posinitem_id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ item.item_sn }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                  <div class="text-sm text-gray-500">批號: {{ item.item_batch }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ item.item_spec }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="text-sm font-semibold text-gray-900">{{ item.item_count }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                  {{ getPackageSpec(item) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ getBoxCount(item) }} 箱
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                  {{ formatDate(item.item_expireday) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex justify-center space-x-2">
                    <button
                      v-if="!isQRGenerated(item)"
                      @click="openQRModal(item)"
                      class="bg-green-500 hover:bg-green-600 text-white font-medium py-1.5 px-3 rounded text-xs transition-colors shadow-sm"
                      :title="$t('posinItems.actions.generateQR')"
                    >
                      {{ $t('posinItems.table.generateQR') }}
                    </button>
                    <button
                      v-else
                      disabled
                      class="bg-gray-400 text-white font-medium py-1.5 px-3 rounded text-xs cursor-not-allowed"
                      :title="'QR Code 已生成'"
                    >
                      已生成QR
                    </button>
                    <button
                      v-if="!isQRGenerated(item)"
                      @click="deleteItem(item)"
                      class="bg-red-500 hover:bg-red-600 text-white font-medium py-1.5 px-3 rounded text-xs transition-colors shadow-sm"
                      :title="$t('posinItems.actions.deleteItem')"
                    >
                      {{ $t('posinItems.table.delete') }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空狀態 -->
        <div v-if="posinItems.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('posinItems.messages.noItems') }}</h3>
          <p class="mt-1 text-sm text-gray-500">{{ $t('posinItems.messages.noItemsDescription') }}</p>
        </div>
      </div>
    </div>

    <!-- QR Code 生成彈出窗口 -->
    <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- 彈窗標題 -->
        <div class="flex justify-between items-center border-b border-gray-200 p-6">
          <h2 class="text-2xl font-bold text-gray-900">{{ $t('posinItems.qrCode.title') }}</h2>
          <button @click="closeQRModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6">
          <!-- 商品詳細資訊 -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6 border border-blue-200">
            <h3 class="font-bold text-xl mb-4 text-gray-900">{{ selectedItem?.item_name }}</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.itemSN') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ selectedItem?.item_sn }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.batch') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ selectedItem?.item_batch }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.spec') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ selectedItem?.item_spec }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.quantity') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ selectedItem?.item_count }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.packageSpec') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ getPackageSpec(selectedItem) }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.boxCount') }}</span>
                <div class="text-lg font-semibold text-blue-600 mt-1">{{ getBoxCount(selectedItem) }} 箱</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.purchaseDate') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ formatPurchaseDate(selectedItem) }}</div>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm">
                <span class="font-medium text-gray-600">{{ $t('posinItems.qrCode.expiryDate') }}</span>
                <div class="text-lg font-semibold text-gray-900 mt-1">{{ formatDate(selectedItem?.item_expireday) }}</div>
              </div>
            </div>
          </div>

          <!-- QR Code 生成設定 -->
          <div class="border-t border-gray-200 pt-6">
            <div class="bg-white rounded-lg p-6 border border-gray-200">
              <h4 class="font-bold text-xl mb-4 text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.99c.28 0 .5.22.5.5s-.22.5-.5.5H12m0 0h.01" />
                </svg>
                {{ $t('posinItems.qrCode.boxQRTitle') }}
              </h4>

              <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                <p class="text-sm text-blue-800 mb-2">
                  {{ $t('posinItems.qrCode.description', { count: getBoxCount(selectedItem) }) }}
                </p>
                <p class="text-sm text-blue-700 mb-2">{{ $t('posinItems.qrCode.labelInfo') }}</p>
                <p class="text-sm text-blue-700">
                  {{ $t('posinItems.qrCode.codeFormat', { example: getExampleCode(selectedItem) }) }}
                </p>
              </div>

              <div class="flex items-center space-x-4 mb-6">
                <label class="font-medium text-gray-700">{{ $t('posinItems.qrCode.generateCount') }}</label>
                <input
                  v-model="qrGenerateCount"
                  type="number"
                  min="1"
                  :max="getBoxCount(selectedItem)"
                  class="border border-gray-300 rounded-lg px-3 py-2 w-20 text-center focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <span class="text-sm text-gray-600">{{ $t('posinItems.qrCode.generateCountUnit') }}</span>
              </div>

              <div class="flex flex-wrap gap-3">
                <button
                  @click="downloadQRLabels"
                  class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <span>{{ $t('posinItems.qrCode.download', { count: qrGenerateCount }) }}</span>
                </button>
                <button
                  @click="previewLabels"
                  class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span>{{ $t('posinItems.qrCode.preview') }}</span>
                </button>
                <button
                  @click="closeQRModal"
                  class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-lg transition-colors"
                >
                  {{ $t('posinItems.qrCode.cancel') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 預覽彈窗 -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- 預覽彈窗標題 -->
        <div class="flex justify-between items-center border-b border-gray-200 p-6">
          <h2 class="text-2xl font-bold text-gray-900">外箱 QR Code 標籤預覽</h2>
          <button @click="closePreviewModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- 預覽內容 -->
        <div class="p-6">
          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">商品資訊</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p><strong>商品名稱:</strong> {{ selectedItem?.item_name }}</p>
              <p><strong>商品序號:</strong> {{ selectedItem?.item_sn }}</p>
              <p><strong>批號:</strong> {{ selectedItem?.item_batch }}</p>
              <p><strong>規格:</strong> {{ selectedItem?.item_spec }}</p>
              <p><strong>總箱數:</strong> {{ qrGenerateCount }} 箱</p>
              <p><strong>有效期限:</strong> {{ formatDate(selectedItem?.item_expireday) }}</p>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">QR Code 標籤預覽 (顯示前 {{ Math.min(qrGenerateCount, 5) }} 張)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="qrCode in previewQRCodes"
                :key="qrCode.serial"
                class="border border-gray-200 rounded-lg p-4 text-center"
              >
                <div class="mb-3">
                  <img :src="qrCode.image" :alt="`QR Code ${qrCode.serial}`" class="mx-auto w-32 h-32" />
                </div>
                <div class="text-sm text-gray-600">
                  <p><strong>{{ qrCode.item_info.item_name }}</strong></p>
                  <p>商品序號: {{ qrCode.item_info.item_sn }}</p>
                  <p>規格: {{ qrCode.item_info.item_spec }}</p>
                  <p>批號: {{ qrCode.item_info.item_batch }}</p>
                  <p>每箱: {{ qrCode.item_info.item_inbox }}個</p>
                  <p>有效期限: {{ formatDate(qrCode.item_info.item_expireday) }}</p>
                  <p>編碼: {{ qrCode.data }}</p>
                  <p>標籤: {{ qrGenerateCount }}箱之{{ qrCode.serial }}</p>
                  <p v-if="qrCode.item_info.posin_note">備註: {{ qrCode.item_info.posin_note }}</p>
                </div>
              </div>
            </div>

            <div v-if="qrGenerateCount > 5" class="mt-4 text-center text-gray-500">
              <p>還有 {{ qrGenerateCount - 5 }} 張標籤未顯示...</p>
            </div>
          </div>

          <!-- 預覽彈窗按鈕 -->
          <div class="flex justify-end space-x-4">
            <button
              @click="closePreviewModal"
              class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors"
            >
              關閉
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import axios from 'axios'
import QRCode from 'qrcode'
import JSZip from 'jszip'
import { saveAs } from 'file-saver'

const { t } = useI18n()
const router = useRouter()

// Props
const props = defineProps({
  posinId: {
    type: Number,
    required: true
  }
})

// Reactive data
const posinItems = ref([])
const showQRModal = ref(false)
const selectedItem = ref(null)
const qrGenerateCount = ref(1)
const loading = ref(false)
const qrGeneratedStatus = ref({})
const itemsCache = ref({}) // 新增：商品資料快取

// Methods
const fetchPosinItems = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/v1/posin/${props.posinId}/items`)
    posinItems.value = response.data.data || response.data

    // 檢查每個商品項目的 QR Code 生成狀態
    await checkQRGeneratedStatus()

    // 獲取商品詳細資訊
    await fetchItemsDetails()
  } catch (error) {
    console.error('Error fetching posin items:', error)
    // 如果 API 失敗，使用模擬數據
    posinItems.value = [
      {
        posinitem_id: 1,
        item_id: 2120,
        item_sn: '2120',
        item_name: 'AC+超彈力玻璃活膚乳',
        item_spec: '50ml',
        item_count: 480,
        item_price: 0,
        item_batch: '5021A',
        item_expireday: '2027-01-01',
        posin: {
          posin_id: props.posinId,
          posin_dt: '2025-05-05',
          posin_sn: '2025-05-05 [005]'
        }
      },
      {
        posinitem_id: 2,
        item_id: 5900,
        item_sn: '5900',
        item_name: '10%果酸身體乳',
        item_spec: '210ml',
        item_count: 4848,
        item_price: 0,
        item_batch: '5021A',
        item_expireday: '2027-01-01',
        posin: {
          posin_id: props.posinId,
          posin_dt: '2025-05-05',
          posin_sn: '2025-05-05 [005]'
        }
      }
    ]
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  fetchPosinItems()
}

const deleteItem = async (item) => {
  if (!confirm(t('posinItems.messages.deleteConfirm', { name: item.item_name }))) return

  try {
    await axios.delete(`/api/v1/posin-items/${item.posinitem_id}`)
    // 重新載入資料
    await fetchPosinItems()
    alert(t('posinItems.messages.deleteSuccess'))
  } catch (error) {
    console.error('Error deleting item:', error)
    alert(t('posinItems.messages.deleteError'))
  }
}

const convertToUsPurchaseOrder = async () => {
  const confirmed = confirm(t('posinItems.messages.convertConfirm'))

  if (!confirmed) return

  try {
    const response = await axios.patch(`/api/v1/posin/${props.posinId}/generate-us-purchase-order`)
    alert(t('posinItems.messages.convertSuccess'))
    // 可以導航回列表頁面或更新狀態
    router.push('/purchase-orders')
  } catch (error) {
    console.error('Error converting to US purchase order:', error)
    if (error.response?.status === 422) {
      alert(t('posinItems.messages.convertAlreadyGenerated'))
    } else {
      alert(t('posinItems.messages.convertError'))
    }
  }
}

// 新增：獲取商品詳細資訊
const fetchItemsDetails = async () => {
  try {
    // 獲取所有商品的 item_id
    const itemIds = posinItems.value.map(item => item.item_id)

    // 批次獲取商品詳細資訊
    for (const itemId of itemIds) {
      if (!itemsCache.value[itemId]) {
        try {
          const response = await axios.get(`/api/v1/items/${itemId}`)
          itemsCache.value[itemId] = response.data
        } catch (error) {
          console.error(`Error fetching item details for item_id ${itemId}:`, error)
          // 如果無法獲取，使用預設值
          itemsCache.value[itemId] = { item_inbox: 48 }
        }
      }
    }
  } catch (error) {
    console.error('Error fetching items details:', error)
  }
}

const getPackageSpec = (item) => {
  if (!item) return ''

  // 從快取中獲取商品詳細資訊
  const itemDetails = itemsCache.value[item.item_id]
  if (itemDetails && itemDetails.item_inbox) {
    return `${itemDetails.item_inbox}個/箱`
  }

  // 如果沒有快取資料，使用預設值
  return '48個/箱'
}

const getBoxCount = (item) => {
  if (!item) return 0

  // 從快取中獲取商品詳細資訊
  const itemDetails = itemsCache.value[item.item_id]
  const itemsPerBox = itemDetails?.item_inbox || 48

  return Math.ceil(item.item_count / itemsPerBox)
}



const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: 'numeric',
    day: 'numeric'
  })
}

const formatPurchaseDate = (item) => {
  if (!item?.posin) return ''
  return formatDate(item.posin.posin_dt)
}

const getExampleCode = (item) => {
  if (!item) return ''
  const date = new Date(item.item_expireday)
  const yearMonth = `${date.getFullYear()}${String(date.getMonth() + 1).padStart(2, '0')}`
  return `${item.item_sn}${item.item_batch}${yearMonth}001`
}

const openQRModal = (item) => {
  selectedItem.value = item
  qrGenerateCount.value = getBoxCount(item)
  showQRModal.value = true
}

const closeQRModal = () => {
  showQRModal.value = false
  selectedItem.value = null
  qrGenerateCount.value = 1
}

// 檢查 QR Code 生成狀態
const checkQRGeneratedStatus = async () => {
  for (const item of posinItems.value) {
    try {
      const response = await axios.get(`/api/v1/check-qr-generated/${item.posinitem_id}`)
      qrGeneratedStatus.value[item.posinitem_id] = response.data.generated
    } catch (error) {
      console.error('Error checking QR status:', error)
      qrGeneratedStatus.value[item.posinitem_id] = false
    }
  }
}

// 檢查單個商品項目的 QR Code 生成狀態
const isQRGenerated = (item) => {
  return qrGeneratedStatus.value[item.posinitem_id] || false
}

// 生成 QR Code 圖片
const generateQRCodeImage = async (qrData) => {
  try {
    const qrCodeDataURL = await QRCode.toDataURL(qrData, {
      width: 200,
      margin: 2,
      color: {
        dark: '#000000',
        light: '#FFFFFF'
      }
    })
    return qrCodeDataURL
  } catch (error) {
    console.error('Error generating QR code:', error)
    throw error
  }
}

// 下載 QR Code 標籤
const downloadQRLabels = async () => {
  try {
    loading.value = true

    // 準備商品資料
    const itemData = {
      ...selectedItem.value,
      posin_id: props.posinId
    }

    // 呼叫後端 API 生成 QR Code 資料
    const response = await axios.post('/api/v1/generate-qr-labels', {
      item: itemData,
      count: qrGenerateCount.value
    })

    if (response.data.message === 'QR codes already generated for this item') {
      alert('此商品已經生成過 QR Code 標籤')
      return
    }

    const { qr_codes, zip_file_name } = response.data

    // 創建 ZIP 檔案
    const zip = new JSZip()

    // 為每個 QR Code 生成包含文字的完整標籤圖片
    for (const qrCode of qr_codes) {
      const completeLabelImage = await generateCompleteLabel(qrCode)

      // 將 base64 轉換為 blob
      const base64Data = completeLabelImage.split(',')[1]
      const byteCharacters = atob(base64Data)
      const byteNumbers = new Array(byteCharacters.length)
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i)
      }
      const byteArray = new Uint8Array(byteNumbers)

      // 加入 ZIP 檔案
      zip.file(qrCode.file_name, byteArray)
    }

    // 生成並下載 ZIP 檔案
    const zipBlob = await zip.generateAsync({ type: 'blob' })
    saveAs(zipBlob, zip_file_name)

    // 同步儲存到 public/qr_codes 目錄
    await saveToPublicDirectory(qr_codes, zip_file_name)

    // 更新 QR Code 生成狀態
    qrGeneratedStatus.value[selectedItem.value.posinitem_id] = true

    // 關閉彈窗
    closeQRModal()

    alert(`成功生成 ${qr_codes.length} 張 QR Code 標籤`)

  } catch (error) {
    console.error('Error downloading QR labels:', error)
    alert('生成QR標籤時發生錯誤: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

// 儲存檔案到 public/qr_codes 目錄
const saveToPublicDirectory = async (qrCodes, zipFileName) => {
  try {
    // 創建 FormData 來傳送檔案
    const formData = new FormData()

    // 為每個 QR Code 生成圖片並加入 FormData
    for (const qrCode of qrCodes) {
      const completeLabelImage = await generateCompleteLabel(qrCode)

      // 將 base64 轉換為 blob
      const base64Data = completeLabelImage.split(',')[1]
      const byteCharacters = atob(base64Data)
      const byteNumbers = new Array(byteCharacters.length)
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i)
      }
      const byteArray = new Uint8Array(byteNumbers)
      const blob = new Blob([byteArray], { type: 'image/png' })

      // 加入 FormData
      formData.append('files[]', blob, qrCode.file_name)
    }

    // 加入 ZIP 檔案
    const zip = new JSZip()
    for (const qrCode of qrCodes) {
      const completeLabelImage = await generateCompleteLabel(qrCode)
      const base64Data = completeLabelImage.split(',')[1]
      const byteCharacters = atob(base64Data)
      const byteNumbers = new Array(byteCharacters.length)
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i)
      }
      const byteArray = new Uint8Array(byteNumbers)
      zip.file(qrCode.file_name, byteArray)
    }
    const zipBlob = await zip.generateAsync({ type: 'blob' })
    formData.append('zip_file', zipBlob, zipFileName)

    // 加入額外資訊
    formData.append('item_info', JSON.stringify({
      item_name: selectedItem.value.item_name,
      item_sn: selectedItem.value.item_sn,
      item_batch: selectedItem.value.item_batch,
      count: qrCodes.length
    }))

    // 呼叫後端 API 儲存檔案
    await axios.post('/api/v1/save-qr-files', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    console.log('QR Code 檔案已成功儲存到 public/qr_codes 目錄')
  } catch (error) {
    console.error('Error saving files to public directory:', error)
    // 不中斷下載流程，只記錄錯誤
  }
}

// 生成包含 QR Code 和文字的完整標籤
const generateCompleteLabel = async (qrCode) => {
  try {
    // 創建 canvas 元素
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    // 設定標籤尺寸
    const labelWidth = 400
    const labelHeight = 300
    canvas.width = labelWidth
    canvas.height = labelHeight

    // 設定背景
    ctx.fillStyle = '#FFFFFF'
    ctx.fillRect(0, 0, labelWidth, labelHeight)

    // 生成 QR Code 圖片
    const qrImageDataURL = await generateQRCodeImage(qrCode.data)
    const qrImage = new Image()

    return new Promise((resolve) => {
      qrImage.onload = () => {
        // 繪製 QR Code
        const qrSize = 120
        const qrX = 20
        const qrY = 20
        ctx.drawImage(qrImage, qrX, qrY, qrSize, qrSize)

        // 設定文字樣式
        ctx.fillStyle = '#000000'
        ctx.font = 'bold 14px Arial'

        // 繪製商品名稱
        ctx.fillText(qrCode.item_info.item_name, 160, 30)

        // 繪製商品資訊
        ctx.font = '12px Arial'
        ctx.fillText(`商品序號: ${qrCode.item_info.item_sn}`, 160, 50)
        ctx.fillText(`規格: ${qrCode.item_info.item_spec}`, 160, 70)
        ctx.fillText(`批號: ${qrCode.item_info.item_batch}`, 160, 90)
        ctx.fillText(`每箱: ${qrCode.item_info.item_inbox}個`, 160, 110)
        ctx.fillText(`有效期限: ${formatDate(qrCode.item_info.item_expireday)}`, 160, 130)
        ctx.fillText(`編碼: ${qrCode.data}`, 160, 150)
        ctx.fillText(`標籤: ${qrGenerateCount.value}箱之${qrCode.serial}`, 160, 170)

        // 如果有備註，也顯示
        if (qrCode.item_info.posin_note) {
          ctx.fillText(`備註: ${qrCode.item_info.posin_note}`, 160, 190)
        }

        // 繪製邊框
        ctx.strokeStyle = '#000000'
        ctx.lineWidth = 2
        ctx.strokeRect(10, 10, labelWidth - 20, labelHeight - 20)

        // 轉換為 base64
        const dataURL = canvas.toDataURL('image/png')
        resolve(dataURL)
      }

      qrImage.src = qrImageDataURL
    })
  } catch (error) {
    console.error('Error generating complete label:', error)
    throw error
  }
}

// 預覽相關狀態
const showPreviewModal = ref(false)
const previewQRCodes = ref([])

// 預覽標籤功能
const previewLabels = async () => {
  try {
    loading.value = true

    // 獲取商品詳細資訊
    const itemDetails = itemsCache.value[selectedItem.value.item_id]
    const itemInbox = itemDetails?.item_inbox || 48

    // 獲取進貨單資訊
    let posinNote = ''
    try {
      const posinResponse = await axios.get(`/api/v1/posin/${props.posinId}`)
      posinNote = posinResponse.data.posin_note || ''
    } catch (error) {
      console.error('Error fetching posin details:', error)
    }

    // 生成預覽用的 QR Code 資料
    const qrCodes = []
    for (let i = 1; i <= Math.min(qrGenerateCount.value, 5); i++) { // 最多預覽5個
      const qrData = generateQRData(selectedItem.value, i)
      const qrImageDataURL = await generateQRCodeImage(qrData)

      qrCodes.push({
        data: qrData,
        serial: i,
        image: qrImageDataURL,
        item_info: {
          ...selectedItem.value,
          item_inbox: itemInbox,
          posin_note: posinNote
        }
      })
    }

    previewQRCodes.value = qrCodes
    showPreviewModal.value = true

  } catch (error) {
    console.error('Error generating preview:', error)
    alert('生成預覽時發生錯誤')
  } finally {
    loading.value = false
  }
}

// 生成 QR Code 資料字串（與後端邏輯一致）
const generateQRData = (item, serialNumber) => {
  const expireDate = item.item_expireday ? new Date(item.item_expireday).toISOString().slice(0, 7).replace('-', '') : ''
  const serial = String(serialNumber).padStart(9, '0')
  return `${item.item_sn}-${item.item_batch}-${expireDate}-${serial}`
}

// 關閉預覽彈窗
const closePreviewModal = () => {
  showPreviewModal.value = false
  previewQRCodes.value = []
}

// Lifecycle
onMounted(() => {
  fetchPosinItems()
})
</script>

<style scoped>
/* 確保表格在小螢幕上可橫向捲動 */
@media (max-width: 768px) {
  .max-w-7xl {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  table {
    font-size: 0.875rem;
  }

  th, td {
    padding: 0.5rem;
  }

  .flex {
    flex-wrap: wrap;
  }
}

/* 動畫效果 */
.transition-colors {
  transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
}

/* 按鈕懸停效果 */
button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* 表格行懸停效果 */
tbody tr:hover {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
</style>
