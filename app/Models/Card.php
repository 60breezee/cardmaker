<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Card extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'user_id', 'public_identifier'];

    protected static function booted(): void
    {
        static::creating(function (Card $card) {
            $card->public_identifier ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return ['data' => 'array', 'is_public' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function exports()
    {
        return $this->hasMany(CardExport::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
