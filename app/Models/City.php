<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    // هر شهر/حوزه می‌تواند چند شعبه داشته باشد
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
