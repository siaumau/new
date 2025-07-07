<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosinItem extends Model
{
    use HasFactory;

    protected $table = 'posinitem';
    protected $primaryKey = 'posinitem_id';
    public $timestamps = false;

    protected $fillable = [
        'posin_id',
        'itemtype',
        'item_id',
        'item_name',
        'item_sn',
        'item_spec',
        'item_batch',
        'item_count',
        'item_price',
        'item_expireday',
        'item_validyear',
    ];

    protected $casts = [
        'item_expireday' => 'date',
        'item_count' => 'integer',
        'item_price' => 'decimal:2',
        'item_id' => 'integer',
        'posin_id' => 'integer',
        'itemtype' => 'integer',
    ];


    public function posin()
    {
        return $this->belongsTo(Posin::class, 'posin_id', 'posin_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
