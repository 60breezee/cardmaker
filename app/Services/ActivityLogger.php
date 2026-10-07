<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function record(string $event, ?Model $subject = null, array $metadata = [], ?Request $request = null): ActivityLog
    {
        return ActivityLog::create(['user_id' => $request?->user()?->id ?? auth()->id(), 'event' => $event, 'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(), 'metadata' => $metadata, 'ip_address' => $request?->ip()]);
    }
}
