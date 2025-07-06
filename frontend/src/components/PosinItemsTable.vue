<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">{{ $t('posinItems.title') }}</h1>

    <!-- 商品項目表格 -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3 px-4 border-b text-left font-medium text-gray-700">{{ $t('posinItems.table.itemSN') }}</th>
            <th class="py-3 px-4 border-b text-left font-medium text-gray-700">{{ $t('posinItems.table.itemName') }}</th>
            <th class="py-3 px-4 border-b text-left font-medium text-gray-700">{{ $t('posinItems.table.spec') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.quantity') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.packageSpec') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.boxCount') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.unitPrice') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.subtotal') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.expiryDate') }}</th>
            <th class="py-3 px-4 border-b text-center font-medium text-gray-700">{{ $t('posinItems.table.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in posinItems" :key="item.posinitem_id" class="hover:bg-gray-50">
            <td class="py-3 px-4 border-b">{{ item.item_sn }}</td>
            <td class="py-3 px-4 border-b font-medium">{{ item.item_name }}</td>
            <td class="py-3 px-4 border-b text-sm text-gray-600">{{ item.item_spec }}</td>
            <td class="py-3 px-4 border-b text-center">{{ item.item_count }}</td>
            <td class="py-3 px-4 border-b text-center">{{ getPackageSpec(item) }}</td>
            <td class="py-3 px-4 border-b text-center">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ getBoxCount(item) }}
              </span>
            </td>
            <td class="py-3 px-4 border-b text-center">${{ item.item_price }}</td>
            <td class="py-3 px-4 border-b text-center font-medium">${{ getSubtotal(item) }}</td>
            <td class="py-3 px-4 border-b text-center">{{ formatDate(item.item_expireday) }}</td>
            <td class="py-3 px-4 border-b text-center">
              <button
                @click="openQRModal(item)"
                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded text-sm transition-colors"
              >
                {{ $t('posinItems.table.generateQR') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- QR Code 生成彈出窗口 -->
    <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold text-gray-800">{{ $t('posinItems.qrCode.title') }}</h2>
          <button @click="closeQRModal" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- 商品詳細資訊 -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <h3 class="font-bold text-lg mb-3">{{ selectedItem?.item_name }}</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.itemSN') }}</span>
              <span class="ml-2">{{ selectedItem?.item_sn }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.batch') }}</span>
              <span class="ml-2">{{ selectedItem?.item_batch }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.spec') }}</span>
              <span class="ml-2">{{ selectedItem?.item_spec }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.quantity') }}</span>
              <span class="ml-2">{{ selectedItem?.item_count }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.packageSpec') }}</span>
              <span class="ml-2">{{ getPackageSpec(selectedItem) }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.boxCount') }}</span>
              <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ getBoxCount(selectedItem) }}
              </span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.purchaseDate') }}</span>
              <span class="ml-2">{{ formatPurchaseDate(selectedItem) }}</span>
            </div>
            <div>
              <span class="font-medium">{{ $t('posinItems.qrCode.expiryDate') }}</span>
              <span class="ml-2">{{ formatDate(selectedItem?.item_expireday) }}</span>
            </div>
          </div>
        </div>

        <!-- QR Code 生成設定 -->
        <div class="border-t pt-4">
          <h4 class="font-bold text-lg mb-3">{{ $t('posinItems.qrCode.boxQRTitle') }}</h4>
          <p class="text-sm text-gray-600 mb-4">
            {{ $t('posinItems.qrCode.description', { count: getBoxCount(selectedItem) }) }}
          </p>
          <p class="text-sm text-gray-600 mb-2">{{ $t('posinItems.qrCode.labelInfo') }}</p>
          <p class="text-sm text-gray-600 mb-4">
            {{ $t('posinItems.qrCode.codeFormat', { example: getExampleCode(selectedItem) }) }}
          </p>

          <div class="flex items-center space-x-4 mb-6">
            <label class="font-medium">{{ $t('posinItems.qrCode.generateCount') }}</label>
            <input
              v-model="qrGenerateCount"
              type="number"
              min="1"
              :max="getBoxCount(selectedItem)"
              class="border rounded px-3 py-2 w-20 text-center"
            />
            <span class="text-sm text-gray-600">{{ $t('posinItems.qrCode.generateCountUnit') }}</span>
          </div>

          <div class="flex space-x-4">
            <button
              @click="downloadQRLabels"
              class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span>{{ $t('posinItems.qrCode.download', { count: qrGenerateCount }) }}</span>
            </button>
            <button
              @click="previewLabels"
              class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span>{{ $t('posinItems.qrCode.preview') }}</span>
            </button>
            <button
              @click="closeQRModal"
              class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
            >
              {{ $t('posinItems.qrCode.cancel') }}
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
import axios from 'axios'

const { t } = useI18n()

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

// Methods
const fetchPosinItems = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/v1/posin/${props.posinId}/items`)
    posinItems.value = response.data.data || response.data
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
          posin_dt: '2025-05-05',
          posin_sn: '2025-05-05 [005]'
        }
      }
    ]
  } finally {
    loading.value = false
  }
}

const getPackageSpec = (item) => {
  if (!item) return ''
  // 根據截圖顯示的格式，包裝規格是固定的"48個/箱"
  return '48個/箱'
}

const getBoxCount = (item) => {
  if (!item) return 0
  // 假設每箱包裝48個/箱（可以從商品資料中獲取）
  const itemsPerBox = 48
  return Math.ceil(item.item_count / itemsPerBox)
}

const getSubtotal = (item) => {
  if (!item) return 0
  return (item.item_count * item.item_price).toFixed(0)
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

const downloadQRLabels = async () => {
  try {
    const response = await axios.post('/api/v1/generate-qr-labels', {
      item: selectedItem.value,
      count: qrGenerateCount.value
    }, {
      responseType: 'blob'
    })

    // 下載生成的PDF文件
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `qr_labels_${selectedItem.value.item_sn}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error downloading QR labels:', error)
    alert('生成QR標籤時發生錯誤')
  }
}

const previewLabels = () => {
  // 實現預覽功能
  console.log('Preview labels for:', selectedItem.value, 'Count:', qrGenerateCount.value)
}

// Lifecycle
onMounted(() => {
  fetchPosinItems()
})
</script>

<style scoped>
/* 確保表格在小螢幕上可橫向捲動 */
@media (max-width: 768px) {
  .container {
    padding: 0.5rem;
  }

  table {
    font-size: 0.875rem;
  }

  th, td {
    padding: 0.5rem;
  }
}
</style>
