<template>
  <div class="min-h-screen bg-gray-50">
    <!-- 頁面標題區域 -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">位置管理</h1>
            <p class="mt-1 text-sm text-gray-600">管理倉庫位置信息，支援CSV批量匯入</p>
          </div>
          <div class="flex space-x-3">
            <!-- CSV匯入按鈕 -->
            <button
              @click="openImportModal"
              class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
              </svg>
              <span>CSV匯入</span>
            </button>

            <!-- 新增位置按鈕 -->
            <button
              @click="newLocation"
              class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors shadow-sm"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span>新增位置</span>
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">總位置數</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ locations.length }}</dd>
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">建築物數</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ uniqueBuildings }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">存儲類型數</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ uniqueStorageTypes }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 位置列表 -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- 表格標題 -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
          <h2 class="text-lg font-semibold text-gray-900">位置清單</h2>
          <p class="text-sm text-gray-600 mt-1">共 {{ locations.length }} 個位置</p>
        </div>

        <!-- 載入中狀態 -->
        <div v-if="loading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">載入中...</span>
        </div>

        <!-- 位置表格 -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">位置代碼</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">位置名稱</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">建築物代碼</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">存儲類型代碼</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">子區域代碼</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">樓層</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="location in locations" :key="location.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ location.location_code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ location.location_name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ location.building_code }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ location.storage_type_code }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ location.sub_area_code }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ location.floor_number }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editLocation(location)"
                      class="text-blue-600 hover:text-blue-900 transition-colors"
                      title="編輯位置"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    
                    <button
                      @click="deleteLocation(location.id)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="刪除位置"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 空狀態 -->
        <div v-if="!loading && locations.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">沒有位置資料</h3>
          <p class="mt-1 text-sm text-gray-500">開始新增位置或匯入CSV檔案</p>
        </div>
      </div>
    </div>

    <!-- CSV匯入彈窗 -->
    <CSVImportModal
      v-if="showImportModal"
      ref="csvImportModal"
      @close="closeImportModal"
      @import="handleCSVImport"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import CSVImportModal from './CSVImportModal.vue'

// Reactive data
const loading = ref(false)
const locations = ref([])
const showImportModal = ref(false)
const csvImportModal = ref(null)

// Computed properties
const uniqueBuildings = computed(() => {
  const buildings = new Set(locations.value.map(loc => loc.building_code).filter(Boolean))
  return buildings.size
})

const uniqueStorageTypes = computed(() => {
  const storageTypes = new Set(locations.value.map(loc => loc.storage_type_code).filter(Boolean))
  return storageTypes.size
})

// Methods
const fetchLocations = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/v1/locations')
    locations.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching locations:', error)
    locations.value = []
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  fetchLocations()
}

const newLocation = () => {
  // Emit an event to parent to open form for new location
  console.log('New location clicked')
}

const editLocation = (location) => {
  // Emit an event to parent to open form for editing
  console.log('Edit location clicked:', location)
}

const deleteLocation = async (id) => {
  if (confirm('確定要刪除這個位置嗎？')) {
    try {
      await axios.delete(`/api/v1/locations/${id}`)
      fetchLocations() // Refresh the list
      alert('位置已成功刪除')
    } catch (error) {
      console.error('Error deleting location:', error)
      alert('刪除位置時發生錯誤')
    }
  }
}

// CSV Import functions
const openImportModal = () => {
  showImportModal.value = true
}

const closeImportModal = () => {
  showImportModal.value = false
}

const handleCSVImport = async (csvData) => {
  try {
    loading.value = true
    
    const response = await axios.post('/api/v1/locations/batch', {
      locations: csvData
    })
    
    // 處理成功和部分成功的響應
    const data = response.data
    let message = data.message || '匯入完成'
    
    if (data.created_count > 0) {
      message += `\n成功匯入 ${data.created_count} 筆位置資料`
    }
    
    if (data.skipped_count > 0) {
      message += `\n跳過 ${data.skipped_count} 筆重複資料`
    }
    
    // 如果有錯誤，顯示錯誤詳情但不中斷流程
    if (data.error_count > 0 && data.errors && data.errors.length > 0) {
      message += `\n\n錯誤詳情 (${data.error_count} 項)：`
      data.errors.forEach((error, index) => {
        message += `\n第${error.index + 2}行 (${error.location_code}): ${error.error}`
      })
    }
    
    // 在模態框中顯示結果
    if (csvImportModal.value) {
      csvImportModal.value.showResult({
        success: true,
        message: message,
        errors: []
      })
    }
    
    fetchLocations() // 重新載入資料
    
    // 延遲關閉模態框，讓用戶看到結果
    setTimeout(() => {
      closeImportModal()
    }, 3000)
    
  } catch (error) {
    console.error('CSV import error:', error)
    
    // 處理 422 錯誤響應
    if (error.response && error.response.status === 422) {
      const errorData = error.response.data
      
      // 生成錯誤報告並下載
      if (errorData.errors && errorData.errors.length > 0) {
        await generateAndDownloadErrorReport(errorData.errors)
        
        // 顯示錯誤摘要
        let errorSummary = `匯入失敗，共發現 ${errorData.error_count} 個錯誤：\n\n`
        errorData.errors.forEach((err, index) => {
          errorSummary += `${index + 1}. 第${err.index + 2}行 (${err.location_code}): ${err.error}\n`
        })
        errorSummary += `\n錯誤報告已自動下載到您的電腦`
        
        // 在模態框中顯示結構化錯誤
        if (csvImportModal.value) {
          csvImportModal.value.showResult({
            success: false,
            message: errorSummary,
            errors: errorData.errors || []
          })
        }
      } else {
        if (csvImportModal.value) {
          csvImportModal.value.showResult({
            success: false,
            message: errorData.message || '匯入失敗：發現重複項目或驗證錯誤',
            errors: []
          })
        }
      }
    } else {
      // 顯示一般錯誤
      if (csvImportModal.value) {
        csvImportModal.value.showResult({
          success: false,
          message: '匯入CSV時發生錯誤',
          errors: [error.response?.data?.message || error.message]
        })
      }
    }
  } finally {
    loading.value = false
  }
}

// 生成並下載錯誤報告
const generateAndDownloadErrorReport = async (errors) => {
  const timestamp = new Date().toISOString().replace(/:/g, '-').split('.')[0]
  const filename = `locations_import_errors_${timestamp}.txt`
  
  let errorReport = `位置批量匯入錯誤報告\n`
  errorReport += `生成時間: ${new Date().toLocaleString('zh-TW')}\n`
  errorReport += `錯誤總數: ${errors.length}\n`
  errorReport += `${'='.repeat(50)}\n\n`
  
  errors.forEach((error, index) => {
    errorReport += `錯誤 ${index + 1}:\n`
    errorReport += `  CSV行號: ${error.index + 2}\n`
    errorReport += `  位置代碼: ${error.location_code || '未指定'}\n`
    errorReport += `  錯誤內容: ${error.error}\n`
    errorReport += `\n`
  })
  
  errorReport += `${'='.repeat(50)}\n`
  errorReport += `請檢查上述錯誤並修正CSV檔案後重新匯入。\n`
  
  // 創建並下載文件
  const blob = new Blob([errorReport], { type: 'text/plain;charset=utf-8' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.style.display = 'none'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}

// Lifecycle
onMounted(fetchLocations)
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
