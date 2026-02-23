<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Structure extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'sigle', 'region', 'direction', 'direction_id', 'site', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function directionRelation(): BelongsTo
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->code . '-' . $this->name . ($this->sigle ? ' (' . $this->sigle . ')' : '');
    }
}
