<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'timezone',
    ];

    /**
     * The "booted" method of the model.
     *
     * Applies a global scope to always order states by name in descending order.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope('orderByName', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }

    /**
     * Get the state's display name as "Name (Code)".
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }
}
