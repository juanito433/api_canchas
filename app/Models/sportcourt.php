<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sportcourt extends Model
{
    /** @use HasFactory<\Database\Factories\SportcourtFactory> */
    use HasFactory;
    protected $fillable = [
        'sport_id',
        'num_sportcourt',
    ];

    public function sport()
    {
        return $this->belongsTo(sport::class);
    }
    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }
}
