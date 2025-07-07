<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementLog extends Model
{
    use HasFactory;

    protected $table = 'movement_logs';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'qr_code_id',
        'item_code',
        'item_name',
        'box_number',
        'from_location_id',
        'from_location_code',
        'to_location_id',
        'to_location_code',
        'movement_type',
        'reason',
        'operator',
        'moved_at',
        'notes'
    ];

    protected $casts = [
        'moved_at' => 'datetime',
        'qr_code_id' => 'integer',
        'from_location_id' => 'integer',
        'to_location_id' => 'integer'
    ];

    // 關聯到QR Code
    public function qrCode()
    {
        return $this->belongsTo(QrCode::class, 'qr_code_id', 'qr_id');
    }

    // 關聯到原位置
    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id', 'id');
    }

    // 關聯到新位置
    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id', 'id');
    }
}
