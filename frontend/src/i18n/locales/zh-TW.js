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
  }
}
