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
      supplier: '供應商',
      purchaseDate: '進貨日期',
      recordTime: '記錄時間',
      status: '狀態',
      itemsCount: '商品項目數量',
      totalAmount: '總金額',
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
    form: {
      supplier: '供應商',
      status: '狀態',
      purchaseDate: '進貨日期',
      expectedDeliveryDate: '預計到貨日期',
      notes: '備註',
      items: '商品項目',
      addItem: '添加項目',
      item: '商品',
      quantity: '數量',
      price: '單價',
      subtotal: '小計',
      actions: '操作',
      delete: '刪除',
      totalAmount: '總金額',
      cancel: '取消',
      save: '保存',
      processing: '處理中...',
      selectSupplier: '選擇供應商',
      selectItem: '選擇商品'
    },
    messages: {
      loading: '載入中...',
      error: '無法載入進貨訂單資料。請稍後再試。',
      noData: '沒有找到任何進貨訂單',
      deleteConfirm: '確定要刪除此進貨訂單？',
      deleteError: '刪除進貨訂單時發生錯誤。請稍後再試。',
      saveError: '保存進貨訂單時發生錯誤。請稍後再試。',
      validation: {
        selectSupplier: '請選擇供應商',
        enterPurchaseDate: '請輸入進貨日期',
        addOneItem: '請至少添加一個商品項目',
        selectItemForAll: '請為所有項目選擇商品',
        quantityPositive: '所有項目的數量必須大於零',
        priceNonNegative: '所有項目的價格不能為負數'
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
