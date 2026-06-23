<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoiceitem;
use App\Models\Customer;
use App\Models\Barber;
use App\Models\Shift;
use App\Models\Appointment;
class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'barber_id',
        'shift_id',
        'appointment_id',
        'total_price',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoiceitems()
    {
        return $this->hasMany(Invoiceitem::class);
    }
}
