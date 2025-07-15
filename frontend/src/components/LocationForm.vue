<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" v-if="showForm">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">{{ isEditing ? 'Edit Location' : 'Add New Location' }}</h3>
      <form @submit.prevent="saveLocation">
        <div class="mb-4">
          <label for="location_code" class="block text-gray-700 text-sm font-bold mb-2">Location Code:</label>
          <input type="text" id="location_code" v-model="form.location_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="location_name" class="block text-gray-700 text-sm font-bold mb-2">Location Name:</label>
          <input type="text" id="location_name" v-model="form.location_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="building_code" class="block text-gray-700 text-sm font-bold mb-2">Building Code:</label>
          <input type="text" id="building_code" v-model="form.building_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="floor_number" class="block text-gray-700 text-sm font-bold mb-2">Floor Number:</label>
          <input type="text" id="floor_number" v-model="form.floor_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="storage_type_code" class="block text-gray-700 text-sm font-bold mb-2">Storage Type Code:</label>
          <input type="text" id="storage_type_code" v-model="form.storage_type_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="position_code" class="block text-gray-700 text-sm font-bold mb-2">Position Code:</label>
          <input type="text" id="position_code" v-model="form.position_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <!-- Add other fields as needed -->
        <div class="flex items-center justify-between">
          <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            {{ isEditing ? 'Update' : 'Save' }}
          </button>
          <button type="button" @click="closeForm" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  location: Object,
  showForm: Boolean,
});

const emit = defineEmits(['close', 'saved']);

const isEditing = ref(false);
const form = ref({
  location_code: '',
  location_name: '',
  building_code: '',
  floor_number: '',
  floor_area_code: null,
  storage_type_code: '',
  sub_area_code: null,
  position_code: '',
  capacity: 0,
  current_stock: 0,
  qr_code_data: null,
  notes: null,
  is_active: true,
});

const resetForm = () => {
  form.value = {
    location_code: '',
    location_name: '',
    building_code: '',
    floor_number: '',
    floor_area_code: null,
    storage_type_code: '',
    sub_area_code: null,
    position_code: '',
    capacity: 0,
    current_stock: 0,
    qr_code_data: null,
    notes: null,
    is_active: true,
  };
};

watch(() => props.location, (newLocation) => {
  if (newLocation) {
    isEditing.value = true;
    // Deep copy to avoid modifying prop directly
    form.value = JSON.parse(JSON.stringify(newLocation));
  } else {
    isEditing.value = false;
    resetForm();
  }
}, { immediate: true });

const apiUrl = import.meta.env.VITE_APP_URL;

const saveLocation = async () => {
  try {
    if (isEditing.value) {
      await axios.put(`${apiUrl}/locations/${form.value.id}`, form.value);
    } else {
      await axios.post(`${apiUrl}/locations`, form.value);
    }
    emit('saved');
    closeForm();
  } catch (error) {
    console.error('Error saving location:', error.response ? error.response.data : error);
    alert('Error saving location. Check console for details.');
  }
};

const closeForm = () => {
  emit('close');
  resetForm();
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
