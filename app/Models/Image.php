<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'original',
        'width',
        'height'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
