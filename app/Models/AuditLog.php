<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'ip_address', 'user_agent', 'request_body', 'response_status',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'request_body' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
