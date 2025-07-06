export default {
  app: {
    title: 'Inventory Management System'
  },
  purchaseOrders: {
    title: 'Purchase Order Management',
    new: 'New Purchase Order',
    edit: 'Edit Purchase Order',
    table: {
      orderNumber: 'Order Number',
      supplier: 'Order Creator',
      purchaseDate: 'Purchase Date',
      status: 'Status',
      itemsCount: 'Items Count',
      usPurchaseOrder: 'US Purchase Order',
      notes: 'Notes',
      actions: 'Actions'
    },
    status: {
      all: 'All Status',
      pending: 'Pending',
      processing: 'Processing',
      completed: 'Completed',
      cancelled: 'Cancelled'
    },
    usPurchaseOrderStatus: {
      pending: 'Pending',
      generated: 'Generated',
      reviewed: 'Reviewed'
    },
    form: {
      supplier: 'Order Creator',
      status: 'Status',
      purchaseDate: 'Purchase Date',
      expectedDeliveryDate: 'Expected Delivery Date',
      recordTime: 'Record Time',
      notes: 'Notes',
      items: 'Items',
      addItem: 'Add Item',
      item: 'Item',
      quantity: 'Quantity',
      batch: 'Batch Number',
      actions: 'Actions',
      delete: 'Delete',
      cancel: 'Cancel',
      save: 'Save',
      generateUsPurchaseOrder: 'Generate US Purchase Order',
      processing: 'Processing...',
      selectSupplier: 'Select Order Creator',
      selectItem: 'Select Item',
      enterBatch: 'Enter batch number'
    },
    messages: {
      loading: 'Loading...',
      error: 'Unable to load purchase orders. Please try again later.',
      noData: 'No purchase orders found',
      deleteConfirm: 'Are you sure you want to delete this purchase order?',
      deleteError: 'Error deleting purchase order. Please try again later.',
      saveError: 'Error saving purchase order. Please try again later.',
      generateUsPurchaseOrderConfirm: 'Are you sure you want to generate a US purchase order?\n\nAfter submission, you cannot modify the content.\nDTC backend will automatically generate a US purchase order for review.',
      generateUsPurchaseOrderSuccess: 'US purchase order generated successfully!\n\nThis order cannot be edited anymore.',
      generateUsPurchaseOrderError: 'Error generating US purchase order. Please try again later.',
      generateUsPurchaseOrderAlreadyGenerated: 'This order has already generated a US purchase order and cannot be generated again.',
      cannotEditAfterGenerated: 'US purchase order has been generated, cannot edit',
      validation: {
        selectSupplier: 'Please select an order creator',
        enterPurchaseDate: 'Please enter purchase date',
        addOneItem: 'Please add at least one item',
        selectItemForAll: 'Please select an item for all entries',
        quantityPositive: 'Quantity must be greater than zero for all items'
      }
    },
    pagination: {
      showing: 'Showing',
      to: 'to',
      of: 'of',
      items: 'items',
      prev: 'Previous',
      next: 'Next'
    },
    search: {
      placeholder: 'Search order number, supplier...',
      button: 'Search'
    },
    edit: 'Edit',
    delete: 'Delete'
  }
}
