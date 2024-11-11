<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyBoost extends Model
{
    use HasFactory;

    protected $table = 'property_boosts';

    // protected $fillable = [
    //     'property_id',
    //     'customer_id',
    //     'start_date',  // Will be set to current date and time when storing
    //     'end_date',    // Will be calculated based on requested days
    //     'price',
    //     'payment_getwey',  // Ensure correct gateway value is stored
    //     'order_id',
    //     'payment_screenshot',
    //     'payment_detail',
    //     'is_payed',
    // ];

    protected $guarded = ['id'];
    /**
     * Relationship with Property
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Set the start date to the current date and time
     */
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = now(); // Sets start date to current date and time
    }

    /**
     * Set the end date based on the start date and requested number of days
     */
   
}
