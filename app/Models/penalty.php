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
        'member_id',
        'admin_id',
    ];

    //Una penalización solo puede tener un miembro
    public function member()
    {
        return $this->belongsTo(member::class);
    }
    //Una penalización solo puede tener un admin
    public function admin()
    {
        return $this->belongsTo(admin::class);
    }
}
