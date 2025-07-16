<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" v-if="showForm">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">{{ isEditing ? 'Edit Item' : 'Add New Item' }}</h3>
      <form @submit.prevent="saveItem">
        <div class="mb-4">
          <label for="item_name" class="block text-gray-700 text-sm font-bold mb-2">Item Name:</label>
          <input type="text" id="item_name" v-model="form.item_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="item_sn" class="block text-gray-700 text-sm font-bold mb-2">Item SN:</label>
          <input type="text" id="item_sn" v-model="form.item_sn" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
        </div>
        <div class="mb-4">
          <label for="item_spec" class="block text-gray-700 text-sm font-bold mb-2">Item Spec:</label>
          <input type="text" id="item_spec" v-model="form.item_spec" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
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
  item: Object,
  showForm: Boolean,
});

const emit = defineEmits(['close', 'saved']);

const isEditing = ref(false);
const form = ref({
  item_name: '',
  item_cid: 1, // Default value
  item_sn: '',
  item_spec: '',
  item_eng: null,
  item_save: 0,
  item_save2: null,
  item_price: 0,
  suggested_retail_price: 0,
  item_note: '',
  item_open: 1,
  item_sort: 0,
  item_mstock: 0,
  item_type: '',
  item_years: null,
  item_holdmonth: 0,
  item_outvyear: '',
  item_predict: 0,
  item_insertdate: new Date().toISOString().slice(0, 19).replace('T', ' '),
  item_editdate: new Date().toISOString().slice(0, 19).replace('T', ' '),
  item_barcode: '',
  item_inbox: 0,
  ppt_id: 0,
  item_vcode: null,
  item_size: '',
});

const resetForm = () => {
  form.value = {
    item_name: '',
    item_cid: 1,
    item_sn: '',
    item_spec: '',
    item_eng: null,
    item_save: 0,
    item_save2: null,
    item_price: 0,
    suggested_retail_price: 0,
    item_note: '',
    item_open: 1,
    item_sort: 0,
    item_mstock: 0,
    item_type: '',
    item_years: null,
    item_holdmonth: 0,
    item_outvyear: '',
    item_predict: 0,
    item_insertdate: new Date().toISOString().slice(0, 19).replace('T', ' '),
    item_editdate: new Date().toISOString().slice(0, 19).replace('T', ' '),
    item_barcode: '',
    item_inbox: 0,
    ppt_id: 0,
    item_vcode: null,
    item_size: '',
  };
};

watch(() => props.item, (newItem) => {
  if (newItem) {
    isEditing.value = true;
    // Deep copy to avoid modifying prop directly
    form.value = JSON.parse(JSON.stringify(newItem));
  } else {
    isEditing.value = false;
    resetForm();
  }
}, { immediate: true });

const saveItem = async () => {
  try {
    if (isEditing.value) {
      await axios.put(`/api/v1/items/${form.value.item_id}`, form.value);
    } else {
      await axios.post(`/api/v1/items`, form.value);
    }
    emit('saved');
    closeForm();
  } catch (error) {
    console.error('Error saving item:', error.response ? error.response.data : error);
    alert('Error saving item. Check console for details.');
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
