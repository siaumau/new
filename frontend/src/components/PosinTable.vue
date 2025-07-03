<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Posin Records</h1>
    <button @click="newPosin" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add New Posin</button>
    <table class="min-w-full bg-white border border-gray-300">
      <thead>
        <tr>
          <th class="py-2 px-4 border-b">ID</th>
          <th class="py-2 px-4 border-b">SN</th>
          <th class="py-2 px-4 border-b">User</th>
          <th class="py-2 px-4 border-b">Date</th>
          <th class="py-2 px-4 border-b">Note</th>
          <th class="py-2 px-4 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="posin in posins" :key="posin.posin_id">
          <td class="py-2 px-4 border-b">{{ posin.posin_id }}</td>
          <td class="py-2 px-4 border-b">{{ posin.posin_sn }}</td>
          <td class="py-2 px-4 border-b">{{ posin.posin_user }}</td>
          <td class="py-2 px-4 border-b">{{ posin.posin_dt }}</td>
          <td class="py-2 px-4 border-b">{{ posin.posin_note }}</td>
          <td class="py-2 px-4 border-b">
            <button @click="editPosin(posin)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
            <button @click="deletePosin(posin.posin_id)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const posins = ref([]);

const fetchPosins = async () => {
  try {
    const response = await axios.get('http://localhost:8000/posin');
    posins.value = response.data;
  } catch (error) {
    console.error('Error fetching posin records:', error);
  }
};

const newPosin = () => {
  // Emit an event to parent to open form for new posin
  // For now, just log
  console.log('New posin clicked');
};

const editPosin = (posin) => {
  // Emit an event to parent to open form for editing
  // For now, just log
  console.log('Edit posin clicked:', posin);
};

const deletePosin = async (id) => {
  if (confirm('Are you sure you want to delete this posin record?')) {
    try {
      await axios.delete(`http://localhost:8000/posin/${id}`);
      fetchPosins(); // Refresh the list
    } catch (error) {
      console.error('Error deleting posin record:', error);
    }
  }
};

onMounted(fetchPosins);
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
