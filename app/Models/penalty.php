<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class penalty extends Model
{
    /** @use HasFactory<\Database\Factories\PenaltyFactory> */
    use HasFactory;
    protected $fillable = [
        'cause',
        'penalty',
        'date',
        'user_id',
    ];

    //Una penalización solo puede tener un miembro
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
