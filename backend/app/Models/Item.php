<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'item_cid',
        'item_sn',
        'item_spec',
        'item_eng',
        'item_save',
        'item_save2',
        'item_price',
        'suggested_retail_price',
        'item_note',
        'item_open',
        'item_sort',
        'item_mstock',
        'item_type',
        'item_years',
        'item_holdmonth',
        'item_outvyear',
        'item_predict',
        'item_insertdate',
        'item_editdate',
        'item_barcode',
        'item_inbox',
        'ppt_id',
        'item_vcode',
        'item_size',
    ];
}
