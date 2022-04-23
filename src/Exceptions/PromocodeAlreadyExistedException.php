<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Exceptions;

use InvalidArgumentException;

class PromocodeAlreadyExistedException extends InvalidArgumentException
{

    public function __construct(string $code)
    {
        $message =  "The given promocode `{$code}` is already existed. Please try with a new one.";

        parent::__construct($message);
    }
}
