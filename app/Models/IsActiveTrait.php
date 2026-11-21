<?php

namespace App\Models;

trait IsActiveTrait
{
    public function scopeIsActive($builder)
    {
        $builder->where('is_active', 1);
    }
    public function scopeNonActive($builder)
    {
        $builder->where('is_active', 0);
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->getRawOriginal('is_active');
    }
}
