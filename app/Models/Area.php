<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'name',
        'postal',
        'delivery_fee'
    ];

    protected $casts = [
        'delivery_fee' => 'array',
    ];

    public function status()
    {
        return $this->belongsTo(AttrStatus::class, 'status_id', 'id');
    }
}