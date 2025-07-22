<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
      <!-- 標題 -->
      <div class="flex justify-between items-center mb-6 pb-4 border-b">
        <div>
          <h3 class="text-xl font-bold text-gray-900">CSV 批量匯入位置</h3>
          <p class="text-sm text-gray-600 mt-1">支援批量匯入位置資料，系統會自動驗證重複項目</p>
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
        <!-- 左側：匯入區域 -->
        <div class="space-y-6">
          <!-- CSV格式說明 -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-md font-medium text-blue-800 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              CSV 格式要求
            </h4>
            <div class="text-sm text-blue-800 space-y-2">
              <p>CSV 檔案必須包含以下欄位（按順序）：</p>
              <div class="bg-white p-3 rounded font-mono text-xs text-gray-700">
                location_code, location_name, building_code, storage_type_code, sub_area_code, floor_number
              </div>
              <ul class="list-disc list-inside space-y-1 text-xs">
                <li><strong>building_code</strong>, <strong>storage_type_code</strong>, <strong>sub_area_code</strong> 為必填欄位</li>
                <li>系統會自動檢查這三個欄位的組合是否重複</li>
                <li>如有重複或空值，將阻止整個匯入作業</li>
                <li>第一行應為欄位標題，系統會自動跳過</li>
              </ul>
            </div>
          </div>

          <!-- CSV範例 -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="text-md font-medium text-gray-700 mb-3">CSV 範例</h4>
            <div class="bg-white border rounded p-3 font-mono text-xs text-gray-700 overflow-x-auto">
              <div>location_code,location_name,building_code,storage_type_code,sub_area_code,floor_number</div>
              <div>A001,倉庫A區第1排,A,GENERAL,001,1</div>
              <div>A002,倉庫A區第2排,A,GENERAL,002,1</div>
              <div>B001,冷藏庫B區,B,COLD,001,2</div>
            </div>
          </div>

          <!-- 檔案上傳區域 -->
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
            <input
              ref="fileInput"
              type="file"
              accept=".csv"
              @change="handleFileSelect"
              class="hidden"
            />
            <div v-if="!csvContent" @click="$refs.fileInput.click()" class="cursor-pointer">
              <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <div class="mt-4">
                <p class="text-sm text-gray-600">
                  <span class="font-medium text-blue-600 hover:text-blue-500">點擊選擇檔案</span>
                  或拖曳 CSV 檔案到此處
                </p>
                <p class="text-xs text-gray-500 mt-1">僅支援 CSV 格式檔案</p>
              </div>
            </div>
            <div v-else class="text-left">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">已選擇檔案：{{ fileName }}</span>
                <button
                  @click="clearFile"
                  class="text-red-600 hover:text-red-800 text-sm"
                >
                  清除
                </button>
              </div>
              <div class="text-xs text-gray-500">
                預覽行數：{{ csvLines.length }} 行（包含標題行）
              </div>
            </div>
          </div>

          <!-- 匯入按鈕 -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200"
            >
              取消
            </button>
            <button
              @click="importCSV"
              :disabled="!csvContent || isImporting"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center space-x-2"
            >
              <svg v-if="isImporting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isImporting ? '匯入中...' : '開始匯入' }}</span>
            </button>
          </div>
        </div>

        <!-- 右側：預覽區域 -->
        <div class="space-y-6">
          <!-- CSV預覽 -->
          <div v-if="csvContent" class="bg-white border rounded-lg">
            <div class="px-4 py-3 border-b bg-gray-50">
              <h4 class="text-lg font-semibold text-gray-900">CSV 內容預覽</h4>
              <p class="text-sm text-gray-600">顯示前 10 行數據</p>
            </div>
            <div class="p-4">
              <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">行號</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">位置代碼</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">位置名稱</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">建築物代碼</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">存儲類型代碼</th>
                      <th class="px-2 py-2 text-left text-xs font-medium text-gray-500">子區域代碼</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="(line, index) in csvPreview" :key="index" class="hover:bg-gray-50">
                      <td class="px-2 py-2 text-xs text-gray-500">{{ index + 2 }}</td>
                      <td class="px-2 py-2 text-xs">{{ line[0] || '-' }}</td>
                      <td class="px-2 py-2 text-xs">{{ line[1] || '-' }}</td>
                      <td class="px-2 py-2 text-xs">
                        <span v-if="line[2]" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                          {{ line[2] }}
                        </span>
                        <span v-else class="text-red-600 text-xs">必填</span>
                      </td>
                      <td class="px-2 py-2 text-xs">
                        <span v-if="line[3]" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                          {{ line[3] }}
                        </span>
                        <span v-else class="text-red-600 text-xs">必填</span>
                      </td>
                      <td class="px-2 py-2 text-xs">
                        <span v-if="line[4]" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                          {{ line[4] }}
                        </span>
                        <span v-else class="text-red-600 text-xs">必填</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="csvLines.length > 11" class="text-center text-xs text-gray-500 mt-2">
                ...還有 {{ csvLines.length - 11 }} 行數據
              </div>
            </div>
          </div>

          <!-- 匯入狀態 -->
          <div v-if="importResult" class="rounded-lg p-4" :class="importResult.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
            <div class="flex items-start">
              <svg v-if="importResult.success" class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <div class="flex-1">
                <h4 class="font-medium" :class="importResult.success ? 'text-green-800' : 'text-red-800'">
                  {{ importResult.success ? '匯入成功' : '匯入失敗' }}
                </h4>
                <div class="text-sm mt-2" :class="importResult.success ? 'text-green-700' : 'text-red-700'">
                  <pre class="whitespace-pre-wrap font-sans text-sm">{{ importResult.message }}</pre>
                </div>
                
                <!-- 簡潔的錯誤列表（如果錯誤較少） -->
                <div v-if="!importResult.success && importResult.errors && importResult.errors.length > 0 && importResult.errors.length <= 5" class="mt-3">
                  <div class="bg-red-100 rounded p-2">
                    <ul class="text-xs text-red-700 space-y-1">
                      <li v-for="(error, index) in importResult.errors" :key="index" class="flex flex-col">
                        <span v-if="typeof error === 'string'">• {{ error }}</span>
                        <span v-else class="break-words">
                          • 第{{ (error.index || 0) + 2 }}行 
                          <span v-if="error.location_code" class="font-medium">({{ error.location_code }})</span>: 
                          {{ error.error || error.message }}
                        </span>
                      </li>
                    </ul>
                  </div>
                </div>
                
                <!-- 下載提示（如果有錯誤文件下載） -->
                <div v-if="!importResult.success && importResult.errors && importResult.errors.length > 0" class="mt-3 p-2 bg-blue-50 rounded border border-blue-200">
                  <div class="flex items-start">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div class="text-xs text-blue-700">
                      <p class="font-medium">錯誤報告已下載</p>
                      <p>詳細錯誤信息已保存為 TXT 檔案，請檢查下載資料夾。</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Emits
