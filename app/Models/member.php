<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'lastname',
        'lstname2',
        'usernamemember',
        'email',
        'password',
        'phone'
    ];
    //un miembro puede tener muchas reservas
    public function reservations()
    {
        return $this->hasMany(reservation::class);
    }
    //una reservacion puede tener muchos compañeros
    public function teammates()
    {
        return $this->hasMany(reservation::class);
    }
    //un miembro puede tener muchas penalizaciones
    public function penalties()
    {
        return $this->hasMany(penalty::class);
    }
}
