<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mode extends Model
{
    /** @use HasFactory<\Database\Factories\ModeFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
    ];

    //Relacion de foraneas con schedules
    //Un mode puede tener muchos schedules
    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }
}
