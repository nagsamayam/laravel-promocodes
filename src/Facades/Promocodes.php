<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Facades;

use Illuminate\Support\Facades\Facade;

class Promocodes extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'promocodes';
    }
}
