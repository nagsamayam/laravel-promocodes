<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Exceptions;

use InvalidArgumentException;

class PromocodeNotAcitveException extends InvalidArgumentException
{
    public function __construct(string $code)
    {
        $message =  "The given promocode `{$code}` is not active. Please try with a new one.";

        parent::__construct($message);
    }
}
