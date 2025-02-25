<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sportcourt extends Model
{
    /** @use HasFactory<\Database\Factories\SportcourtFactory> */
    use HasFactory;
    protected $fillable=[
        'sport_id',
        'num_sportcourt'
    ];


    // Relación inversa: muchas canchas pertenecen a un deporte
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }
    public function courts()
    {
        return $this->hasMany(mode::class);
    }

}


