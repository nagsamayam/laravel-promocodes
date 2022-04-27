<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes;

use NagSamayam\Promocodes\Exceptions\PromocodeAlreadyUsedByUserException;
use NagSamayam\Promocodes\Exceptions\PromocodeBoundToOtherUserException;
use NagSamayam\Promocodes\Exceptions\UserHasNoAppliesPromocodeTrait;
use NagSamayam\Promocodes\Exceptions\PromocodeDoesNotExistException;
use NagSamayam\Promocodes\Exceptions\PromocodeNoUsagesLeftException;
use NagSamayam\Promocodes\Exceptions\UserRequiredToAcceptPromocode;
use NagSamayam\Promocodes\Exceptions\PromocodeExpiredException;
use NagSamayam\Promocodes\Contracts\PromocodeUserContract;
use NagSamayam\Promocodes\Events\GuestAppliedPromocode;
use NagSamayam\Promocodes\Events\UserAppliedPromocode;
use NagSamayam\Promocodes\Contracts\PromocodeContract;
use NagSamayam\Promocodes\Traits\AppliesPromocode;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Carbon\CarbonInterface;
use NagSamayam\Promocodes\Enums\PromocodeStatus;
use NagSamayam\Promocodes\Exceptions\PromocodeAlreadyExistedException;
use NagSamayam\Promocodes\Exceptions\PromocodeAlreadyUsedForOrderException;
use NagSamayam\Promocodes\Exceptions\PromocodeNotAcitveException;
use NagSamayam\Promocodes\Models\Promocode;

class Promocodes
{
    /**
     * @var string|null
     */
    protected ?string $code = null;

    /**
     * @var string|null
     */
    protected ?string $mask = null;

    /**
     * @var string|null
     */
    protected ?string $characters = null;

    /**
     * @var bool
     */
    protected bool $boundToUser = false;

    /**
     * @var int
     */
    protected int $count = 1;

    protected string $type;

    public string $status = '';

    /**
     * @var bool
     */
    protected bool $unlimited = false;

    /**
     * @var bool
     */
    protected bool $multiUse = false;

    /**
     * @var array
     */
    protected array $details = [];

    /**
     * @var int
     */
    protected int $usagesLeft = 1;

    /**
     * @var CarbonInterface|null
     */
    protected ?CarbonInterface $expiredAt = null;

    /**
     * @var User|null
     */
    protected ?User $user = null;

    protected ?User $createdByAdmin = null;

    protected ?User $updatedByAdmin = null;

    protected ?int $minOrderValue = null;

    protected ?int $maxDiscount = null;

    protected int|float $orderTotal = 0;

    /**
     * @var PromocodeContract|null
     */
    protected ?PromocodeContract $promocode = null;

