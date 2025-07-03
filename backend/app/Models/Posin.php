<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posin extends Model
{
    use HasFactory;

    protected $table = 'posin';
    protected $primaryKey = 'posin_id';
    public $timestamps = false;

    protected $fillable = [
        '_users_id',
        '_users_id2',
        'posin_sn',
        'posin_user',
        'posin_user2',
        'posin_dt',
        'posin_log',
        'posin_note',
    ];

    public function posinItems()
    {
        return $this->hasMany(PosinItem::class, 'posin_id', 'posin_id');
    }
}
