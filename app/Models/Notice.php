<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    /** @use HasFactory<\Database\Factories\NoticeFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'date_published',
    ];

    protected $casts = [
        'date_published' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Admin que creó la notificación
    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Usuarios que recibieron esta notificación ->withTimestamps();

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'notice_user')
            ->using(\App\Models\NoticeUser::class)
            ->withPivot('read_at')
            ->withTimestamps();
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'notice_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
