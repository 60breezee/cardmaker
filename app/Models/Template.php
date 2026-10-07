<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['configuration' => 'array', 'is_active' => 'boolean', 'is_premium' => 'boolean'];
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
}
