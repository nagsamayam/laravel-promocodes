<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Models;

use App\Traits\HasMeta;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PromocodeUser extends Pivot
{
    use HasMeta;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('promocodes.models.pivot.table_name'));
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
    }

    public function appliedForOrderId(Promocode $promocode, string|int $orderId): bool
    {
        return self::where('promocode_id', $promocode->id)->whereIn('meta->order_id', [$orderId])->exists();
    }
}
