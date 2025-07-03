<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'location_code',
        'location_name',
        'building_code',
        'floor_number',
        'floor_area_code',
        'storage_type_code',
        'sub_area_code',
        'position_code',
        'capacity',
        'current_stock',
        'qr_code_data',
        'notes',
        'is_active',
    ];
}
