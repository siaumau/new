
<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-sm mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">選擇列印樣板</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div class="space-y-4">
        <button @click="selectTemplate('template1')" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700 transition-colors">
          棧板 QR Code標籤
        </button>
        <button @click="selectTemplate('template2')" class="w-full px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-700 transition-colors">
          層架 QR Code標籤
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(['close', 'select']);

const selectTemplate = (template) => {
  emit('select', template);
};
</script>
