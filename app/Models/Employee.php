<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'fname',
        'lname',
        'city_id',
        'branch_id',
    ];
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
    public function city() {
        return $this->belongsTo(City::class);
    }
}
