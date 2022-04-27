<?php

declare(strict_types=1);

use NagSamayam\Promocodes\Exceptions\PromocodeDoesNotExistException;
use NagSamayam\Promocodes\Contracts\PromocodeContract;
use NagSamayam\Promocodes\Facades\Promocodes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Carbon\CarbonInterface;
use NagSamayam\Promocodes\Enums\PromocodeStatus;
use NagSamayam\Promocodes\Enums\PromocodeType;

if (!function_exists('apply_promocode')) {

    function apply_promocode(
        string $code,
        ?User $user = null,
        ?array $promoCodeUsermeta = null
    ): ?PromocodeContract {
        $promocodes = Promocodes::code($code);

        if ($user) {
            $promocodes = $promocodes->user($user);
        }

        return $promocodes->apply($promoCodeUsermeta);
    }
}

if (!function_exists('expire_promocode')) {
    /**
     * @param string $code
     * @return bool
     */
    function expire_promocode(string $code): bool
    {
        $promocode = app(PromocodeContract::class)->findByCode($code)->first();

        if (!$promocode) {
            throw new PromocodeDoesNotExistException($code);
        }

        return $promocode->update(['expired_at' => now()]);
    }
}

if (!function_exists('create_promocodes')) {

    function create_promocodes(
        ?string $customPromoCode = null,
        ?string $mask = null,
        ?string $characters = null,
        int $count = 1,
        ?string $type = null,
        bool $unlimited = false,
        int $usages = 1,
        bool $multiUse = false,
        ?User $user = null,
        ?User $createdByAdmin = null,
        ?int $minOrderValue = null,
        ?int $maxDiscount = null,
        bool $boundToUser = false,
        ?CarbonInterface $expiration = null,
        string $status = 'inactive',
        array $details = []
    ): Collection {
        $count = isset($customPromoCode) ? 1 : $count;
        $promocodes = Promocodes::count($count)->details($details);

        if (!isset($customPromoCode)) {
            if ($mask) {
                $promocodes = $promocodes->mask($mask);
            }

            if ($characters !== null) {
                $promocodes = $promocodes->characters($characters);
            }
        }

        $promocodes = isset($type) ? $promocodes->type($type) : $promocodes->type(PromocodeType::FLAT->value);

        if ($unlimited) {
            $promocodes = $promocodes->unlimited();
        }

        if ($usages !== null) {
            $promocodes = $promocodes->usages($usages);
        }

        if ($multiUse) {
            $promocodes = $promocodes->multiUse();
        }

        if ($user) {
            $promocodes = $promocodes->user($user);
        }

        if ($createdByAdmin) {
            $promocodes = $promocodes->createdByAdmin($createdByAdmin);
        }

        if ($minOrderValue) {
            $promocodes = $promocodes->minOrderValue($minOrderValue);
        }

        if ($maxDiscount) {
            $promocodes = $promocodes->maxDiscount($maxDiscount);
        }

        if ($boundToUser) {
            $promocodes = $promocodes->boundToUser();
        }

        if ($expiration) {
            $promocodes = $promocodes->expiration($expiration);
        }

        $promocodes->status($status);

        return $promocodes->create($customPromoCode);
    }
}

if (!function_exists('create_custom_promocode')) {

    function create_custom_promocode(
        ?string $code = null,
        ?string $type = null,
        bool $unlimited = false,
        int $usages = 1,
        bool $multiUse = false,
        ?User $user = null,
        ?User $createdByAdmin = null,
        ?int $minOrderValue = null,
        ?int $maxDiscount = null,
        bool $boundToUser = false,
        ?CarbonInterface $expiration = null,
        string $status = 'inactive',
        array $details = []
    ): PromocodeContract {
        $promocodes = Promocodes::count(1)->details($details);

        $promocodes = isset($type) ? $promocodes->type($type) : $promocodes->type(PromocodeType::FLAT->value);

        if ($unlimited) {
            $promocodes = $promocodes->unlimited();
        }

        if ($usages !== null) {
            $promocodes = $promocodes->usages($usages);
        }

        if ($multiUse) {
            $promocodes = $promocodes->multiUse();
        }

        if ($user) {
            $promocodes = $promocodes->user($user);
        }

        if ($createdByAdmin) {
            $promocodes = $promocodes->createdByAdmin($createdByAdmin);
        }

        if ($minOrderValue) {
            $promocodes = $promocodes->minOrderValue($minOrderValue);
        }

        if ($maxDiscount) {
            $promocodes = $promocodes->maxDiscount($maxDiscount);
        }

        if ($boundToUser) {
            $promocodes = $promocodes->boundToUser();
        }

        if ($expiration) {
            $promocodes = $promocodes->expiration($expiration);
        }

        $promocodes->status($status);

        return $promocodes->create($code);
    }
}

if (!function_exists('update_promocode_status')) {
    function update_promocode_status(string $code, string $status, ?User $updatedByAdmin = null): ?PromocodeContract
    {
        return match ($status) {
            PromocodeStatus::ACTIVE->value => activate_promocode($code, $updatedByAdmin),
            PromocodeStatus::INACTIVE->value => deactivate_promocode($code, $updatedByAdmin),
            default => null,
        };
    }
}

if (!function_exists('activate_promocode')) {
    function activate_promocode(string $code, ?User $updatedByAdmin = null): ?PromocodeContract
    {
        return Promocodes::code($code)->updatedByAdmin($updatedByAdmin)->markAsActive();
    }
}

if (!function_exists('deactivate_promocode')) {
    function deactivate_promocode(string $code, ?User $updatedByAdmin = null): ?PromocodeContract
    {
        return Promocodes::code($code)->updatedByAdmin($updatedByAdmin)->markAsInActive();
    }
}
