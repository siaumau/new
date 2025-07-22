<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <!-- 標題 -->
      <div class="mt-3">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-medium text-gray-900">Teams通知設定</h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- 設定表單 -->
        <form @submit.prevent="saveSettings" class="space-y-6">
          <!-- 基本設定 -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-4">基本設定</h4>
            
            <!-- 啟用通知 -->
            <div class="mb-4">
              <label class="flex items-center">
                <input
                  v-model="localSettings.enableNotifications"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                />
                <span class="ml-2 text-sm text-gray-700">啟用Teams通知</span>
              </label>
              <p class="text-xs text-gray-500 mt-1">
                開啟後將透過PowerAutomate發送通知到Teams頻道
              </p>
            </div>

            <!-- Webhook URL -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                PowerAutomate Webhook URL <span class="text-red-500">*</span>
              </label>
              <input
                v-model="localSettings.webhookUrl"
                type="url"
                :disabled="!localSettings.enableNotifications"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="https://prod-xx.westus.logic.azure.com:443/workflows/..."
              />
              <p class="text-xs text-gray-500 mt-1">
                請在Teams中設定PowerAutomate流程並複製Webhook URL
              </p>
            </div>

            <!-- 頻道名稱 -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Teams頻道名稱
              </label>
              <input
                v-model="localSettings.channelName"
                type="text"
                :disabled="!localSettings.enableNotifications"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                placeholder="例如: 庫存管理通知"
              />
              <p class="text-xs text-gray-500 mt-1">
                用於識別通知來源的頻道名稱
              </p>
            </div>
          </div>

          <!-- 通知類型 -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-4">通知類型</h4>
            
            <div class="space-y-3">
              <!-- 即將到期 -->
              <label class="flex items-center">
                <input
                  v-model="localSettings.notificationTypes.expiring"
                  type="checkbox"
                  :disabled="!localSettings.enableNotifications"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100"
                />
                <span class="ml-2 text-sm text-gray-700">即將到期商品提醒</span>
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  警告
                </span>
              </label>
              
              <!-- 已過期 -->
              <label class="flex items-center">
                <input
                  v-model="localSettings.notificationTypes.expired"
                  type="checkbox"
                  :disabled="!localSettings.enableNotifications"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100"
                />
                <span class="ml-2 text-sm text-gray-700">已過期商品通知</span>
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  嚴重
                </span>
              </label>
              
              <!-- 低庫存 -->
              <label class="flex items-center">
                <input
                  v-model="localSettings.notificationTypes.lowStock"
                  type="checkbox"
                  :disabled="!localSettings.enableNotifications"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:bg-gray-100"
                />
                <span class="ml-2 text-sm text-gray-700">低庫存提醒</span>
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                  提醒
                </span>
              </label>
            </div>
          </div>

          <!-- PowerAutomate設定說明 -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-md font-medium text-blue-800 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              PowerAutomate設定步驟
            </h4>
            <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
              <li>在Teams中進入要接收通知的頻道</li>
              <li>點擊「...」→「連接器」→「PowerAutomate」</li>
              <li>選擇「當收到HTTP請求時」觸發器</li>
              <li>設定JSON結構：
                <pre class="text-xs bg-white p-2 rounded mt-1 text-gray-700">{
  "title": "string",
  "message": "string", 
  "type": "string",
  "items": "array"
}</pre>
              </li>
              <li>添加「在Teams中發佈訊息」動作</li>
              <li>複製HTTP觸發器的URL並貼上到上方欄位</li>
            </ol>
          </div>

          <!-- 測試通知 -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-700 mb-3">測試通知</h4>
            <button
              type="button"
              @click="sendTestNotification"
              :disabled="!localSettings.enableNotifications || !localSettings.webhookUrl || isTestingSending"
              class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors"
            >
              <svg v-if="isTestingSending" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <span>{{ isTestingSending ? '發送中...' : '發送測試通知' }}</span>
            </button>
            <p class="text-xs text-gray-500 mt-2">
              發送測試訊息到Teams頻道以驗證設定是否正確
            </p>
          </div>

          <!-- 按鈕 -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              取消
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              儲存設定
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

// Props
const props = defineProps({
  settings: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['save', 'close'])

// Local reactive copy of settings
const localSettings = ref({ ...props.settings })
const isTestingSending = ref(false)

// Watch for external settings changes
watch(() => props.settings, (newSettings) => {
  localSettings.value = { ...newSettings }
}, { deep: true })

// Methods
const saveSettings = () => {
  // 驗證設定
  if (localSettings.value.enableNotifications && !localSettings.value.webhookUrl) {
    alert('請輸入PowerAutomate Webhook URL')
    return
  }

  if (localSettings.value.webhookUrl && !isValidUrl(localSettings.value.webhookUrl)) {
    alert('請輸入有效的Webhook URL')
    return
  }

  emit('save', { ...localSettings.value })
}

const sendTestNotification = async () => {
  if (!localSettings.value.webhookUrl) {
    alert('請先設定Webhook URL')
    return
  }

  isTestingSending.value = true

  try {
    const testData = {
      title: '🧪 庫存管理系統測試通知',
      message: '這是一則測試訊息，用於驗證Teams通知設定是否正確。',
      type: 'test',
      timestamp: new Date().toLocaleString('zh-TW'),
      items: [
        {
          name: '測試商品',
          status: '測試中',
          details: '如果您看到此訊息，表示Teams通知設定已正確完成！'
        }
      ]
    }

    // 直接發送到PowerAutomate Webhook
    const response = await fetch(localSettings.value.webhookUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(testData)
    })

    if (response.ok) {
      alert('✅ 測試通知已成功發送到Teams!')
    } else {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    }

  } catch (error) {
    console.error('Error sending test notification:', error)
    alert(`❌ 發送測試通知失敗: ${error.message}`)
  } finally {
    isTestingSending.value = false
  }
}

const isValidUrl = (string) => {
  try {
    const url = new URL(string)
    return url.protocol === 'http:' || url.protocol === 'https:'
  } catch (_) {
    return false
  }
}
</script>

<style scoped>
/* Modal specific styles */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

pre {
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>