<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Enums;

enum PromocodeType: string
{
    case FLAT = 'flat';
    case PERCENT = 'percent';
}
