<script setup>
import { ref } from 'vue';
import PurchaseOrderTable from '../components/PurchaseOrderTable.vue';
import PurchaseOrderForm from '../components/PurchaseOrderForm.vue';

const showPurchaseOrderForm = ref(false);
const selectedPurchaseOrder = ref(null);

const openNewPurchaseOrderForm = () => {
  selectedPurchaseOrder.value = null;
  showPurchaseOrderForm.value = true;
};

const openEditPurchaseOrderForm = (purchaseOrder) => {
  selectedPurchaseOrder.value = purchaseOrder;
  showPurchaseOrderForm.value = true;
};

const closePurchaseOrderForm = () => {
  showPurchaseOrderForm.value = false;
  selectedPurchaseOrder.value = null;
};

const onPurchaseOrderSaved = () => {
  // 儲存後重新整理訂單列表
  closePurchaseOrderForm();
};
</script>

<template>
  <div>
    <PurchaseOrderTable
      @add-new="openNewPurchaseOrderForm"
      @edit-purchase-order="openEditPurchaseOrderForm"
    />
    <PurchaseOrderForm
      :showForm="showPurchaseOrderForm"
      :purchaseOrder="selectedPurchaseOrder"
      @close="closePurchaseOrderForm"
      @saved="onPurchaseOrderSaved"
    />
  </div>
</template>
