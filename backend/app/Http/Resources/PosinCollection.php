<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PosinCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'pagination' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'has_more_pages' => $this->hasMorePages(),
                'on_first_page' => $this->onFirstPage(),
                'path' => $this->path(),
            ],
            'meta' => [
                'timestamp' => now()->toISOString(),
                'api_version' => '1.0',
                'filters_applied' => $this->getFiltersApplied($request),
            ]
        ];
    }

    /**
     * Get filters that were applied to the request
     */
    private function getFiltersApplied($request): array
    {
        $filters = [];
        
        if ($request->has('search') && !empty($request->search)) {
            $filters['search'] = $request->search;
        }
        
        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $filters['status'] = $request->status;
        }
        
        if ($request->has('per_page')) {
            $filters['per_page'] = $request->per_page;
        }
        
        return $filters;
    }

    /**
     * Get additional response data
     */
    public function with($request): array
    {
        return [
            'success' => true,
            'message' => '進貨單列表獲取成功',
        ];
    }
}