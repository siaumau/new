# 庫存管理系統架構設計文檔

## 專案概述

這是一個全棧式庫存管理系統，主要專注於進貨單管理、商品追蹤、位置管理以及QR Code標籤生成等功能。系統採用前後端分離架構，提供完整的庫存管理解決方案。

## 技術棧

### 前端 (Frontend)
- **框架**: Vue 3 (Composition API)
- **路由**: Vue Router 4
- **國際化**: Vue I18n (支援繁體中文/英文)
- **HTTP客戶端**: Axios
- **UI框架**: Tailwind CSS
- **QR Code**: html5-qrcode, qrcode
- **文件處理**: file-saver, jszip
- **構建工具**: Vite

### 後端 (Backend)
- **框架**: Laravel 9
- **PHP版本**: 8.0+
- **API文檔**: L5-Swagger (OpenAPI)
- **認證**: Laravel Sanctum
- **資料庫**: MySQL (透過Eloquent ORM)

## 系統架構

```
┌─────────────────────────────────────────────────────────────┐
│                        前端層 (Frontend)                      │
├─────────────────────────────────────────────────────────────┤
│  Vue 3 應用                                                  │
│  ├── 路由管理 (Vue Router)                                   │
│  ├── 狀態管理 (Composition API)                              │
│  ├── 國際化 (Vue I18n)                                       │
│  └── UI組件 (Tailwind CSS)                                   │
└─────────────────────────────────────────────────────────────┘
                                │
                        HTTP/API 請求
                                │
┌─────────────────────────────────────────────────────────────┐
│                        後端層 (Backend)                       │
├─────────────────────────────────────────────────────────────┤
│  Laravel 9 應用                                              │
│  ├── API路由 (RESTful)                                       │
│  ├── 控制器層 (Controllers)                                  │
│  ├── 模型層 (Eloquent Models)                                │
│  ├── 中介層 (Middleware)                                     │
│  └── 服務層 (Business Logic)                                 │
└─────────────────────────────────────────────────────────────┘
                                │
                        資料庫連接
                                │
┌─────────────────────────────────────────────────────────────┐
│                        資料層 (Database)                      │
├─────────────────────────────────────────────────────────────┤
│  MySQL 資料庫                                                │
│  ├── 商品表 (item)                                           │
│  ├── 進貨單表 (posin/posinitem)                              │
│  ├── 位置管理表 (locations)                                  │
│  ├── QR Code表 (qr_codes)                                    │
│  ├── 移動記錄表 (movement_logs)                              │
│  └── 系統設定表 (system_settings)                            │
└─────────────────────────────────────────────────────────────┘
```

## 功能模組架構

### 1. 商品管理模組 (Items)
```
frontend/src/views/ProductsView.vue
frontend/src/components/ItemForm.vue
frontend/src/components/ItemTable.vue
    ↓
backend/app/Http/Controllers/Api/V1/ItemController.php
backend/app/Models/Item.php
    ↓
item 表
```

### 2. 進貨單管理模組 (Purchase Orders)
```
frontend/src/views/PurchaseOrdersView.vue
frontend/src/views/PosinItemsView.vue
frontend/src/components/PosinForm.vue
frontend/src/components/PosinTable.vue
frontend/src/components/PosinItemsTable.vue
    ↓
backend/app/Http/Controllers/PosinController.php
backend/app/Models/Posin.php
backend/app/Models/PosinItem.php
    ↓
posin, posinitem 表
```

### 3. 位置管理模組 (Locations)
```
frontend/src/views/LocationsView.vue
frontend/src/components/LocationForm.vue
frontend/src/components/LocationTable.vue
    ↓
backend/app/Http/Controllers/LocationController.php
backend/app/Models/Location.php
    ↓
locations, location_code_settings, location_product_bindings 表
```

### 4. QR Code管理模組
```
frontend/src/views/QrCodeView.vue
frontend/src/components/QrCodeScanner.vue
frontend/src/components/PrintTemplateModal.vue
    ↓
backend/app/Http/Controllers/Api/V1/QrCodeController.php
backend/app/Models/QrCode.php
    ↓
qr_codes 表
```

### 5. 掃描歸位模組 (Scan & Place)
```
frontend/src/views/ScanPlaceView.vue
frontend/src/components/FirstBindingComponent.vue
frontend/src/components/ProcessShippingComponent.vue
frontend/src/components/ReturnToStockComponent.vue
    ↓
backend/app/Http/Controllers/ScanPlaceController.php
    ↓
移動記錄相關表
```

