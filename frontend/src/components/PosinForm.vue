<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" v-if="showForm">
    <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
      <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">{{ isEditing ? 'Edit Posin Record' : 'Add New Posin Record' }}</h3>
      <form @submit.prevent="savePosin">
        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4">
            <label for="_users_id" class="block text-gray-700 text-sm font-bold mb-2">User ID:</label>
            <input type="number" id="_users_id" v-model="form._users_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="posin_sn" class="block text-gray-700 text-sm font-bold mb-2">Posin SN:</label>
            <input type="text" id="posin_sn" v-model="form.posin_sn" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="posin_user" class="block text-gray-700 text-sm font-bold mb-2">Posin User:</label>
            <input type="text" id="posin_user" v-model="form.posin_user" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label for="posin_dt" class="block text-gray-700 text-sm font-bold mb-2">Posin Date:</label>
            <input type="datetime-local" id="posin_dt" v-model="form.posin_dt" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4 col-span-2">
            <label for="posin_note" class="block text-gray-700 text-sm font-bold mb-2">Posin Note:</label>
            <textarea id="posin_note" v-model="form.posin_note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
          </div>
        </div>

        <h4 class="text-xl font-bold mt-6 mb-4">Posin Items</h4>
        <div v-for="(item, index) in form.posin_items" :key="index" class="grid grid-cols-3 gap-4 border p-4 mb-4 rounded">
          <div class="mb-4">
            <label :for="`itemtype-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Type:</label>
            <input type="number" :id="`itemtype-${index}`" v-model="item.itemtype" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_id-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item ID:</label>
            <input type="number" :id="`item_id-${index}`" v-model="item.item_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_name-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Name:</label>
            <input type="text" :id="`item_name-${index}`" v-model="item.item_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_sn-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item SN:</label>
            <input type="text" :id="`item_sn-${index}`" v-model="item.item_sn" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_spec-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Spec:</label>
            <input type="text" :id="`item_spec-${index}`" v-model="item.item_spec" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_batch-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Batch:</label>
            <input type="text" :id="`item_batch-${index}`" v-model="item.item_batch" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_count-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Count:</label>
            <input type="number" :id="`item_count-${index}`" v-model="item.item_count" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
          </div>
          <div class="mb-4">
            <label :for="`item_price-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Item Price:</label>
            <input type="number" :id="`item_price-${index}`" v-model="item.item_price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" step="0.01" required>
          </div>
          <div class="mb-4">
            <label :for="`item_expireday-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Expire Date:</label>
            <input type="date" :id="`item_expireday-${index}`" v-model="item.item_expireday" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <div class="mb-4">
            <label :for="`item_validyear-${index}`" class="block text-gray-700 text-sm font-bold mb-2">Valid Year:</label>
            <input type="text" :id="`item_validyear-${index}`" v-model="item.item_validyear" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
          </div>
          <button type="button" @click="removePosinItem(index)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded col-span-3">Remove Item</button>
        </div>
        <button type="button" @click="addPosinItem" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">Add Item</button>

        <div class="flex items-center justify-between mt-6">
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
  posin: Object,
  showForm: Boolean,
});

const emit = defineEmits(['close', 'saved']);

const isEditing = ref(false);
const form = ref({
  _users_id: 1, // Default user ID
  posin_sn: '',
  posin_user: '',
  posin_dt: new Date().toISOString().slice(0, 16), // YYYY-MM-DDTHH:mm format for datetime-local
  posin_log: null,
  posin_note: '',
  posin_items: [],
});

const defaultPosinItem = () => ({
  itemtype: 1,
  item_id: 0,
  item_name: '',
  item_sn: '',
  item_spec: '',
  item_batch: '',
  item_count: 0,
  item_price: 0,
  item_expireday: null,
  item_validyear: null,
});

watch(() => props.posin, (newPosin) => {
  if (newPosin) {
    isEditing.value = true;
    // Deep copy to avoid modifying prop directly
    const tempPosin = JSON.parse(JSON.stringify(newPosin));
    // Format posin_dt for datetime-local input
    if (tempPosin.posin_dt) {
      tempPosin.posin_dt = tempPosin.posin_dt.slice(0, 16);
    }
    // Format item_expireday for date input
    tempPosin.posin_items = tempPosin.posin_items.map(item => {
      if (item.item_expireday) {
        item.item_expireday = item.item_expireday.slice(0, 10);
      }
      return item;
    });
    form.value = tempPosin;
  } else {
    isEditing.value = false;
    resetForm();
  }
}, { immediate: true });

const addPosinItem = () => {
  form.value.posin_items.push(defaultPosinItem());
};

const removePosinItem = (index) => {
  form.value.posin_items.splice(index, 1);
};

const savePosin = async () => {
  try {
    // Format dates back to database format if needed, or let Laravel handle it
    const payload = JSON.parse(JSON.stringify(form.value));
    if (payload.posin_dt) {
      payload.posin_dt = payload.posin_dt + ':00'; // Add seconds for datetime format
    }
    payload.posin_items = payload.posin_items.map(item => {
      if (item.item_expireday) {
        item.item_expireday = item.item_expireday + ' 00:00:00';
      }
      return item;
    });

    if (isEditing.value) {
      await axios.put(`http://localhost:8000/api/posin/${form.value.posin_id}`, payload);
    } else {
      await axios.post('http://localhost:8000/api/posin', payload);
    }
    emit('saved');
    closeForm();
  } catch (error) {
    console.error('Error saving posin record:', error.response ? error.response.data : error);
    alert('Error saving posin record. Check console for details.');
  }
};

const closeForm = () => {
  emit('close');
  resetForm();
};

const resetForm = () => {
  form.value = {
    _users_id: 1,
    posin_sn: '',
    posin_user: '',
    posin_dt: new Date().toISOString().slice(0, 16),
    posin_log: null,
    posin_note: '',
    posin_items: [],
  };
};
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
