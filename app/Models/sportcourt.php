<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sportcourt extends Model
{
    use HasFactory;

    protected $fillable = [
        'sport_id',
        'num_sportcourt',
    ];

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }

    public function courts()
    {
        return $this->hasMany(mode::class); // Valida que esto sí lo necesites
    }
}