## 資料庫設計

### 核心實體關係
```
item (商品)
├── 1:N → posinitem (進貨單商品項目)
├── 1:N → qr_codes (QR碼)
└── N:N → locations (透過 location_product_bindings)

posin (進貨單)
├── 1:N → posinitem (進貨單商品項目)
└── 1:N → qr_codes (透過 posinitem)

locations (位置)
├── 1:N → location_product_bindings (位置商品綁定)
├── 1:N → qr_codes (QR碼位置)
└── N:1 → location_code_settings (位置代碼設定)

qr_codes (QR碼)
├── N:1 → item (商品)
├── N:1 → posinitem (進貨單商品項目)
└── N:1 → locations (位置)
```

### 主要資料表結構

#### item (商品表)
- `item_id`: 主鍵
- `item_name`: 商品名稱
- `item_sn`: 型號
- `item_spec`: 規格
- `item_price`: 成本
- `item_inbox`: 每箱數量
- `item_barcode`: 產品條碼

#### posin & posinitem (進貨單表)
- `posin_id`: 進貨單主鍵
- `posinitem_id`: 進貨單商品項目主鍵
- `item_batch`: 批號
- `item_expireday`: 到期日
- `item_count`: 數量

#### locations (位置表)
- `id`: 位置主鍵
- `location_code`: 位置代碼
- `building_code`: 建築代碼
- `storage_type_code`: 存放類別
- `position_code`: 存放位置

#### qr_codes (QR碼表)
- `qr_id`: QR碼主鍵
- `qr_content`: QR碼內容
- `box_number`: 箱號
- `location_id`: 位置ID
- `status`: 狀態 (generated/printed/used)

## API設計

### RESTful API 端點

#### 商品管理
```
GET    /api/v1/items              # 獲取商品列表
POST   /api/v1/items              # 創建商品
GET    /api/v1/items/{id}         # 獲取單一商品
PUT    /api/v1/items/{id}         # 更新商品
DELETE /api/v1/items/{id}         # 刪除商品
```

#### 進貨單管理
```
GET    /api/v1/posin              # 獲取進貨單列表
POST   /api/v1/posin              # 創建進貨單
GET    /api/v1/posin/{id}/items   # 獲取進貨單商品項目
POST   /api/v1/posin/batch        # 批量創建進貨單
PATCH  /api/v1/posin/{id}/generate-us-purchase-order # 生成美國進貨單
```

#### QR Code管理
```
GET    /api/v1/qr-codes           # 獲取QR碼列表
POST   /api/v1/generate-qr-labels # 生成QR碼標籤
POST   /api/v1/qr-codes/{id}/assign-location # 分配位置
GET    /api/v1/qr-codes/statistics # QR碼統計
```

#### 位置管理
```
GET    /api/v1/locations          # 獲取位置列表
POST   /api/v1/locations          # 創建位置
GET    /api/v1/locations/{id}/items # 獲取位置商品
POST   /api/v1/locations/batch    # 批量創建位置
```

#### 掃描歸位
```
POST   /api/v1/scan-place/validate-location # 驗證位置
POST   /api/v1/scan-place/first-binding     # 首次綁定
POST   /api/v1/scan-place/process-shipping  # 處理出貨
POST   /api/v1/scan-place/return-to-stock   # 退回庫存
```

## 前端架構詳細設計

### 組件層次結構
```
App.vue
├── SideMenu.vue (側邊選單)
├── Router View
    ├── ProductsView.vue (商品管理頁面)
    │   ├── ItemForm.vue
    │   └── ItemTable.vue
    ├── LocationsView.vue (位置管理頁面)
    │   ├── LocationForm.vue
    │   └── LocationTable.vue
    ├── PurchaseOrdersView.vue (進貨單管理)
    │   ├── PosinForm.vue
    │   └── PosinTable.vue
    ├── PosinItemsView.vue (進貨單商品項目)
    │   └── PosinItemsTable.vue
    ├── QrCodeView.vue (QR碼管理)
    │   ├── QrCodeScanner.vue
    │   └── PrintTemplateModal.vue
    ├── ScanPlaceView.vue (掃描歸位)
    │   ├── FirstBindingComponent.vue
    │   ├── ProcessShippingComponent.vue
    │   └── ReturnToStockComponent.vue
    └── MovementHistoryView.vue (移動歷史)
```

