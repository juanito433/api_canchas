<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pdf_url',
    ];
    // A report belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