const emit = defineEmits(['close', 'import'])

// Reactive data
const csvContent = ref('')
const fileName = ref('')
const isImporting = ref(false)
const importResult = ref(null)

// Computed properties
const csvLines = computed(() => {
  if (!csvContent.value) return []
  return csvContent.value.split('\n').filter(line => line.trim())
})

const csvPreview = computed(() => {
  if (csvLines.value.length <= 1) return []
  // 跳過標題行，顯示前10行數據
  return csvLines.value.slice(1, 11).map(line => {
    return line.split(',').map(cell => cell.trim().replace(/^"|"$/g, ''))
  })
})

// Methods
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  fileName.value = file.name
  importResult.value = null

  const reader = new FileReader()
  reader.onload = (e) => {
    csvContent.value = e.target.result
  }
  reader.readAsText(file, 'UTF-8')
}

const clearFile = () => {
  csvContent.value = ''
  fileName.value = ''
  importResult.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const importCSV = async () => {
  if (!csvContent.value) {
    alert('請先選擇CSV檔案')
    return
  }

  isImporting.value = true
  importResult.value = null

  try {
    // 解析 CSV 數據為對象數組
    const lines = csvContent.value.split('\n').filter(line => line.trim())
    if (lines.length <= 1) {
      throw new Error('CSV 檔案沒有有效的數據行')
    }

    // 解析標題行
    const headers = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''))
    
    // 解析數據行
    const locations = []
    for (let i = 1; i < lines.length; i++) {
      const values = lines[i].split(',').map(v => v.trim().replace(/^"|"$/g, ''))
      if (values.length >= 3) { // 至少需要 3 個必填欄位
        locations.push({
          location_code: values[0] || `AUTO_${Date.now()}_${i}`,
          location_name: values[1] || values[0] || `位置_${i}`,
          building_code: values[2] || '',
          floor_number: values[5] || '1', // 預設樓層為1
          floor_area_code: null,
          storage_type_code: values[3] || '',
          sub_area_code: values[4] || '',
          position_code: values[0] || `POS_${Date.now()}_${i}`, // 使用 location_code 作為 position_code
          capacity: 100, // 預設容量
          current_stock: 0, // 預設庫存
          qr_code_data: values[0] || null,
          notes: null,
          is_active: true
        })
      }
    }

    // 發送到父組件
    emit('import', locations)
  } catch (error) {
    console.error('Import error:', error)
    importResult.value = {
      success: false,
      message: '匯入時發生錯誤',
      errors: [error.message]
    }
  } finally {
    isImporting.value = false
  }
}

// 提供方法給父組件調用以顯示結果
const showResult = (result) => {
  importResult.value = result
  if (result.success) {
    // 成功後清除檔案
    setTimeout(() => {
      clearFile()
    }, 2000)
  }
}

// 暴露方法給父組件
defineExpose({
  showResult
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>