### 路由設計
```javascript
{
  path: '/',                     // 首頁
  path: '/products',             // 商品管理
  path: '/locations',            // 位置管理
  path: '/purchase-orders',      // 進貨單管理
  path: '/posin/:id/items',      // 進貨單商品項目
  path: '/qr-codes',             // QR碼管理
  path: '/scan-place',           // 掃描歸位
  path: '/movement-history'      // 移動歷史
}
```

### 國際化設計
```
frontend/src/i18n/
├── index.js (i18n配置)
└── locales/
    ├── zh-TW.js (繁體中文)
    └── en.js (英文)
```

## 後端架構詳細設計

### 控制器層次結構
```
app/Http/Controllers/
├── Controller.php (基礎控制器)
├── ItemController.php (商品管理)
├── LocationController.php (位置管理)
├── PosinController.php (進貨單管理)
├── QrCodeController.php (QR碼管理)
├── ScanPlaceController.php (掃描歸位)
├── MovementLogController.php (移動記錄)
└── Api/V1/
    ├── ItemController.php (API版本控制)
    └── QrCodeController.php
```

### 模型關係設計
```php
// Item Model
class Item extends Model {
    public function qrCodes() {
        return $this->hasMany(QrCode::class);
    }
    public function posinItems() {
        return $this->hasMany(PosinItem::class);
    }
}

// Posin Model
class Posin extends Model {
    public function items() {
        return $this->hasMany(PosinItem::class);
    }
}

// Location Model
class Location extends Model {
    public function qrCodes() {
        return $this->hasMany(QrCode::class);
    }
    public function bindings() {
        return $this->hasMany(LocationProductBinding::class);
    }
}
```

## 核心功能流程

### 1. QR Code生成流程
```
1. 用戶在進貨單商品項目頁面點擊「生成QR」
2. 前端調用 POST /api/v1/generate-qr-labels
3. 後端根據商品信息生成QR碼內容
4. QR碼格式: SKU + 批號 + 效期(YYYYMM) + 流水號(001-999)
5. 生成PDF標籤文件並返回下載連結
6. 記錄QR碼到 qr_codes 表
```

### 2. 掃描歸位流程
```
1. 用戶掃描位置QR碼驗證位置
2. 掃描商品QR碼進行綁定
3. 系統驗證位置和商品的合法性
4. 更新位置商品綁定關係
5. 記錄移動日誌到 movement_logs
```

### 3. 庫存追蹤流程
```
1. 商品入庫時創建進貨單記錄
2. 生成對應的QR碼標籤
3. 透過掃描歸位確定商品位置
4. 系統實時更新庫存狀態
5. 提供移動歷史查詢功能
```

## 安全性設計

### 認證授權
- 使用 Laravel Sanctum 進行API認證
- 支援Token-based身份驗證
- 實作角色權限控制系統

### 資料驗證
- 前端表單驗證
- 後端Request驗證
- 資料庫約束確保數據完整性

### 操作記錄
- 完整的操作日誌記錄 (operation_logs)
- 用戶行為追蹤
- 系統異常監控

## 部署架構

### 開發環境
```
frontend/ (Vue 3 + Vite)
├── npm run dev (localhost:3000)

backend/ (Laravel 9)
├── php artisan serve (localhost:8000)
├── MySQL Database
```

### 生產環境建議
```
Web Server (Nginx/Apache)
├── Frontend (靜態文件部署)
├── Backend (PHP-FPM)
└── Database (MySQL/MariaDB)
```

## 擴展性考量

### 水平擴展
- API設計遵循RESTful原則
- 前後端分離架構支援獨立部署
- 資料庫支援讀寫分離

### 功能擴展
- 模組化組件設計
- 插件式架構
- 國際化支援多語言擴展

### 性能優化
- 資料庫索引優化
- API響應緩存
- 前端代碼分割

## 維護與監控

### 日誌管理
- Laravel日誌系統
- 操作記錄追蹤
- 錯誤監控告警

### 備份策略
- 資料庫定期備份
- 文件系統備份
- 系統配置版本控制

---

*此架構文檔描述了庫存管理系統的完整技術架構，包括前端Vue.js應用、後端Laravel API、資料庫設計以及核心業務流程。系統採用現代化的全棧開發技術，提供穩定、可擴展的庫存管理解決方案。*