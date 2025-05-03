<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'status'
    ];

    /**
     * Get the customer's address books
     */
    public function addressBooks()
    {
        return $this->hasMany(CustomerAddressBook::class);
    }

    /**
     * Get the customer's default address
     */
    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddressBook::class)->where('is_default', true);
    }

    public function status()
    {
        return $this->belongsTo(AttrStatus::class, 'status_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(AttrState::class, 'state_id', 'id');
    }
}