<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';
    protected $primaryKey = 'qr_id';
    public $timestamps = false;

    protected $fillable = [
        'posin_id',
        'posinitem_id',
        'item_code',
        'item_name',
        'item_batch',
        'expiry_date',
        'box_number',
        'location_id',
        'floor_level',
        'qr_content',
        'file_name',
        'zip_file_name',
        'generated_at',
        'generated_by',
        'status',
        'notes',
        'item_inbox_status',
        'item_inbox'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'generated_at' => 'datetime',
        'box_number' => 'integer',
        'location_id' => 'integer',
        'posin_id' => 'integer',
        'posinitem_id' => 'integer',
        'item_inbox_status' => 'integer',
    ];

    // 關聯到進貨單
    public function posin()
    {
        return $this->belongsTo(Posin::class, 'posin_id', 'posin_id');
    }

    // 關聯到進貨單項目
    public function posinItem()
    {
        return $this->belongsTo(PosinItem::class, 'posinitem_id', 'posinitem_id');
    }

    // 關聯到位置
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    // 關聯到商品
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_barcode');
    }
}
