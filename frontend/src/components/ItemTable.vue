<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Items List</h1>
    <button @click="newItem" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add New Item</button>
    <table class="min-w-full bg-white border border-gray-300">
      <thead>
        <tr>
          <th class="py-2 px-4 border-b">ID</th>
          <th class="py-2 px-4 border-b">Name</th>
          <th class="py-2 px-4 border-b">SN</th>
          <th class="py-2 px-4 border-b">Spec</th>
          <th class="py-2 px-4 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.item_id">
          <td class="py-2 px-4 border-b">{{ item.item_id }}</td>
          <td class="py-2 px-4 border-b">{{ item.item_name }}</td>
          <td class="py-2 px-4 border-b">{{ item.item_sn }}</td>
          <td class="py-2 px-4 border-b">{{ item.item_spec }}</td>
          <td class="py-2 px-4 border-b">
            <button @click="editItem(item)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
            <button @click="deleteItem(item.item_id)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);

const fetchItems = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/items');
    items.value = response.data;
  } catch (error) {
    console.error('Error fetching items:', error);
  }
};

const newItem = () => {
  // Emit an event to parent to open form for new item
  // For now, just log
  console.log('New item clicked');
};

const editItem = (item) => {
  // Emit an event to parent to open form for editing
  // For now, just log
  console.log('Edit item clicked:', item);
};

const deleteItem = async (id) => {
  if (confirm('Are you sure you want to delete this item?')) {
    try {
      await axios.delete(`http://localhost:8000/api/items/${id}`);
      fetchItems(); // Refresh the list
    } catch (error) {
      console.error('Error deleting item:', error);
    }
  }
};

onMounted(fetchItems);
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
