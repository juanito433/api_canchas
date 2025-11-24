<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeUser extends Model
{
    protected $table = 'notice_user';

    protected $fillable = [
        'notice_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];


}
