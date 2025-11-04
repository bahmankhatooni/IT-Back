<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city_id',  // حتماً city_id اضافه شود
    ];

    // هر شعبه متعلق به یک شهر/حوزه است
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function employees() {
        return $this->hasMany(Employee::class);
    }
}
