<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Service;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'appointment_date',
        'appointment_time',
        'source',
        'appointment_status',
        'appointment_notes',
    ];

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
