<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'city_id',
        'branch_id',
        'model',
        'quantity',
        'ip_address',
        'type',
        'is_color',
        'is_network'
    ];

    protected $casts = [
        'is_color' => 'boolean',
        'is_network' => 'boolean',
        'quantity' => 'integer'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
