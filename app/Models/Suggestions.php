<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suggestions extends Model
{
    /** @use HasFactory<\Database\Factories\SuggestionsFactory> */
    use HasFactory;

    protected $fillable = [
        'issuse',
        'message',
        'member_id',
    ];
}
