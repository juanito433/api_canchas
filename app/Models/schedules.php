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
    //Relacion con sportcourt
    //Un horario pertenece a muchas canchas
    public function sportcourt()
    {
        return $this->belongsTo(sportcourt::class);
    }
    //Relacion con mode
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
