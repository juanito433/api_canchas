<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sport extends Model
{
    /** @use HasFactory<\Database\Factories\SportFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public function sportCourts()
    {
        return $this->hasMany(SportCourt::class);
    }
}
