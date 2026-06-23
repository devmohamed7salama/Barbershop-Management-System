<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;
use App\Models\Service;
class AppointmentService extends Model
{
    protected $table = 'appointment_service';

    protected $fillable = [
        'appointment_id',
        'service_id',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
