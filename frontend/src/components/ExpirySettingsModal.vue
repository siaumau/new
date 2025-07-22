<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <!-- 標題 -->
      <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">短效期監控設定</h3>
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
        <form @submit.prevent="saveSettings" class="space-y-4">
          <!-- 警告天數 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              警告天數 (黃色提醒)
            </label>
            <input
              v-model.number="localSettings.warningDays"
              type="number"
              min="1"
              max="365"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="180"
            />
            <p class="text-xs text-gray-500 mt-1">
              商品將在此天數內到期時顯示黃色警告
            </p>
          </div>

          <!-- 嚴重警告天數 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              嚴重警告天數 (紅色提醒)
            </label>
            <input
              v-model.number="localSettings.criticalDays"
              type="number"
              min="1"
              :max="localSettings.warningDays"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="90"
            />
            <p class="text-xs text-gray-500 mt-1">
              商品將在此天數內到期時顯示紅色嚴重警告
            </p>
          </div>

          <!-- 自動通知 -->
          <div>
            <label class="flex items-center">
              <input
                v-model="localSettings.enableAutoNotification"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
              />
              <span class="ml-2 text-sm text-gray-700">啟用自動通知</span>
            </label>
            <p class="text-xs text-gray-500 mt-1">
              每日自動檢查並發送即將到期的商品通知
            </p>
          </div>

          <!-- 通知時間 -->
          <div v-if="localSettings.enableAutoNotification">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              每日通知時間
            </label>
            <input
              v-model="localSettings.notificationTime"
              type="time"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
            <p class="text-xs text-gray-500 mt-1">
              系統將在此時間自動檢查並發送通知
            </p>
          </div>

          <!-- 預覽效果 -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">預覽效果</h4>
            <div class="space-y-2">
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  正常 (> {{ localSettings.warningDays }} 天)
                </span>
                <span class="text-xs text-gray-500">安全期</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                  警告 ({{ localSettings.criticalDays + 1 }}-{{ localSettings.warningDays }} 天)
                </span>
                <span class="text-xs text-gray-500">注意期</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  嚴重 (≤ {{ localSettings.criticalDays }} 天)
                </span>
                <span class="text-xs text-gray-500">危險期</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  已過期 (< 0 天)
                </span>
                <span class="text-xs text-gray-500">過期</span>
              </div>
            </div>
          </div>

          <!-- 按鈕 -->
          <div class="flex justify-end space-x-3 pt-4">
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

// Watch for external settings changes
watch(() => props.settings, (newSettings) => {
  localSettings.value = { ...newSettings }
}, { deep: true })

// Methods
const saveSettings = () => {
  // 驗證設定
  if (localSettings.value.criticalDays >= localSettings.value.warningDays) {
    alert('嚴重警告天數必須小於警告天數')
    return
  }

  if (localSettings.value.warningDays <= 0 || localSettings.value.criticalDays <= 0) {
    alert('天數必須大於 0')
    return
  }

  emit('save', { ...localSettings.value })
}
</script>

<style scoped>
/* Modal specific styles */
</style>