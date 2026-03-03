<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDriver extends Model
{
    use HasUuids;

    protected $fillable = [
        'vehicle_id',
        'direction',
        'matricule',
        'driver_license',
        'is_primary',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
