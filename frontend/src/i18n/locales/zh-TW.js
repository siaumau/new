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
  }
}
