<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'sigle', 'region', 'direction', 'site', 'type', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
