<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頁面標題區域 -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">QR Code 列印管理</h1>
            <p class="mt-1 text-sm text-gray-600">管理和列印已儲存的 QR Code 標籤</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="refreshFiles"
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
      <!-- 載入中狀態 -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-gray-600">載入中...</span>
      </div>

      <!-- QR檔案列表 -->
      <div v-else class="space-y-6">
        <div v-if="qrFiles.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">沒有已儲存的 QR Code</h3>
          <p class="mt-1 text-sm text-gray-500">請先生成並儲存 QR Code 標籤</p>
        </div>

        <!-- QR檔案卡片 -->
        <div v-for="fileGroup in qrFiles" :key="fileGroup.folder_name" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ fileGroup.folder_name }}</h3>
                <p class="text-sm text-gray-600">
                  {{ fileGroup.file_count }} 個檔案 
                  <span v-if="fileGroup.file_types">
                    ({{ fileGroup.file_types.html || 0 }} HTML, {{ fileGroup.file_types.png || 0 }} PNG)
                  </span>
                  • 建立時間: {{ formatDate(fileGroup.created_at) }}
                </p>
              </div>
              <div class="flex space-x-2">
                <button
                  v-if="hasHTMLFiles(fileGroup)"
                  @click="openHTMLFile(fileGroup)"
                  class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                  <span>開啟HTML</span>
                </button>
                <button
                  v-if="hasPNGFiles(fileGroup)"
                  @click="previewFiles(fileGroup)"
                  class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span>預覽PNG</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 預覽彈窗 -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
        <!-- 預覽彈窗標題 -->
        <div class="flex justify-between items-center border-b border-gray-200 p-6">
          <h2 class="text-2xl font-bold text-gray-900">QR Code 標籤預覽 - {{ selectedFileGroup?.folder_name }}</h2>
          <button @click="closePreviewModal" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- 預覽內容 -->
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="fileName in getPNGFiles(selectedFileGroup)"
              :key="fileName"
              class="border border-gray-200 rounded-lg p-4 text-center"
            >
              <div class="mb-3">
                <img 
                  :src="getFileUrl(selectedFileGroup.folder_name, fileName)" 
                  :alt="fileName"
                  class="mx-auto max-w-full h-auto"
                  @error="handleImageError"
                />
              </div>
              <div class="text-sm text-gray-600">
                <p class="font-medium">{{ fileName }}</p>
              </div>
            </div>
          </div>

          <!-- 預覽彈窗按鈕 -->
          <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Reactive data
const qrFiles = ref([])
const loading = ref(false)
const showPreviewModal = ref(false)
const selectedFileGroup = ref(null)

// Methods
const fetchQRFiles = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/v1/qr-files')
    qrFiles.value = response.data.files || []
  } catch (error) {
    console.error('Error fetching QR files:', error)
    alert('載入QR檔案列表時發生錯誤')
  } finally {
    loading.value = false
  }
}

const refreshFiles = () => {
  fetchQRFiles()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleString('zh-TW')
}

const getFileUrl = (folderName, fileName) => {
  return `${window.location.origin}/qr_codes/${folderName}/${fileName}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  console.error('Image load error:', event.target.src)
}

const hasHTMLFiles = (fileGroup) => {
  return fileGroup.file_types && fileGroup.file_types.html > 0
}

const hasPNGFiles = (fileGroup) => {
  return fileGroup.file_types && fileGroup.file_types.png > 0
}

const openHTMLFile = (fileGroup) => {
  // 找到HTML文件
  const htmlFile = fileGroup.files.find(file => file.endsWith('.html'))
  if (htmlFile) {
    const url = getFileUrl(fileGroup.folder_name, htmlFile)
    window.open(url, '_blank')
  }
}

const getPNGFiles = (fileGroup) => {
  if (!fileGroup) return []
  return fileGroup.files.filter(file => file.endsWith('.png'))
}

const previewFiles = (fileGroup) => {
  selectedFileGroup.value = fileGroup
  showPreviewModal.value = true
}

const closePreviewModal = () => {
  showPreviewModal.value = false
  selectedFileGroup.value = null
}

// Lifecycle
onMounted(() => {
  fetchQRFiles()
})
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  
  .print-content {
    width: 100%;
    height: 100%;
  }
  
  body {
    margin: 0;
    padding: 0;
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
</style>