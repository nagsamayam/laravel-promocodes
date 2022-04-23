<?php

declare(strict_types=1);

use Carbon\CarbonInterface;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use NagSamayam\Promocodes\Contracts\PromocodeContract;
use NagSamayam\Promocodes\Enums\PromocodeType;
use NagSamayam\Promocodes\Exceptions\PromocodeDoesNotExistException;
use NagSamayam\Promocodes\Facades\Promocodes;

if (!function_exists('applyPromocode')) {
    function applyPomocode(
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

if (!function_exists('expirePromocode')) {
    /**
     * @param string $code
     *
     * @return bool
     */
    function expirePromocode(string $code): bool
    {
        $promocode = app(PromocodeContract::class)->findByCode($code)->first();

        if (!$promocode) {
            throw new PromocodeDoesNotExistException($code);
        }

        return $promocode->update(['expired_at' => now()]);
    }
}

if (!function_exists('createPromocodes')) {
    function createPromocodes(
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

        return $promocodes->create($customPromoCode);
    }
}

if (!function_exists('createCustomPromocode')) {
    function createCustomPromocode(
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

        return $promocodes->create($code);
    }
}
