<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scanner extends Model
{
    protected $fillable = [
        'city_id',
        'branch_id',
        'quantity',
        'model',
        'type',
    ];
    protected $casts = [
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
