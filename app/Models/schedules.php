<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class schedules extends Model
{
    /** @use HasFactory<\Database\Factories\SchedulesFactory> */
    use HasFactory;
    protected $fillable = [
        'days',
        'sportcourt_id',
        'mode_id',
        'start_time',
        'end_time',
        'status',
    ];
    public function sportcourt()
    {
        return $this->belongsTo(sportcourt::class);
    }
    /* una cancha tienen muchos horarios */
    public function sportCourts()
    {
        return $this->hasMany(sportcourt::class);
    }

    //Un horario pertenece a muchos modos
    public function mode()
    {
        return $this->belongsTo(mode::class);
    }

    //Relacion con reservation
    //Un horario puede tener muchas reservas
    public function reservations()
    {
        return $this->hasMany(reservation::class);
    }
}
