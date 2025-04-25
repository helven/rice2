<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'ic_name',
        'ic_no',
        'address', // Added address field
        'route',
        'status',
    ];

    // If 'route' is stored as JSON, cast it to an array
    protected $casts = [
        'route' => 'array',
    ];
}