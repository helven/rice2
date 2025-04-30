<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'route' => 'array',
    ];

    public function attr_status()
    {
        return $this->belongsTo(AttrStatus::class, 'status', 'id');
    }
}