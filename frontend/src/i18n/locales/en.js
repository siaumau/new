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
      supplier: 'Supplier',
      purchaseDate: 'Purchase Date',
      recordTime: 'Record Time',
      status: 'Status',
      itemsCount: 'Items Count',
      totalAmount: 'Total Amount',
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
    form: {
      supplier: 'Supplier',
      status: 'Status',
      purchaseDate: 'Purchase Date',
      expectedDeliveryDate: 'Expected Delivery Date',
      notes: 'Notes',
      items: 'Items',
      addItem: 'Add Item',
      item: 'Item',
      quantity: 'Quantity',
      price: 'Price',
      subtotal: 'Subtotal',
      actions: 'Actions',
      delete: 'Delete',
      totalAmount: 'Total Amount',
      cancel: 'Cancel',
      save: 'Save',
      processing: 'Processing...',
      selectSupplier: 'Select Supplier',
      selectItem: 'Select Item'
    },
    messages: {
      loading: 'Loading...',
      error: 'Unable to load purchase orders. Please try again later.',
      noData: 'No purchase orders found',
      deleteConfirm: 'Are you sure you want to delete this purchase order?',
      deleteError: 'Error deleting purchase order. Please try again later.',
      saveError: 'Error saving purchase order. Please try again later.',
      validation: {
        selectSupplier: 'Please select a supplier',
        enterPurchaseDate: 'Please enter purchase date',
        addOneItem: 'Please add at least one item',
        selectItemForAll: 'Please select an item for all entries',
        quantityPositive: 'Quantity must be greater than zero for all items',
        priceNonNegative: 'Price cannot be negative for any item'
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
