<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lastname',
        'lastname2',
        'username',
        'phone',
        'role',
        'photo_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    /* Relaciones  */
    public function suggestions()
    {
        return $this->hasMany(Suggestions::class);
    }
    public function reservations()
    {
        return $this->hasMany(reservation::class);
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function receivedNotices()
    {
        return $this->belongsToMany(Notice::class, 'notice_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }


    public function notices()
    {
        return $this->hasMany(Notice::class, 'user_id');
    }
}
