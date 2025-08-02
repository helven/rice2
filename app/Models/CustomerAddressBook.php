<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerAddressBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_id',
        'is_default',
        'customer_id',
        'name',
        'contact',
        'email',
        'mall_id',
        'area_id',
        'address_1',
        'address_2',
        'postal_code',
        'city',
        'state_id',
        'country_id',
        'driver_id',
        'driver_route',
        'backup_driver_id',
        'backup_driver_route'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            // Ensure at least one of mall_id or area_id is selected
            if (!$model->mall_id && !$model->area_id) {
                throw new ModelNotFoundException('Please select either a Mall or an Area.');
            }

            // Ensure only one is selected
            if ($model->mall_id && $model->area_id) {
                throw new ModelNotFoundException('Please select either a Mall or an Area, not both.');
            }

            // Set unselected field to null
            if ($model->mall_id) {
                $model->area_id = null;
            } else if ($model->area_id) {
                $model->mall_id = null;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function attr_status()
    {
        return $this->belongsTo(AttrStatus::class, 'status', 'id');
    }

    public function mall()
    {
        return $this->belongsTo(Mall::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function backup_driver()
    {
        return $this->belongsTo(Driver::class, 'backup_driver_id');
    }

    /**
     * Get the mall or area
     *
     * @return string
     */
    public function getMallOrAreaAttribute() :  string
    {
        return $this->mall_id ? $this->mall->name : $this->area->postal.' '.$this->area->name;
    }
}