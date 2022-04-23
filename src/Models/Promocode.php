<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Models;

use App\Traits\HasMeta;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NagSamayam\Promocodes\Contracts\PromocodeContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use NagSamayam\Promocodes\Enums\PromocodeType;

class Promocode extends Model implements PromocodeContract
{
    use HasFactory;
    use HasMeta;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'code', 'usages_left', 'bound_to_user', 'multi_use', 'details', 'expired_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expired_at' => 'datetime',
        'usages_left' => 'integer',
        'bound_to_user' => 'boolean',
        'multi_use' => 'boolean',
        'details' => 'array',
        'meta' => 'array',
        'type' => PromocodeType::class,
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('promocodes.models.promocodes.table_name'));
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $meta = $model->meta;

            $meta['user_agent'] = request()->userAgent();
            $meta['ip_address'] = request()->ip();
            $meta['referer_url'] = request()->headers->get('referer');

            $model->meta = $meta;
        });

        static::updating(function ($model) {
            $meta = $model->meta;

            $meta['updated_ip_address'] = request()->userAgent();
            $meta['updated_user_agent'] = request()->ip();
            $meta['updated_referer_url'] = request()->headers->get('referer');

            $model->meta = $meta;
        });
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('promocodes.models.users.model'),
            config('promocodes.models.users.foreign_id'),
        );
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('promocodes.models.users.model'),
            config('promocodes.models.pivot.table_name'),
            config('promocodes.models.promocodes.foreign_id'),
            config('promocodes.models.users.foreign_id'),
        )
            ->using(config('promocodes.models.pivot.model'))
            ->withPivot('created_at', 'session_id', 'order_id', 'meta');
    }

    /**
     * @param Builder $builder
     * @return void
     */
    public function scopeAvailable(Builder $builder): void
    {
        $builder->whereNull('expired_at')->orWhere('expired_at', '>', now());
    }

    /**
     * @param Builder $builder
     * @param string $code
     * @return Builder
     */
    public function scopeFindByCode(Builder $builder, string $code): Builder
    {
        return $builder->where('code', $code);
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isBefore(now());
    }

    /**
     * @return bool
     */
    public function isUnlimited(): bool
    {
        return $this->usages_left === -1;
    }

    /**
     * @return bool
     */
    public function hasUsagesLeft(): bool
    {
        return $this->isUnlimited() || $this->usages_left > 0;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function allowedForUser(User $user): bool
    {
        return !$this->bound_to_user || $this->user === null || $this->user->is($user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function appliedByUser(User $user): bool
    {
        return $this->users()->where(DB::raw('users.id'), $user->id)->exists();
    }

    public function getDetail(string $key, mixed $fallback = null): mixed
    {
        return Arr::get($this->details, $key) ?? $fallback;
    }

    public function getApplicableDiscount(int|float $total): int|float
    {
        return match ($this->type) {
            PromocodeType::FLAT => $this->getDetail('discount'),
            PromocodeType::PERCENT => $this->calculateMaxDiscount($total),
            default => 0
        };
    }

    public function calculateMaxDiscount(int|float $total): int|float
    {
        $calculatedDiscount = round(($this->getDetail('percent_off') / 100) * $total);
        return $calculatedDiscount > $this->max_discount ? $this->max_discount : $calculatedDiscount;
    }
}
