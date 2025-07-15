<?php

namespace App\Services;

use App\Models\Posin;
use App\Models\PosinItem;
use App\Models\Item;
use App\Repositories\PosinRepository;
use App\Repositories\ItemRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\BusinessLogicException;

class PosinService
{
    protected PosinRepository $posinRepository;
    protected ItemRepository $itemRepository;

    public function __construct(
        PosinRepository $posinRepository,
        ItemRepository $itemRepository
    ) {
        $this->posinRepository = $posinRepository;
        $this->itemRepository = $itemRepository;
    }

    /**
     * 獲取進貨單列表（含搜尋和分頁）
     */
    public function getPosinList(array $filters = [], int $perPage = 10): array
    {
        try {
            $query = $this->posinRepository->getQueryBuilder();

            // 應用搜尋篩選
            if (!empty($filters['search'])) {
                $query = $this->posinRepository->applySearch($query, $filters['search']);
            }

            // 應用狀態篩選
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $query = $this->posinRepository->applyStatusFilter($query, $filters['status']);
            }

            // 分頁並格式化資料
            $posinRecords = $query->orderBy('posin_dt', 'desc')->paginate($perPage);
            
            return $this->formatPosinListResponse($posinRecords);

        } catch (\Exception $e) {
            Log::error('Error in PosinService::getPosinList', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('獲取進貨單列表失敗: ' . $e->getMessage());
        }
    }

    /**
     * 創建新進貨單
     */
    public function createPosin(array $posinData): Posin
    {
        DB::beginTransaction();
        try {
            // 驗證商品項目
            $this->validatePosinItems($posinData['posin_items']);

            // 創建進貨單
            $posin = $this->posinRepository->create([
                '_users_id' => $posinData['_users_id'],
                'posin_sn' => $posinData['posin_sn'],
                'posin_user' => $posinData['posin_user'],
                'posin_dt' => $posinData['posin_dt'],
                'posin_note' => $posinData['posin_note'],
            ]);

            // 創建進貨單項目
            foreach ($posinData['posin_items'] as $itemData) {
                $this->createPosinItem($posin->posin_id, $itemData);
            }

            DB::commit();
            return $posin->load('posinItems');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating posin', [
                'data' => $posinData,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('創建進貨單失敗: ' . $e->getMessage());
        }
    }

    /**
     * 更新進貨單
     */
    public function updatePosin(int $posinId, array $posinData): Posin
    {
        DB::beginTransaction();
        try {
            $posin = $this->posinRepository->findOrFail($posinId);

            // 驗證商品項目
            $this->validatePosinItems($posinData['posin_items']);

            // 更新進貨單基本資訊
            $posin = $this->posinRepository->update($posinId, [
                '_users_id' => $posinData['_users_id'],
                'posin_sn' => $posinData['posin_sn'],
                'posin_user' => $posinData['posin_user'],
                'posin_dt' => $posinData['posin_dt'],
                'posin_note' => $posinData['posin_note'],
            ]);

            // 刪除舊的項目並重新創建
            $this->posinRepository->deleteItems($posinId);
            foreach ($posinData['posin_items'] as $itemData) {
                $this->createPosinItem($posinId, $itemData);
            }

            DB::commit();
            return $posin->load('posinItems');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating posin', [
                'posin_id' => $posinId,
                'data' => $posinData,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('更新進貨單失敗: ' . $e->getMessage());
        }
    }

