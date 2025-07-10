<template>
  <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-4 w-full max-w-md mx-auto">
      <div class="flex justify-between items-center border-b pb-3 mb-3">
        <h3 class="text-lg font-semibold">QR Code Scanner</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-800">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div id="qr-reader" style="width: 100%;"></div>
      <div v-if="errorMessage" class="mt-3 text-red-500 text-sm">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const emit = defineEmits(['close', 'scanned']);
const errorMessage = ref('');
let html5QrCode = null;

onMounted(() => {
  const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    rememberLastUsedCamera: true,
  };

  const onScanSuccess = (decodedText, decodedResult) => {
    emit('scanned', decodedText);
    emit('close');
  };

  const onScanFailure = (error) => {
    // console.warn(`Code scan error = ${error}`);
  };

  html5QrCode = new Html5Qrcode('qr-reader');
  html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure)
    .catch(err => {
      errorMessage.value = `Unable to start scanning, error: ${err}`;
      console.error(errorMessage.value);
    });
});

onBeforeUnmount(() => {
  if (html5QrCode) {
    html5QrCode.stop().catch(err => {
      console.error("Failed to stop the scanner.", err);
    });
  }
});
</script>
