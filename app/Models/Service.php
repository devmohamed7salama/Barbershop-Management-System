<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
  protected $fillable = [
        'service_name',
        'service_description',
        'service_image',
        'service_price',
        'service_duration',
        'created_at',
        'updated_at',
    ];
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class);
    }
}
