<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FormOption extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'value',
        'parent_type',
        'parent_value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope: active options of a given type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope: options filtered by parent.
     */
    public function scopeForParent($query, string $parentType, string $parentValue)
    {
        return $query->where('parent_type', $parentType)->where('parent_value', $parentValue);
    }

    /**
     * All known option types.
     */
    public const TYPES = [
        'vehicle_type',
        'category',
        'fuel_type',
        'transmission',
        'status',
        'contract_type',
        'coverage_type',
        'color',
    ];
}
