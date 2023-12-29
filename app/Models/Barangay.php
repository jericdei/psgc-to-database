<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'code';

    protected $fillable = [
        'code',
        'old_code',
        'region_code',
        'province_code',
        'municipality_code',
        'sub_municipality_code',
        'barangay_code',
        'name',
        'old_name',
    ];
}
