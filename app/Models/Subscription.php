<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'expires_at' => 'datetime'];
    }
}
