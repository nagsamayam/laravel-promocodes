<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NagSamayam\Promocodes\Contracts\PromocodeContract;
use NagSamayam\Promocodes\Facades\Promocodes;

trait AppliesPromocode
{
    /**
     * @return BelongsToMany
     */
    public function appliedPromocodes(): BelongsToMany
    {
        return $this->belongsToMany(
            config('promocodes.models.promocodes.model'),
            config('promocodes.models.pivot.table_name'),
            config('promocodes.models.users.foreign_id'),
            config('promocodes.models.promocodes.foreign_id'),
        )
            ->using(config('promocodes.models.pivot.model'))
            ->withPivot('created_at');
    }

    /**
     * @return HasMany
     */
    public function boundPromocodes(): HasMany
    {
        return $this->hasMany(
            config('promocodes.models.promocodes.model'),
            config('promocodes.models.users.foreign_id'),
        );
    }

    public function applyPromocode(string $code, ?array $promoCodeUsermeta = null): ?PromocodeContract
    {
        return Promocodes::code($code)->user($this)->apply($promoCodeUsermeta);
    }
}
