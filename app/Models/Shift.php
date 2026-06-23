<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
class Shift extends Model
{
   
    protected $fillable = [
        'start_time',
        'end_time',
        'total_cash',
        'total_revenue',
        'total_orders',
        'shift_status',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
