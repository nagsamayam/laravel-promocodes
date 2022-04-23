<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NagSamayam\Promocodes\Contracts\PromocodeContract;

class GuestAppliedPromocode
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var PromocodeContract
     */
    public PromocodeContract $promocode;

    /**
     * @param PromocodeContract $promocode
     */
    public function __construct(PromocodeContract $promocode)
    {
        $this->promocode = $promocode;
    }
}
