<?php

namespace App\Services\Admin;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminActivityLogger
{
    public function record(string $action, ?Model $subject = null, array $metadata = [], ?Request $request = null): AdminActivityLog
    {
        $request ??= request();
        return AdminActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
