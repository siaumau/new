<script setup>
import { ref } from 'vue';
import ItemTable from './components/ItemTable.vue';
import ItemForm from './components/ItemForm.vue';
import LocationTable from './components/LocationTable.vue';
import LocationForm from './components/LocationForm.vue';
import PosinTable from './components/PosinTable.vue';
import PosinForm from './components/PosinForm.vue';

const showItemForm = ref(false);
const selectedItem = ref(null);

const showLocationForm = ref(false);
const selectedLocation = ref(null);

const showPosinForm = ref(false);
const selectedPosin = ref(null);

const currentView = ref('items'); // 'items', 'locations', or 'posin'

const openNewItemForm = () => {
  selectedItem.value = null;
  showItemForm.value = true;
};

const openEditItemForm = (item) => {
  selectedItem.value = item;
  showItemForm.value = true;
};

const closeItemForm = () => {
  showItemForm.value = false;
  selectedItem.value = null;
};

const onItemSaved = () => {
  // Refresh the item list after saving
  // This assumes ItemTable has a method to refresh, or we can re-fetch here
  // For simplicity, we'll just close the form and let the table re-fetch on mount/update
};

const openNewLocationForm = () => {
  selectedLocation.value = null;
  showLocationForm.value = true;
};

const openEditLocationForm = (location) => {
  selectedLocation.value = location;
  showLocationForm.value = true;
};

const closeLocationForm = () => {
  showLocationForm.value = false;
  selectedLocation.value = null;
};

const onLocationSaved = () => {
  // Refresh the location list after saving
  // For simplicity, we'll just close the form and let the table re-fetch on mount/update
};

const openNewPosinForm = () => {
  selectedPosin.value = null;
  showPosinForm.value = true;
};

const openEditPosinForm = (posin) => {
  selectedPosin.value = posin;
  showPosinForm.value = true;
};

const closePosinForm = () => {
  showPosinForm.value = false;
  selectedPosin.value = null;
};

const onPosinSaved = () => {
  // Refresh the posin list after saving
  // For simplicity, we'll just close the form and let the table re-fetch on mount/update
};
</script>

<template>
  <header>
    <div class="wrapper">
      <h1 class="text-3xl font-bold text-center my-8">Inventory Management System</h1>
      <nav class="flex justify-center space-x-4 mb-4">
        <button @click="currentView = 'items'" :class="{'bg-blue-500 text-white': currentView === 'items', 'bg-gray-200 text-gray-800': currentView !== 'items'}" class="py-2 px-4 rounded">Items</button>
        <button @click="currentView = 'locations'" :class="{'bg-blue-500 text-white': currentView === 'locations', 'bg-gray-200 text-gray-800': currentView !== 'locations'}" class="py-2 px-4 rounded">Locations</button>
        <button @click="currentView = 'posin'" :class="{'bg-blue-500 text-white': currentView === 'posin', 'bg-gray-200 text-gray-800': currentView !== 'posin'}" class="py-2 px-4 rounded">Posin</button>
      </nav>
    </div>
  </header>

  <main>
    <ItemTable v-if="currentView === 'items'" @add-new="openNewItemForm" @edit-item="openEditItemForm" />
    <ItemForm :showForm="showItemForm" :item="selectedItem" @close="closeItemForm" @saved="onItemSaved" />

    <LocationTable v-if="currentView === 'locations'" @add-new="openNewLocationForm" @edit-location="openEditLocationForm" />
    <LocationForm :showForm="showLocationForm" :location="selectedLocation" @close="closeLocationForm" @saved="onLocationSaved" />

    <PosinTable v-if="currentView === 'posin'" @add-new="openNewPosinForm" @edit-posin="openEditPosinForm" />
    <PosinForm :showForm="showPosinForm" :posin="selectedPosin" @close="closePosinForm" @saved="onPosinSaved" />
  </main>
</template>

<style scoped>
/* You can keep or remove existing styles as needed */
header {
  line-height: 1.5;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }
}
</style>