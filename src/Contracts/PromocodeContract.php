<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User;
use NagSamayam\Promocodes\Models\Promocode;

interface PromocodeContract
{
    //
    public function user(): BelongsTo;

    //
    public function users(): BelongsToMany;

    //
    public function scopeAvailable(Builder $builder): void;

    //
    public function scopeFindByCode(Builder $builder, string $code): Builder;

    //
    public function isExpired(): bool;

    //
    public function isUnlimited(): bool;

    //
    public function hasUsagesLeft(): bool;

    //
    public function allowedForUser(User $user): bool;

    public function getApplicableDiscount(int|float $total): int|float;

    public function markAsActive(mixed $adminId): Promocode;

    public function markAsInActive(mixed $adminId): Promocode;
}