    /**
     * 刪除進貨單
     */
    public function deletePosin(int $posinId): bool
    {
        DB::beginTransaction();
        try {
            $posin = $this->posinRepository->findOrFail($posinId);
            
            // 檢查是否可以刪除（例如：已生成QR碼的不能刪除）
            if ($this->hasGeneratedQrCodes($posinId)) {
                throw new BusinessLogicException('該進貨單已生成QR碼，無法刪除');
            }

            $this->posinRepository->deleteItems($posinId);
            $this->posinRepository->delete($posinId);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting posin', [
                'posin_id' => $posinId,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('刪除進貨單失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取單一進貨單
     */
    public function getPosin(int $posinId): Posin
    {
        try {
            return $this->posinRepository->findOrFail($posinId);
        } catch (\Exception $e) {
            Log::error('Error getting posin', [
                'posin_id' => $posinId,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('獲取進貨單失敗: ' . $e->getMessage());
        }
    }

    /**
     * 獲取進貨單項目
     */
    public function getPosinItems(int $posinId): array
    {
        try {
            $posin = $this->posinRepository->findOrFail($posinId);
            $posinItems = $this->posinRepository->getItemsWithRelations($posinId);

            return $posinItems->map(function ($item) use ($posin) {
                return $this->formatPosinItem($item, $posin);
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Error getting posin items', [
                'posin_id' => $posinId,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('獲取進貨單項目失敗: ' . $e->getMessage());
        }
    }

    /**
     * 轉換為美國進貨單
     */
    public function convertToUsPurchaseOrder(int $posinId): array
    {
        try {
            $posin = $this->posinRepository->findOrFail($posinId);

            // 檢查是否已經轉換過
            if ($posin->us_purchase_order_status !== 'pending') {
                throw new BusinessLogicException('該進貨單已經轉換為美國進貨單');
            }

            // 更新狀態
            $this->posinRepository->update($posinId, [
                'us_purchase_order_status' => 'generated'
            ]);

            Log::info('US purchase order generated', ['posin_id' => $posinId]);

            return [
                'message' => '美國進貨單轉換成功',
                'status' => 'generated'
            ];

        } catch (\Exception $e) {
            Log::error('Error converting to US purchase order', [
                'posin_id' => $posinId,
                'error' => $e->getMessage()
            ]);
            throw new BusinessLogicException('轉換美國進貨單失敗: ' . $e->getMessage());
        }
    }

    /**
     * 批量匯入進貨單
     */
    public function batchImportPosin(array $purchaseOrders): array
    {
        DB::beginTransaction();
        try {
            $result = [
                'created_count' => 0,
                'error_count' => 0,
                'errors' => []
            ];

            // 按進貨單號分組
            $groupedOrders = $this->groupOrdersByNumber($purchaseOrders);

            foreach ($groupedOrders as $orderNumber => $orderGroup) {
                try {
                    $this->processBatchOrder($orderGroup, $result);
                } catch (\Exception $e) {
                    $result['error_count']++;
                    $result['errors'][] = [
                        'order_number' => $orderNumber,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch import failed', [
                'error' => $e->getMessage(),
                'data_count' => count($purchaseOrders)
            ]);
            throw new BusinessLogicException('批量匯入失敗: ' . $e->getMessage());
        }
    }

    // ============ Private Methods ============

    /**
     * 驗證進貨單項目
     */
    private function validatePosinItems(array $items): void
    {
        foreach ($items as $itemData) {
            $item = $this->itemRepository->find($itemData['item_id']);
            if (!$item) {
                throw new BusinessLogicException("商品ID {$itemData['item_id']} 不存在");
            }
        }
    }

    /**
     * 創建進貨單項目
     */
    private function createPosinItem(int $posinId, array $itemData): PosinItem
    {
        $item = $this->itemRepository->findOrFail($itemData['item_id']);

        return PosinItem::create([
            'posin_id' => $posinId,
            'itemtype' => $itemData['itemtype'],
            'item_id' => $itemData['item_id'],
            'item_name' => $item->item_name,
            'item_sn' => $item->item_sn,
            'item_spec' => $item->item_spec,
            'item_batch' => $itemData['item_batch'],
            'item_count' => $itemData['item_count'],
            'item_price' => $itemData['item_price'],
            'item_expireday' => $itemData['item_expireday'] ?? null,
            'item_validyear' => $itemData['item_validyear'] ?? null,
        ]);
    }

    /**
     * 檢查是否已生成QR碼
     */
    private function hasGeneratedQrCodes(int $posinId): bool
    {
        return DB::table('qr_codes')->where('posin_id', $posinId)->exists();
    }

    /**
     * 格式化進貨單列表響應
     */
    private function formatPosinListResponse($posinRecords): array
    {
        $formattedItems = [];
        foreach ($posinRecords->items() as $posin) {
            $formattedItems[] = $this->formatPosinListItem($posin);
        }

        return [
            'data' => $formattedItems,
            'current_page' => $posinRecords->currentPage(),
            'last_page' => $posinRecords->lastPage(),
            'per_page' => $posinRecords->perPage(),
            'total' => $posinRecords->total(),
            'from' => $posinRecords->firstItem(),
            'to' => $posinRecords->lastItem(),
        ];
    }

    /**
     * 格式化單個進貨單項目（列表用）
     */
    private function formatPosinListItem($posin): array
    {
        $purchaseDate = '';
        $createdAt = '';

        if ($posin->posin_dt) {
            try {
                $purchaseDate = date('Y/n/j', strtotime($posin->posin_dt));
                $createdAt = date('Y/n/j', strtotime($posin->posin_dt));
            } catch (\Exception $e) {
                $purchaseDate = $posin->posin_dt;
                $createdAt = $posin->posin_dt;
            }
        }

        return [
            'id' => $posin->posin_id,
            'order_number' => $posin->posin_sn ?? '',
            'supplier' => $posin->posin_user ?? '',
            'purchase_date' => $purchaseDate,
            'created_at' => $createdAt,
            'status' => $posin->posin_log ? '已完成' : '進行中',
            'items_count' => $posin->posinItems ? $posin->posinItems->count() : 0,
            'notes' => $posin->posin_note ?? '',
            'us_purchase_order_status' => $posin->us_purchase_order_status ?? 'pending',
            'posin_items' => $posin->posinItems ?? []
        ];
    }

    /**
     * 格式化進貨單項目（詳細用）
     */
    private function formatPosinItem($item, $posin): array
    {
        return [
            'posinitem_id' => $item->posinitem_id,
            'posin_id' => $item->posin_id,
            'item_id' => $item->item_id,
            'item_name' => $item->item_name,
            'item_sn' => $item->item_sn,
            'item_spec' => $item->item_spec,
            'item_batch' => $item->item_batch,
            'item_count' => $item->item_count,
            'item_price' => $item->item_price,
            'item_expireday' => $item->item_expireday,
            'item_validyear' => $item->item_validyear,
            'itemtype' => $item->itemtype,
            'posin' => [
                'posin_id' => $posin->posin_id,
                'posin_sn' => $posin->posin_sn,
                'posin_user' => $posin->posin_user,
                'posin_dt' => $posin->posin_dt,
                'posin_note' => $posin->posin_note,
            ]
        ];
    }

    /**
     * 按訂單號分組
     */
    private function groupOrdersByNumber(array $purchaseOrders): array
    {
        $groupedOrders = [];
        foreach ($purchaseOrders as $orderData) {
            $orderNumber = $orderData['order_number'];
            if (!isset($groupedOrders[$orderNumber])) {
                $groupedOrders[$orderNumber] = [
                    'order_info' => [
                        'order_number' => $orderData['order_number'],
                        'user_name' => $orderData['user_name'],
                        'order_date' => $orderData['order_date'],
                        'expected_date' => $orderData['expected_date'] ?? null,
                        'notes' => $orderData['notes'] ?? null,
                    ],
                    'items' => []
                ];
            }

            $groupedOrders[$orderNumber]['items'][] = [
                'item_id' => $orderData['item_id'],
                'item_batch' => $orderData['item_batch'],
                'item_count' => $orderData['item_count'],
                'item_price' => $orderData['item_price'],
                'item_expireday' => $orderData['item_expireday'] ?? null,
                'item_validyear' => $orderData['item_validyear'] ?? null,
                'itemtype' => $orderData['itemtype']
            ];
        }
        return $groupedOrders;
    }

    /**
     * 處理批量訂單
     */
    private function processBatchOrder(array $orderGroup, array &$result): void
    {
        $orderInfo = $orderGroup['order_info'];
        
        // 驗證所有商品是否存在
        foreach ($orderGroup['items'] as $itemData) {
            $item = $this->itemRepository->find($itemData['item_id']);
            if (!$item) {
                throw new \Exception("商品ID {$itemData['item_id']} 不存在");
            }
        }

        // 檢查進貨單是否已存在
        $existingPosin = $this->posinRepository->findByOrderNumber($orderInfo['order_number']);

        if ($existingPosin) {
            if (in_array($existingPosin->us_purchase_order_status, ['generated', 'reviewed', 'completed'])) {
                throw new \Exception('進貨單已提交為美國進貨單，無法重複匯入');
            }
            
            // 更新現有進貨單
            $posin = $this->posinRepository->update($existingPosin->posin_id, [
                'posin_user' => $orderInfo['user_name'],
                'posin_dt' => $orderInfo['order_date'],
                'posin_note' => $orderInfo['notes'],
            ]);
        } else {
            // 創建新進貨單
            $posin = $this->posinRepository->create([
                '_users_id' => 1,
                'posin_sn' => $orderInfo['order_number'],
                'posin_user' => $orderInfo['user_name'],
                'posin_dt' => $orderInfo['order_date'],
                'posin_note' => $orderInfo['notes'],
                'us_purchase_order_status' => 'pending'
            ]);
            $result['created_count']++;
        }

        // 處理項目
        $addedItemsCount = 0;
        foreach ($orderGroup['items'] as $itemData) {
            if (!$this->posinRepository->hasExistingItem($posin->posin_id, $itemData)) {
                $this->createPosinItem($posin->posin_id, $itemData);
                $addedItemsCount++;
            }
        }

        if ($existingPosin && $addedItemsCount > 0) {
            $result['created_count']++;
        }
    }
}