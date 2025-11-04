<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Computer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
        'branch_id',
        'employee_id',
        'monitor',
        'mb',
        'cpu',
        'ram',
        'hard',
        'os',
        'antivirus'
    ];

    protected $casts = [
        'hard' => 'boolean',
        'antivirus' => 'boolean',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
