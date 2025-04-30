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
        'address',
        'route',
        'status_id',
    ];

    protected $casts = [
        'route' => 'array',
    ];

    public function status()
    {
        return $this->belongsTo(AttrStatus::class, 'status_id', 'id');
    }
}