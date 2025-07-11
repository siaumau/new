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
  },
  posinItems: {
    title: 'Purchase Order Items',
    table: {
      itemSN: 'Item SN',
      itemName: 'Item Name',
      spec: 'Specification',
      quantity: 'Quantity',
      packageSpec: 'Package Spec',
      boxCount: 'Box Count',
      expiryDate: 'Expiry Date',
      actions: 'Actions',
      generateQR: 'Generate QR',
      delete: 'Delete',
      unit: 'pcs/box',
      box: 'box'
    },
    actions: {
      convertToUsPurchaseOrder: 'Convert to US Purchase Order',
      refresh: 'Refresh',
      deleteItem: 'Delete Item',
      generateQR: 'Generate QR Labels'
    },
    messages: {
      loading: 'Loading...',
      pageDescription: 'Manage purchase order items and generate QR labels',
      itemList: 'Item List',
      totalItems: 'Total {count} items',
      noItems: 'No items found',
      noItemsDescription: 'No items have been added to this purchase order',
      deleteConfirm: 'Are you sure you want to delete item "{name}"?',
      deleteSuccess: 'Item deleted successfully',
      deleteError: 'Error deleting item. Please try again later',
      convertConfirm: 'Are you sure you want to convert to US purchase order?\n\nAfter conversion, you cannot modify this purchase order.\nThe system will automatically generate a US purchase order for further processing.',
      convertSuccess: 'US purchase order generated successfully!',
      convertError: 'Error converting to US purchase order. Please try again later',
      convertAlreadyGenerated: 'This purchase order has already been converted to US purchase order and cannot be converted again'
    },
    qrCode: {
      title: 'Item QR Code Generation',
      itemSN: 'Item SN:',
      batch: 'Batch:',
      spec: 'Specification:',
      quantity: 'Total Quantity:',
      packageSpec: 'Package Spec:',
      boxCount: 'Box Count Required:',
      purchaseDate: 'Purchase Date:',
      expiryDate: 'Expiry Date:',
      boxQRTitle: 'Box QR Code Label Generation',
      description: 'This item requires {count} box QR code labels (one per box)',
      labelInfo: 'Each label contains: QR Code + Coding Info + Item Details',
      codeFormat: 'Code Format: SKU + Batch + Expiry Date + Serial Number (e.g., {example})',
      generateCount: 'Generate Count:',
      generateCountUnit: 'labels',
      download: 'Download {count} Box Labels',
      preview: 'Preview Label Style',
      cancel: 'Cancel',
      close: 'Close'
    }
  },
  locations: {
    title: 'Location Management',
    description: 'Manage inventory locations and warehouse information',
    list: 'Location List',
    addNew: 'Add New',
    search: {
      placeholder: 'Search location code or name...',
      button: 'Product Search',
      allBuildings: 'All Buildings',
      allCategories: 'All Categories',
      allDates: 'All Dates',
      today: 'Today',
      yesterday: 'Yesterday',
      thisWeek: 'This Week',
      lastWeek: 'Last Week',
      thisMonth: 'This Month',
      lastMonth: 'Last Month',
      customDate: 'Custom Date',
      downloadTemplate: 'Download Template',
      import: 'Import'
    },
    table: {
      locationCode: 'Location Code',
      locationName: 'Location Name',
      building: 'Building',
      storageType: 'Storage Type',
      storageCode: 'Storage Code',
      createdAt: 'Created At',
      capacity: 'Capacity',
      stock: 'Stock',
      utilization: 'Utilization',
      notes: 'Notes',
      actions: 'Actions'
    },
    actions: {
      details: 'Details',
      qrCode: 'QR Code',
      edit: 'Edit',
      delete: 'Delete'
    },
    buildings: {
      taipei: 'Taipei',
      changhua: 'Changhua'
    },
    categories: {
      area: 'Pallet',
      shelf: 'Shelf'
    },
    pagination: {
      itemsPerPage: 'Items per page',
      page: 'Page',
      totalPages: 'of',
      pages: 'pages',
      prev: 'Previous',
      next: 'Next'
    },
    modal: {
      editTitle: 'Edit Location',
      locationCode: 'Location Code',
      locationName: 'Location Name',
      building: 'Building',
      storageType: 'Storage Type',
      floor: 'Floor Code',
      storageSmallCode: 'Storage Small Area/Layer Code',
      storageCode: 'Storage Code',
      capacity: 'Capacity',
      currentStock: 'Current Stock',
      qrCodeData: 'QR Code Data',
      notes: 'Notes',
      enabled: 'Enabled',
      cancel: 'Cancel',
      save: 'Save',
      close: 'Close'
    },
    qrModal: {
      title: 'Location QR Code',
      close: 'Close'
    },
    batchPrint: {
      title: 'Batch Print QR Codes',
      description: 'Select locations to batch print QR codes',
      selectedCount: 'Selected {count} locations for batch printing',
      noSelection: 'Please select at least one location for batch printing',
      printButton: 'Print {count} QR Codes',
      processing: 'Processing...',
      cancel: 'Cancel',
      close: 'Close',
      enableMode: 'Batch Print QR Codes',
      startPrint: 'Start Batch Print QR Codes',
      selectedLocations: 'Selected {count} locations',
      startBatchPrint: 'Start Batch Print QR Codes',
      cancelBatchPrint: 'Cancel Batch Print',
      selectAllFiltered: 'Select All Filtered'
    }
  },
  qrCodes: {
    title: 'QR Code Label Management',
    description: 'Manage generated QR code labels, including print status and location assignment',
    stats: {
      total: 'Total Labels',
      withLocation: 'With Location',
      withoutLocation: 'Without Location',
      recentGenerated: 'Recent 7 Days'
    },
    search: {
      placeholder: 'Search item code, name, batch...',
      button: 'Search'
    },
    filters: {
      status: 'Status',
      location: 'Location',
      inboxStatus: 'Inbox Status',
      allStatus: 'All Status',
      allLocations: 'All Locations',
      allInboxStatus: 'All Inbox Status'
    },
    status: {
      generated: 'Generated',
      printed: 'Printed',
      used: 'Used'
    },
    inboxStatus: {
      pending: 'Pending',
      completed: 'Completed'
    },
    table: {
      itemCode: 'Item Code',
      itemName: 'Item Name',
      batch: 'Batch',
      boxNumber: 'Box Number',
      location: 'Location',
      status: 'Status',
      inboxStatus: 'Inbox Status',
      generatedAt: 'Generated At',
      actions: 'Actions',
      noLocation: 'Not Assigned'
    },
    actions: {
      assignLocation: 'Assign Location',
      updateStatus: 'Update Status'
    },
    batch: {
      selected: 'Selected {count} items',
      selectAction: 'Select batch action',
      markAsPrinted: 'Mark as Printed',
      markAsUsed: 'Mark as Used',
      execute: 'Execute'
    },
    modals: {
      assignLocation: {
        title: 'Assign Location',
        location: 'Location',
        floorLevel: 'Floor Level',
        selectLocation: 'Select Location',
        floorLevelPlaceholder: 'Enter floor level (optional)'
      },
      updateStatus: {
        title: 'Update Status',
        status: 'Status'
      },
      cancel: 'Cancel',
      assign: 'Assign',
      update: 'Update'
    },
    pagination: {
      showing: 'Showing',
      to: 'to',
      of: 'of',
      results: 'results',
      prev: 'Previous',
      next: 'Next'
    },
    messages: {
      loading: 'Loading...',
      loadError: 'Failed to load QR code labels. Please try again later',
      noData: 'No QR code labels found',
      assignLocationError: 'Failed to assign location. Please try again later',
      updateStatusError: 'Failed to update status. Please try again later',
      batchActionConfirm: 'Are you sure you want to execute this action on {count} selected items?',
      batchActionError: 'Batch action failed. Please try again later'
    }
  },
  common: {
    loading: 'Loading...',
    actions: 'Actions',
    edit: 'Edit',
    delete: 'Delete',
    yes: 'Enabled',
    no: 'Disabled'
  },
  scanAndPlace: {
    title: 'Scan & Place',
    description: 'Use scanning functionality for quick product placement operations',
    options: {
      firstBinding: '01. Product Binding (First Warehousing)',
      processShipping: '02. Processing/Shipping'
    },
    firstBinding: {
      title: 'Product Binding (First Warehousing)',
      description: 'Bind new products to cabinet positions and set as warehoused status',
      step: 'Step',
      step1: '1. Scan Cabinet QR Code',
      step2: '2. Scan Product Box QR Code',
      scanLocation: 'Scan Cabinet QR Code',
      scanBox: 'Scan Product Box QR Code',
      locationCode: 'Cabinet Code',
      locationPlaceholder: 'Please scan or enter cabinet QR code',
      boxCode: 'Box QR Code',
      boxPlaceholder: 'Please scan or enter box QR code',
      bindingOptions: 'Binding Options',
      bindOnlyOption: 'Bind Only (No Warehousing)',
      bindAndInboxOption: 'Bind & Warehouse (Default)',
      confirm: 'Confirm Binding',
      reset: 'Reset',
      status: {
        waitingLocation: 'Waiting for cabinet scan',
        waitingBox: 'Waiting for product box scan',
        ready: 'Ready to bind'
      },
      messages: {
        locationScanned: 'Cabinet scanned: {location}',
        boxScanned: 'Product box scanned: {box}',
        bindingSuccess: 'Binding successful!',
        bindingError: 'Binding failed, please try again',
        invalidLocation: 'Invalid cabinet QR code',
        invalidBox: 'Invalid product box QR code',
        alreadyBound: 'This product box is already bound to another cabinet'
      }
    },
    processShipping: {
      title: 'Processing/Shipping',
      description: 'Handle outbound operations for placed products',
      scanBox: 'Scan Product Box QR Code',
      boxCode: 'Box QR Code',
      boxPlaceholder: 'Please scan or enter box QR code',
      outboundType: 'Outbound Type',
      processing: 'Processing (Default)',
      shipping: 'Shipping',
      confirm: 'Confirm Outbound',
      reset: 'Reset',
      status: {
        waitingBox: 'Waiting for product box scan',
        ready: 'Ready for outbound'
      },
      messages: {
        boxScanned: 'Product box scanned: {box}',
        processingSuccess: 'Processing outbound successful! Product moved to CH 7F Processing Area',
        shippingSuccess: 'Shipping successful! Product marked as shipped',
        outboundError: 'Outbound failed, please try again',
        invalidBox: 'Invalid product box QR code',
        notBound: 'This product box is not bound to any cabinet',
        notInStock: 'This product box is not in stock'
      }
    },
    returnToStock: {
      title: 'Return to Cabinet After Processing',
      description: 'Return processed products to designated cabinet',
      step: 'Step',
      step1: '1. Scan Target Cabinet QR Code',
      step2: '2. Scan Product Box QR Code',
      scanLocation: 'Scan Target Cabinet QR Code',
      scanBox: 'Scan Product Box QR Code',
      locationCode: 'Cabinet Code',
      locationPlaceholder: 'Please scan or enter cabinet QR code',
      boxCode: 'Box QR Code',
      boxPlaceholder: 'Please scan or enter box QR code',
      confirm: 'Confirm Return',
      reset: 'Reset',
      status: {
        waitingLocation: 'Waiting for target cabinet scan',
        waitingBox: 'Waiting for product box scan',
        ready: 'Ready to return'
      },
      messages: {
        locationScanned: 'Target cabinet scanned: {location}',
        boxScanned: 'Product box scanned: {box}',
        returnSuccess: 'Return successful! Product warehoused to designated cabinet',
        returnError: 'Return failed, please try again',
        invalidLocation: 'Invalid cabinet QR code',
        invalidBox: 'Invalid product box QR code',
        notFromProcessing: 'This product box is not from processing area'
      }
    },
    common: {
      scan: 'Scan',
      cancel: 'Cancel',
      confirm: 'Confirm',
      reset: 'Reset',
      back: 'Back',
      next: 'Next',
      loading: 'Processing...',
      scanSuccessful: 'Scan successful',
      scanError: 'Scan failed, please try again',
      networkError: 'Network error, please check connection',
      systemError: 'System error, please contact administrator'
    }
  }
}
