<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Locations List</h1>
    <button @click="newLocation" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add New Location</button>
    <table class="min-w-full bg-white border border-gray-300">
      <thead>
        <tr>
          <th class="py-2 px-4 border-b">ID</th>
          <th class="py-2 px-4 border-b">Location Code</th>
          <th class="py-2 px-4 border-b">Location Name</th>
          <th class="py-2 px-4 border-b">Building Code</th>
          <th class="py-2 px-4 border-b">Floor Number</th>
          <th class="py-2 px-4 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="location in locations" :key="location.id">
          <td class="py-2 px-4 border-b">{{ location.id }}</td>
          <td class="py-2 px-4 border-b">{{ location.location_code }}</td>
          <td class="py-2 px-4 border-b">{{ location.location_name }}</td>
          <td class="py-2 px-4 border-b">{{ location.building_code }}</td>
          <td class="py-2 px-4 border-b">{{ location.floor_number }}</td>
          <td class="py-2 px-4 border-b">
            <button @click="editLocation(location)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">Edit</button>
            <button @click="deleteLocation(location.id)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const locations = ref([]);

const fetchLocations = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/locations');
    locations.value = response.data;
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

const newLocation = () => {
  // Emit an event to parent to open form for new location
  // For now, just log
  console.log('New location clicked');
};

const editLocation = (location) => {
  // Emit an event to parent to open form for editing
  // For now, just log
  console.log('Edit location clicked:', location);
};

const deleteLocation = async (id) => {
  if (confirm('Are you sure you want to delete this location?')) {
    try {
      await axios.delete(`http://localhost:8000/api/locations/${id}`);
      fetchLocations(); // Refresh the list
    } catch (error) {
      console.error('Error deleting location:', error);
    }
  }
};

onMounted(fetchLocations);
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
