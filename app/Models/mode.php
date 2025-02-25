<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mode extends Model
{
    /** @use HasFactory<\Database\Factories\ModeFactory> */
    use HasFactory;
    protected $fillable=[
        'name',
        'date',
        'start_time',
        'end_time', 
        'sportcourt_id'
    ];
    public function sport()
    {
        return $this->belongsTo(sportcourt::class);
    }
    // Relación uno a muchos con Mode
    public function mode()
    {
        return $this->belongsTo(sportcourt::class);
    }

}
