<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personality extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
