<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Exceptions;

use InvalidArgumentException;

class PromocodeAlreadyUsedForOrderException extends InvalidArgumentException
{
    public function __construct(string|int $orderId, string $code)
    {
        parent::__construct("The given code `{$code}` is already applied for the order with id {$orderId}.");
    }
}
