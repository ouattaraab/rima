<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePhoto extends Model
{
    use HasUuids;

    protected $fillable = [
        'vehicle_id', 'photo_type', 'file_path', 'thumbnail_path',
        'original_name', 'size_bytes', 'width', 'height',
        'gps_latitude', 'gps_longitude', 'captured_at', 'comment',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->file_path && str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return $this->file_path ? asset('storage/' . $this->file_path) : '';
    }
}
