<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlyerDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'location',
        'area',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}