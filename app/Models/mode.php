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
    public function schedules()
    {
        return $this->hasMany(schedules::class);
    }
}
