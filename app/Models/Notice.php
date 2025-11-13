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
}
