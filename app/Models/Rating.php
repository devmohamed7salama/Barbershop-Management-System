<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'invoice_id',
        'barber_id',
        'shop_rate',
        'barber_rate',
        'comment',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
