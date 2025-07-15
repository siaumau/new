<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PosinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->posin_id,
            'order_number' => $this->posin_sn,
            'supplier' => $this->posin_user,
            'purchase_date' => $this->formatDate($this->posin_dt),
            'created_at' => $this->formatDate($this->posin_dt),
            'status' => $this->posin_log ? '已完成' : '進行中',
            'items_count' => $this->whenLoaded('posinItems', function () {
                return $this->posinItems->count();
            }, 0),
            'notes' => $this->posin_note,
            'us_purchase_order_status' => $this->us_purchase_order_status ?? 'pending',
            'items' => PosinItemResource::collection($this->whenLoaded('posinItems')),
            'metadata' => [
                'has_qr_codes' => $this->when($this->relationLoaded('qrCodes'), function () {
                    return $this->qrCodes->isNotEmpty();
                }),
                'can_edit' => $this->us_purchase_order_status === 'pending',
                'can_delete' => $this->us_purchase_order_status === 'pending' && !$this->hasQrCodes(),
                'can_convert_to_us' => $this->us_purchase_order_status === 'pending',
            ]
        ];
    }

    /**
     * Format date for display
     */
    private function formatDate($date): string
    {
        if (!$date) {
            return '';
        }

        try {
            return date('Y/n/j', strtotime($date));
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Check if has QR codes
     */
    private function hasQrCodes(): bool
    {
        if ($this->relationLoaded('qrCodes')) {
            return $this->qrCodes->isNotEmpty();
        }
        
        // 如果沒有預載入關聯，進行查詢
        return \Illuminate\Support\Facades\DB::table('qr_codes')
            ->where('posin_id', $this->posin_id)
            ->exists();
    }

    /**
     * Get additional response data
     */
    public function with($request): array
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'api_version' => '1.0',
            ]
        ];
    }
}