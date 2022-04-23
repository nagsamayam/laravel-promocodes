<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Exceptions;

use InvalidArgumentException;

class UserHasNoAppliesPromocodeTrait extends InvalidArgumentException
{
    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct("The given user model doesn't have AppliesPromocode trait.");
    }
}
