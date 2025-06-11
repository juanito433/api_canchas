<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'schedule_id',
        'teammates',
        'date',
        'confirmation',
        'status',
    ];

    //una reservacion solo puede tener una modalidad
    public function mode()
    {
        return $this->belongsTo(mode::class);
    }
    //una reservacion solo puede tener un miembro
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //una reservacion puede terner muchos compañeros
    public function teammates()
    {
        return $this->hasMany(User::class);
    }

    //Relación con schedules
    //una reservacion solo puede tener un horario
    public function schedule()
    {
        return $this->belongsTo(schedules::class);
    }
}
