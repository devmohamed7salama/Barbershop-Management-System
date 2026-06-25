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
        'invoice_number',
        'customer_id',
        'barber_id',
        'shift_id',
        'appointment_id',
        'total_price',
        'rating_status',
    ];

    protected static function booted()
    {
        static::created(function ($invoice) {
            $invoice->invoice_number = 'INV-' . date('Y') . '-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
            $invoice->saveQuietly();
        });
    }

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

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }
}
