<?php

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemRepository
{
    protected Item $model;

    public function __construct(Item $model)
    {
        $this->model = $model;
    }

    /**
     * 根據ID查找
     */
    public function find(int $id): ?Item
    {
        return $this->model->find($id);
    }

    /**
     * 根據ID查找或失敗
     */
    public function findOrFail(int $id): Item
    {
        $item = $this->model->find($id);
        
        if (!$item) {
            throw new ModelNotFoundException("Item with ID {$id} not found");
        }
        
        return $item;
    }

    /**
     * 根據條碼查找
     */
    public function findByBarcode(string $barcode): ?Item
    {
        return $this->model->where('item_barcode', $barcode)->first();
    }

    /**
     * 根據序號查找
     */
    public function findBySerialNumber(string $serialNumber): ?Item
    {
        return $this->model->where('item_sn', $serialNumber)->first();
    }

    /**
     * 獲取所有項目
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * 搜尋項目
     */
    public function search(string $searchTerm): Collection
    {
        return $this->model->where(function (Builder $query) use ($searchTerm) {
            $query->where('item_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('item_sn', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('item_spec', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('item_barcode', 'LIKE', "%{$searchTerm}%");
        })->get();
    }

    /**
     * 根據類型獲取項目
     */
    public function getByType(string $type): Collection
    {
        return $this->model->where('item_type', $type)->get();
    }

    /**
     * 獲取低庫存項目
     */
    public function getLowStockItems(): Collection
    {
        return $this->model->whereRaw('item_save > (SELECT COALESCE(SUM(item_count), 0) FROM posinitem WHERE posinitem.item_id = item.item_id)')
            ->get();
    }

    /**
     * 創建新項目
     */
    public function create(array $data): Item
    {
        return $this->model->create($data);
    }

    /**
     * 更新項目
     */
    public function update(int $id, array $data): Item
    {
        $item = $this->findOrFail($id);
        $item->update($data);
        return $item->fresh();
    }

    /**
     * 刪除項目
     */
    public function delete(int $id): bool
    {
        $item = $this->findOrFail($id);
        return $item->delete();
    }

    /**
     * 批量驗證項目ID
     */
    public function validateItemIds(array $itemIds): array
    {
        $existingIds = $this->model->whereIn('item_id', $itemIds)->pluck('item_id')->toArray();
        $missingIds = array_diff($itemIds, $existingIds);
        
        return [
            'existing' => $existingIds,
            'missing' => $missingIds,
            'valid' => empty($missingIds)
        ];
    }

    /**
     * 獲取項目統計
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => $this->model->count(),
            'active_count' => $this->model->where('item_open', true)->count(),
            'low_stock_count' => $this->getLowStockItems()->count(),
            'categories' => $this->model->select('item_type')
                ->groupBy('item_type')
                ->selectRaw('item_type, COUNT(*) as count')
                ->get()
                ->pluck('count', 'item_type')
                ->toArray()
        ];
    }
}