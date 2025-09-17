<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mall extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'name'
    ];

    public function status()
    {
        return $this->belongsTo(MallStatus::class, 'status_id', 'id');
    }
}