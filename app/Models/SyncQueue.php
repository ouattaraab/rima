<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncQueue extends Model
{
    use HasUuids;

    protected $table = 'sync_queue';

    protected $fillable = [
        'vehicle_id', 'agent_id', 'sync_status',
        'retry_count', 'error_message', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
