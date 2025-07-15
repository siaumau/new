<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PosinItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'posinitem_id' => $this->posinitem_id,
            'posin_id' => $this->posin_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'item_sn' => $this->item_sn,
            'item_spec' => $this->item_spec,
            'item_batch' => $this->item_batch,
            'item_count' => $this->item_count,
            'item_price' => number_format($this->item_price, 2),
            'item_price_raw' => $this->item_price,
            'item_expireday' => $this->item_expireday,
            'item_validyear' => $this->item_validyear,
            'itemtype' => $this->itemtype,
            'subtotal' => $this->calculateSubtotal(),
            'formatted_subtotal' => number_format($this->calculateSubtotal(), 2),
            'qr_codes_generated' => $this->hasQrCodes(),
            'qr_codes_count' => $this->getQrCodesCount(),
            'item_details' => $this->when($this->relationLoaded('item'), function () {
                return [
                    'item_inbox' => $this->item->item_inbox ?? null,
                    'item_barcode' => $this->item->item_barcode ?? null,
                    'suggested_retail_price' => $this->item->suggested_retail_price ?? null,
                ];
            }),
            'posin_details' => $this->when($this->relationLoaded('posin'), function () {
                return [
                    'posin_sn' => $this->posin->posin_sn,
                    'posin_user' => $this->posin->posin_user,
                    'posin_dt' => $this->posin->posin_dt,
                    'posin_note' => $this->posin->posin_note,
                ];
            }),
        ];
    }

    /**
     * Calculate subtotal
     */
    private function calculateSubtotal(): float
    {
        return $this->item_count * $this->item_price;
    }

    /**
     * Check if has QR codes
     */
    private function hasQrCodes(): bool
    {
        if ($this->relationLoaded('qrCodes')) {
            return $this->qrCodes->isNotEmpty();
        }
        
        return \Illuminate\Support\Facades\DB::table('qr_codes')
            ->where('posinitem_id', $this->posinitem_id)
            ->exists();
    }

    /**
     * Get QR codes count
     */
    private function getQrCodesCount(): int
    {
        if ($this->relationLoaded('qrCodes')) {
            return $this->qrCodes->count();
        }
        
        return \Illuminate\Support\Facades\DB::table('qr_codes')
            ->where('posinitem_id', $this->posinitem_id)
            ->count();
    }
}