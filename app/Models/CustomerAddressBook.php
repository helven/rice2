<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddressBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'is_default',
        'customer_id',
        'name',
        'contact',
        'email',
        'address_1',
        'address_2',
        'postal_code',
        'city',
        'state',
        'country'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function attr_status()
    {
        return $this->belongsTo(AttrStatus::class, 'status', 'id');
    }
}