    public function __construct()
    {
        $this->status = PromocodeStatus::INACTIVE->value;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function code(string $code): static
    {
        $promocodeModel = app(PromocodeContract::class);
        $promocode = $promocodeModel->findByCode($code)->first();

        $this->code = $code;
        $this->promocode = $promocode;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function user(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function createdByAdmin(?User $createdByAdmin): static
    {
        $this->createdByAdmin = $createdByAdmin;
        return $this;
    }

    public function updatedByAdmin(?User $updatedByAdmin): static
    {
        $this->updatedByAdmin = $updatedByAdmin;
        return $this;
    }

    /**
     * @param string $mask
     * @return $this
     */
    public function mask(string $mask): static
    {
        $this->mask = $mask;
        return $this;
    }

    /**
     * @param string $characters
     * @return $this
     */
    public function characters(string $characters): static
    {
        $this->characters = $characters;
        return $this;
    }

    /**
     * @param bool $boundToUser
     * @return $this
     */
    public function boundToUser(bool $boundToUser = true): static
    {
        $this->boundToUser = $boundToUser;
        return $this;
    }

    /**
     * @param bool $multiUse
     * @return $this
     */
    public function multiUse(bool $multiUse = true): static
    {
        $this->multiUse = $multiUse;
        return $this;
    }

    /**
     * @param bool $unlimited
     * @return $this
     */
    public function unlimited(bool $unlimited = true): static
    {
        $this->unlimited = $unlimited;
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function count(int $count): static
    {
        $this->count = $count;
        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $usagesLeft
     * @return $this
     */
    public function usages(int $usagesLeft): static
    {
        $this->usagesLeft = $usagesLeft;
        return $this;
    }

    /**
     * @param array $details
     * @return $this
     */
    public function details(array $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function minOrderValue(?int $minOrderValue): static
    {
        $this->minOrderValue = $minOrderValue;
        return $this;
    }

    public function maxDiscount(?int $maxDiscount): static
    {
        $this->maxDiscount = $maxDiscount;
        return $this;
    }

    public function orderTotal(int|float $orderTotal): static
    {
        $this->orderTotal = $orderTotal;
        return $this;
    }

    /**
     * @param CarbonInterface $expiredAt
     * @return $this
     */
    public function expiration(CarbonInterface $expiredAt): static
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }

    public function status(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function apply(?array $meta = null): ?PromocodeContract
    {
        if (!$this->promocode) {
            throw new PromocodeDoesNotExistException($this->code);
        }

        if ($this->promocode->status !== PromocodeStatus::ACTIVE) {
            throw new PromocodeNotAcitveException($this->code);
        }

        if (!$this->promocode->hasUsagesLeft()) {
            throw new PromocodeNoUsagesLeftException($this->code);
        }

        if ($this->promocode->isExpired()) {
            throw new PromocodeExpiredException($this->code);
        }

        if ($this->promocode->bound_to_user && !$this->user) {
            throw new UserRequiredToAcceptPromocode($this->code);
        }

        if (isset($meta['order_id'])) {
            $orderId = $meta['order_id'];
            $promocodeUserModel = app(PromocodeUserContract::class);

            if ($promocodeUserModel->appliedForOrderId($this->promocode, $orderId)) {
                throw new PromocodeAlreadyUsedForOrderException($orderId, $this->code);
            }
        }

        if ($this->user) {
            if (!in_array(AppliesPromocode::class, class_uses($this->user), true)) {
                throw new UserHasNoAppliesPromocodeTrait();
            }

            if (!$this->promocode->allowedForUser($this->user)) {
                throw new PromocodeBoundToOtherUserException($this->user, $this->code);
            }

            if (!$this->promocode->multi_use && $this->promocode->appliedByUser($this->user)) {
                throw new PromocodeAlreadyUsedByUserException($this->user, $this->code);
            }

            $this->user->appliedPromocodes()
                ->attach(
                    $this->promocode,
                    [
                        'meta' => $meta,
                        'session_id' => Session::getId()
                    ]
                );

            if ($this->promocode->bound_to_user && $this->promocode->user_id === null) {
                $this->promocode->user()->associate($this->user);
                $this->promocode->save();
            }

            event(new UserAppliedPromocode($this->promocode, $this->user));
        } else {
            $models = config('promocodes.models');
            $promocodeForeignId = $models['promocodes']['foreign_id'];

            $attributes = [
                $promocodeForeignId => $this->promocode->id,
                'session_id' => Session::getId(),
            ];
            if ($meta) {
                $attributes += ['meta' => $meta];
            }

            $promocodeUserModel = app(PromocodeUserContract::class);
            $promocodeUserModel->forceCreate($attributes);

            event(new GuestAppliedPromocode($this->promocode));
        }

        if (!$this->promocode->isUnlimited()) {
            $this->promocode->decrement('usages_left');
        }

        return $this->promocode;
    }

    public function create(?string $code = null): Collection|Promocode
    {
        if ($code) {
            return $this->createWithCustomCode($code);
        }

        return $this->generate()->map(fn (string $code) => $this->savePromocode($code));
    }

    public function createWithCustomCode(string $code): Promocode
    {
        if (app(PromocodeContract::class)->findByCode($code)->exists()) {
            throw new PromocodeAlreadyExistedException($code);
        }

        return $this->savePromocode($code);
    }

    public function updateStatus(string $status): ?static
    {
        return match ($status) {
            PromocodeStatus::ACTIVE->value => self::markAsActive(),
            PromocodeStatus::INACTIVE->value => self::markAsInactive(),
            default => null,
        };
    }

    public function markAsActive()
    {
        return $this->promocode->markAsActive($this->updatedByAdmin?->id);
    }

    public function markAsInactive()
    {
        return $this->promocode->markAsInactive($this->updatedByAdmin?->id);
    }

    public function getApplicableDiscount(bool $forceCheck = false): int|float
    {
        if (!$this->promocode) {
            throw new PromocodeDoesNotExistException($this->code);
        }

        if (!$forceCheck) {

            if ($this->promocode->status !== PromocodeStatus::ACTIVE) {
                throw new PromocodeNotAcitveException($this->code);
            }

            if (!$this->promocode->hasUsagesLeft()) {
                throw new PromocodeNoUsagesLeftException($this->code);
            }

            if ($this->promocode->isExpired()) {
                throw new PromocodeExpiredException($this->code);
            }
        }

        return $this->promocode->getApplicableDiscount($this->orderTotal);
    }

    private function savePromocode(string $code): Promocode
    {
        return app(PromocodeContract::class)->create([
            'user_id' => $this->user?->id,
            'code' => $code,
            'type' => $this->type,
            'usages_left' => $this->unlimited ? -1 : $this->usagesLeft,
            'bound_to_user' => $this->user || $this->boundToUser,
            'multi_use' => $this->multiUse,
            'details' => $this->details,
            'min_order_value' => $this->minOrderValue,
            'max_discount' => $this->maxDiscount,
            'created_by_admin_id' => $this->createdByAdmin?->id,
            'expired_at' => $this->expiredAt,
            'status' => $this->status,
        ]);
    }

    /**
     * @return Collection
     */
    public function generate(): Collection
    {
        $existingCodes = app(PromocodeContract::class)->pluck('code')->toArray();
        $codes = collect([]);

        for ($i = 1; $i <= $this->count; $i++) {
            $code = $this->generateCode();

            while ($this->codeExists($code, $existingCodes)) {
                $code = $this->generateCode();
            }

            $codes->push($code);
            $existingCodes = array_merge($existingCodes, [$code]);
        }

        return $codes;
    }

    /**
     * @return string
     */
    protected function generateCode(): string
    {
        $characters = $this->characters ?? config('promocodes.allowed_symbols');
        $mask = $this->mask ?? config('promocodes.code_mask');
        $maskLength = substr_count($mask, '*');
        $randomCharacter = [];

        for ($i = 1; $i <= $maskLength; $i++) {
            $character = $characters[rand(0, strlen($characters) - 1)];
            $randomCharacter[] = $character;
        }

        shuffle($randomCharacter);
        $length = count($randomCharacter);

        for ($i = 0; $i < $length; $i++) {
            $mask = preg_replace('/\*/', $randomCharacter[$i], $mask, 1);
        }

        return $mask;
    }

    /**
     * @param string $code
     * @param array<string> $existingCodes
     * @return bool
     */
    protected function codeExists(string $code, array $existingCodes): bool
    {
        return in_array($code, $existingCodes, true);
    }
}
