<?php

namespace App\Repositories;

use App\Models\Posin;
use App\Models\PosinItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PosinRepository
{
    protected Posin $model;

    public function __construct(Posin $model)
    {
        $this->model = $model;
    }

    /**
     * 獲取查詢建構器
     */
    public function getQueryBuilder(): Builder
    {
        return $this->model->with('posinItems');
    }

    /**
     * 應用搜尋條件
     */
    public function applySearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('posin_sn', 'LIKE', "%{$searchTerm}%")
              ->orWhere('posin_user', 'LIKE', "%{$searchTerm}%")
              ->orWhere('posin_note', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * 應用狀態篩選
     */
    public function applyStatusFilter(Builder $query, string $status): Builder
    {
        if ($status === '已完成') {
            return $query->whereNotNull('posin_log');
        } elseif ($status === '進行中') {
            return $query->whereNull('posin_log');
        }
        
        return $query;
    }

    /**
     * 根據ID查找
     */
    public function find(int $id): ?Posin
    {
        return $this->model->find($id);
    }

    /**
     * 根據ID查找或失敗
     */
    public function findOrFail(int $id): Posin
    {
        $posin = $this->model->find($id);
        
        if (!$posin) {
            throw new ModelNotFoundException("Posin record with ID {$id} not found");
        }
        
        return $posin;
    }

    /**
     * 根據訂單號查找
     */
    public function findByOrderNumber(string $orderNumber): ?Posin
    {
        return $this->model->where('posin_sn', $orderNumber)->first();
    }

    /**
     * 創建新記錄
     */
    public function create(array $data): Posin
    {
        return $this->model->create($data);
    }

    /**
     * 更新記錄
     */
    public function update(int $id, array $data): Posin
    {
        $posin = $this->findOrFail($id);
        $posin->update($data);
        return $posin->fresh();
    }

    /**
     * 刪除記錄
     */
    public function delete(int $id): bool
    {
        $posin = $this->findOrFail($id);
        return $posin->delete();
    }

    /**
     * 獲取帶關聯的項目
     */
    public function getItemsWithRelations(int $posinId): Collection
    {
        $posin = $this->findOrFail($posinId);
        return $posin->posinItems()->with('item')->get();
    }

    /**
     * 刪除所有項目
     */
    public function deleteItems(int $posinId): void
    {
        $posin = $this->findOrFail($posinId);
        $posin->posinItems()->delete();
    }

    /**
     * 檢查是否存在相同的項目
     */
    public function hasExistingItem(int $posinId, array $itemData): bool
    {
        $query = PosinItem::where('posin_id', $posinId)
            ->where('item_id', $itemData['item_id'])
            ->where('item_batch', $itemData['item_batch']);
            
        if (isset($itemData['item_expireday']) && $itemData['item_expireday'] !== null) {
            $query->whereDate('item_expireday', $itemData['item_expireday']);
        } else {
            $query->whereNull('item_expireday');
        }
        
        return $query->exists();
    }

    /**
     * 獲取統計數據
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => $this->model->count(),
            'pending_count' => $this->model->whereNull('posin_log')->count(),
            'completed_count' => $this->model->whereNotNull('posin_log')->count(),
            'us_generated_count' => $this->model->where('us_purchase_order_status', 'generated')->count(),
        ];
    }

    /**
     * 獲取最近的記錄
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->with('posinItems')
            ->orderBy('posin_dt', 'desc')
            ->limit($limit)
            ->get();
    }
}