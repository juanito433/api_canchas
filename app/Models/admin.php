<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class admin extends Model
{
    /** @use HasFactory<\Database\Factories\AdminFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'phone'
    ];
    //Un admin puede penalizar a muchos miembros
    public function penalties()
    {
        return $this->hasMany(penalty::class);
    }
    
}
