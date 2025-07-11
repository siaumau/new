export default {
  app: {
    title: '庫存管理系統'
  },
  purchaseOrders: {
    title: '進貨訂單管理',
    new: '新增進貨訂單',
    edit: '編輯進貨訂單',
    table: {
      orderNumber: '進貨單號',
      supplier: '建單人員',
      purchaseDate: '進貨日期',
      status: '狀態',
      itemsCount: '項目數',
      usPurchaseOrder: '美國進貨單',
      notes: '備註',
      actions: '操作'
    },
    status: {
      all: '所有狀態',
      pending: '待處理',
      processing: '處理中',
      completed: '已完成',
      cancelled: '已取消'
    },
    usPurchaseOrderStatus: {
      pending: '待處理',
      generated: '已產生',
      reviewed: '已審查'
    },
    form: {
      supplier: '建單人員',
      status: '狀態',
      purchaseDate: '進貨日期',
      expectedDeliveryDate: '預計到貨日期',
      recordTime: '紀錄時間',
      notes: '備註',
      items: '商品項目',
      addItem: '添加項目',
      item: '商品',
      quantity: '數量',
      batch: '批號',
      actions: '操作',
      delete: '刪除',
      cancel: '取消',
      save: '保存',
      generateUsPurchaseOrder: '自動產生美國進貨單',
      processing: '處理中...',
      selectSupplier: '選擇建單人員',
      selectItem: '選擇商品',
      enterBatch: '輸入批號'
    },
    messages: {
      loading: '載入中...',
      error: '無法載入進貨訂單資料。請稍後再試。',
      noData: '沒有找到任何進貨訂單',
      deleteConfirm: '確定要刪除此進貨訂單？',
      deleteError: '刪除進貨訂單時發生錯誤。請稍後再試。',
      saveError: '保存進貨訂單時發生錯誤。請稍後再試。',
      generateUsPurchaseOrderConfirm: '確定要產生美國進貨單嗎？\n\n送出後無法再針對內容做修改調整。\nDTC後台會自動產生一個筆美國進貨單供審查。',
      generateUsPurchaseOrderSuccess: '美國進貨單已成功產生！\n\n此筆訂單將無法再進行編輯。',
      generateUsPurchaseOrderError: '產生美國進貨單時發生錯誤。請稍後再試。',
      generateUsPurchaseOrderAlreadyGenerated: '此筆訂單已經產生過美國進貨單，無法重複產生。',
      cannotEditAfterGenerated: '美國進貨單已產生，無法編輯',
      validation: {
        selectSupplier: '請選擇建單人員',
        enterPurchaseDate: '請輸入進貨日期',
        addOneItem: '請至少添加一個商品項目',
        selectItemForAll: '請為所有項目選擇商品',
        quantityPositive: '所有項目的數量必須大於零'
      }
    },
    pagination: {
      showing: '顯示',
      to: '至',
      of: '項，共',
      items: '項',
      prev: '上一頁',
      next: '下一頁'
    },
    search: {
      placeholder: '搜尋進貨單號、供應商...',
      button: '搜尋'
    },
    edit: '編輯',
    delete: '刪除'
  },
  posinItems: {
    title: '進貨單商品項目',
    table: {
      itemSN: '商品序號',
      itemName: '商品名稱',
      spec: '規格',
      quantity: '數量',
      packageSpec: '包裝規格',
      boxCount: '箱數',
      expiryDate: '有效期限',
      actions: '操作',
      generateQR: '生成QR',
      delete: '刪除',
      unit: '個/箱',
      box: '箱'
    },
    actions: {
      convertToUsPurchaseOrder: '轉美國進貨單',
      refresh: '刷新',
      deleteItem: '刪除商品項目',
      generateQR: '生成QR標籤'
    },
    messages: {
      loading: '載入中...',
      pageDescription: '管理進貨單商品項目，生成QR標籤',
      itemList: '商品項目清單',
      totalItems: '共 {count} 項商品',
      noItems: '暫無商品項目',
      noItemsDescription: '此進貨單尚未添加任何商品',
      deleteConfirm: '確定要刪除商品「{name}」嗎？',
      deleteSuccess: '商品項目已成功刪除',
      deleteError: '刪除商品項目時發生錯誤，請稍後再試',
      convertConfirm: '確定要轉換為美國進貨單嗎？\n\n轉換後將無法修改此進貨單的內容。\n系統將自動生成美國進貨單供後續處理。',
      convertSuccess: '美國進貨單已成功生成！',
      convertError: '轉換美國進貨單時發生錯誤，請稍後再試',
      convertAlreadyGenerated: '此進貨單已經轉換過美國進貨單，無法重複轉換'
    },
    qrCode: {
      title: '商品QR Code生成',
      itemSN: '商品序號:',
      batch: '批號:',
      spec: '規格:',
      quantity: '總數量:',
      packageSpec: '包裝規格:',
      boxCount: '需要箱數:',
      purchaseDate: '進貨日期:',
      expiryDate: '有效期限:',
      boxQRTitle: '品 外箱 QR Code 標籤生成',
      description: '此商品需要 {count} 張外箱 QR Code 標籤 (每箱一張)',
      labelInfo: '每張標籤包含：QR Code + 編碼資訊 + 商品詳情',
      codeFormat: '編碼格式：SKU + 批號 + 效期 + 流水號 (如：{example})',
      generateCount: '生成數量:',
      generateCountUnit: '張',
      download: '下載 {count} 張外箱標籤',
      preview: '預覽標籤樣式',
      cancel: '取消',
      close: '關閉'
    }
  },
  locations: {
    title: '位置管理',
    description: '管理庫存位置和倉庫資訊',
    list: '位置列表',
    addNew: '新增',
    search: {
      placeholder: '搜尋位置代碼或名稱...',
      button: '商品搜尋',
      allBuildings: '全部建築',
      allCategories: '全部類別',
      downloadTemplate: '下載模板',
      import: '匯入'
    },
    table: {
      locationCode: '位置代碼',
      locationName: '位置名稱',
      building: '建築',
      storageType: '存放類別',
      storageCode: '存放代碼',
      capacity: '容量',
      stock: '庫存',
      utilization: '使用率',
      notes: '備註',
      actions: '操作'
    },
    actions: {
      details: '明細',
      qrCode: 'QR Code',
      edit: '編輯',
      delete: '刪除'
    },
    buildings: {
      taipei: '台北',
      changhua: '彰化'
    },
    categories: {
      area: '棧板',
      shelf: '層架'
    },
    pagination: {
      itemsPerPage: '每頁顯示',
      page: '第',
      totalPages: '共',
      pages: '頁',
      prev: '上一頁',
      next: '下一頁'
    },
    modal: {
      editTitle: '編輯位置',
      locationCode: '位置代碼',
      locationName: '位置名稱',
      building: '建築',
      storageType: '存放類別',
      floor: '層架區碼',
      storageSmallCode: '存放小區/層代碼',
      storageCode: '存放代碼',
      capacity: '容量',
      currentStock: '目前庫存',
      qrCodeData: 'QR Code資料',
      notes: '備註',
      enabled: '是否啟用',
      cancel: '取消',
      save: '儲存',
      close: '關閉'
    },
    qrModal: {
      title: '位置 QR Code',
      close: '關閉'
    },
    batchPrint: {
      title: '批次列印 QR Code',
      description: '選擇要批次列印的位置 QR Code',
      selectedCount: '已選擇 {count} 個位置進行批次列印',
      noSelection: '請至少選擇一個位置進行批次列印',
      printButton: '列印 {count} 個 QR Code',
      processing: '處理中...',
      cancel: '取消',
      close: '關閉',
      enableMode: '批次列印 QR Code',
      startPrint: '開始列印批次 QR Code',
      selectedLocations: '已選擇 {count} 個位置',
      startBatchPrint: '開始列印批次 QR Code',
      cancelBatchPrint: '取消批次列印'
    }
  },
  qrCodes: {
    title: 'QR Code 標籤管理',
    description: '管理已生成的 QR Code 標籤，包含列印狀態、位置分配等功能',
    stats: {
      total: '總標籤數',
      withLocation: '已分配位置',
      withoutLocation: '未分配位置',
      recentGenerated: '近7天生成'
    },
    search: {
      placeholder: '搜尋商品代碼、名稱、批號...',
      button: '搜尋'
    },
    filters: {
      status: '狀態',
      location: '位置',
      inboxStatus: '入庫狀態',
      allStatus: '所有狀態',
      allLocations: '所有位置',
      allInboxStatus: '所有入庫狀態'
    },
    status: {
      generated: '已生成',
      printed: '已列印',
      used: '已使用'
    },
    inboxStatus: {
      pending: '待入庫',
      completed: '已入庫'
    },
    table: {
      itemCode: '商品代碼',
      itemName: '商品名稱',
      batch: '批號',
      boxNumber: '箱號',
      location: '位置',
      status: '狀態',
      inboxStatus: '入庫狀態',
      generatedAt: '生成時間',
      actions: '操作',
      noLocation: '未分配'
    },
    actions: {
      assignLocation: '分配位置',
      updateStatus: '更新狀態',
      scanAssign: '掃描歸位'
    },
    batch: {
      selected: '已選擇 {count} 項',
      selectAction: '選擇批次操作',
      markAsPrinted: '標記為已列印',
      markAsUsed: '標記為已使用',
      execute: '執行'
    },
    modals: {
      assignLocation: {
        title: '分配位置',
        location: '位置',
        floorLevel: '層架',
        selectLocation: '選擇位置',
        floorLevelPlaceholder: '輸入層架編號（選填）'
      },
      updateStatus: {
        title: '更新狀態',
        status: '狀態'
      },
      scan: {
        title: '掃描歸位',
        mode: '掃描模式',
        box: '掃描箱子QR Code',
        location: '掃描位置QR Code',
        qrCode: '箱子QR Code',
        locationQRCode: '位置QR Code',
        boxPlaceholder: '掃描箱子QR Code',
        locationPlaceholder: '掃描位置QR Code',
        scan: '掃描'
      },
      scanResult: {
        title: '掃描結果'
      },
      cancel: '取消',
      assign: '分配',
      update: '更新',
      close: '關閉'
    },
    pagination: {
      showing: '顯示',
      to: '至',
      of: '項，共',
      results: '筆結果',
      prev: '上一頁',
      next: '下一頁'
    },
    messages: {
      loading: '載入中...',
      loadError: '載入 QR Code 標籤失敗，請稍後再試',
      noData: '沒有找到任何 QR Code 標籤',
      assignLocationError: '分配位置失敗',
      updateStatusError: '更新狀態失敗',
      batchActionConfirm: '確定要對選中的 {count} 項執行此操作嗎？',
      batchActionError: '批次操作失敗，請稍後再試'
    }
  },
  common: {
    loading: '載入中...',
    actions: '操作',
    edit: '編輯',
    delete: '刪除',
    yes: '啟用',
    no: '停用'
  },
  movementHistory: {
    title: '移動歷史',
    description: '查看所有箱子的移動記錄和歷史軌跡',
    stats: {
      total: '總移動次數',
      assignments: '分配次數',
      moves: '移動次數',
      returns: '歸位次數'
    },
    search: {
      itemCode: '商品代碼',
      itemCodePlaceholder: '搜尋商品代碼或名稱...',
      button: '搜尋'
    },
    filters: {
      movementType: '移動類型',
      dateRange: '日期範圍',
      allTypes: '所有類型'
    },
    types: {
      assign: '分配',
      move: '移動',
      return: '歸位'
    },
    table: {
      itemCode: '商品代碼',
      itemName: '商品名稱',
      boxNumber: '箱號',
      fromLocation: '原位置',
      toLocation: '新位置',
      movementType: '移動類型',
      reason: '移動原因',
      operator: '操作者',
      movedAt: '移動時間'
    },
    pagination: {
      showing: '顯示',
      to: '到',
      of: '共',
      prev: '上一頁',
      next: '下一頁'
    },
    loading: '載入中...',
    noRecords: '沒有移動記錄',
    errors: {
      fetchFailed: '載入移動記錄失敗'
    }
  },
  scanAndPlace: {
    title: '掃描歸位',
    description: '使用掃描功能快速進行商品歸位操作',
    options: {
      firstBinding: '01.商品歸位綁定（首次入庫）',
      processShipping: '02.加工／出貨'
    },
    firstBinding: {
      title: '商品歸位綁定（首次入庫）',
      description: '將新進商品與櫃位進行綁定，並設定為已入庫狀態',
      step: '步驟',
      step1: '1. 掃描櫃位 QR Code',
      step2: '2. 掃描商品箱子 QR Code',
      scanLocation: '掃描櫃位 QR Code',
      scanBox: '掃描商品箱子 QR Code',
      locationCode: '櫃位代碼',
      locationPlaceholder: '掃描櫃位 QR Code',
      boxCode: '箱子 QR Code',
      boxPlaceholder: '掃描箱子 QR Code',
      bindingOptions: '綁定選項',
      bindOnlyOption: '僅綁定不入庫',
      bindAndInboxOption: '綁定並入庫（預設）',
      confirm: '確認綁定',
      reset: '重置',
      status: {
        waitingLocation: '等待掃描櫃位',
        waitingBox: '等待掃描商品箱子',
        ready: '準備綁定'
      },
      messages: {
        locationScanned: '櫃位已掃描：{location}',
        boxScanned: '商品箱子已掃描：{box}',
        bindingSuccess: '綁定成功！',
        bindingError: '綁定失敗，請重試',
        invalidLocation: '無效的櫃位 QR Code',
        invalidBox: '無效的商品箱子 QR Code',
        alreadyBound: '該商品箱子已綁定其他櫃位'
      }
    },
    processShipping: {
      title: '加工／出貨',
      description: '處理已歸位商品的出庫操作',
      scanBox: '掃描商品箱子 QR Code',
      boxCode: '箱子 QR Code',
      boxPlaceholder: '掃描箱子 QR Code',
      outboundType: '出庫類型',
      processing: '加工（預設）',
      shipping: '出貨',
      confirm: '確認出庫',
      reset: '重置',
      status: {
        waitingBox: '等待掃描商品箱子',
        ready: '準備出庫'
      },
      messages: {
        boxScanned: '商品箱子已掃描：{box}',
        processingSuccess: '加工出庫成功！商品已移至 CH七樓加工區',
        shippingSuccess: '出貨成功！商品已標記為出貨狀態',
        outboundError: '出庫失敗，請重試',
        invalidBox: '無效的商品箱子 QR Code',
        notBound: '該商品箱子尚未綁定櫃位',
        notInStock: '該商品箱子不在庫存中'
      }
    },
    returnToStock: {
      title: '加工完成後歸還櫃位',
      description: '將加工完成的商品歸還到指定櫃位',
      step: '步驟',
      step1: '1. 掃描目標櫃位 QR Code',
      step2: '2. 掃描商品箱子 QR Code',
      scanLocation: '掃描目標櫃位 QR Code',
      scanBox: '掃描商品箱子 QR Code',
      locationCode: '櫃位代碼',
      locationPlaceholder: '掃描櫃位 QR Code',
      boxCode: '箱子 QR Code',
      boxPlaceholder: '掃描箱子 QR Code',
      confirm: '確認歸還',
      reset: '重置',
      status: {
        waitingLocation: '等待掃描目標櫃位',
        waitingBox: '等待掃描商品箱子',
        ready: '準備歸還'
      },
      messages: {
        locationScanned: '目標櫃位已掃描：{location}',
        boxScanned: '商品箱子已掃描：{box}',
        returnSuccess: '歸還成功！商品已入庫到指定櫃位',
        returnError: '歸還失敗，請重試',
        invalidLocation: '無效的櫃位 QR Code',
        invalidBox: '無效的商品箱子 QR Code',
        notFromProcessing: '該商品箱子不在加工區'
      }
    },
    common: {
      scan: '掃描',
      cancel: '取消',
      confirm: '確認',
      reset: '重置',
      back: '返回',
      next: '下一步',
      loading: '處理中...',
      scanSuccessful: '掃描成功',
      scanError: '掃描失敗，請重試',
      networkError: '網路錯誤，請檢查連線',
      systemError: '系統錯誤，請聯繫管理員'
    }
  }
}
