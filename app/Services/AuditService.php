<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        string $action,
        ?string $entityType = null,
        ?string $entityId = null,
        ?Request $request = null,
        ?int $responseStatus = null,
        ?string $source = null,
        ?string $userId = null,
    ): AuditLog {
        $sensitiveKeys = ['password', 'password_confirmation', 'token', 'refresh_token'];

        $body = $request?->except($sensitiveKeys);

        // Auto-detect source from request path if not provided
        if ($source === null && $request !== null) {
            $source = str_starts_with($request->path(), 'api/') ? 'api' : 'web';
        }

        return AuditLog::create([
            'user_id' => $userId ?? $request?->user()?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_body' => $body,
            'response_status' => $responseStatus,
            'source' => $source,
        ]);
    }
}
