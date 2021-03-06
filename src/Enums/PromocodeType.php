<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Enums;

enum PromocodeType: string
{
    case FLAT = 'flat';
    case PERCENT = 'percent';

    public static function getOptions(): array
    {
        $options = [];

        foreach (self::cases() as $option) {
            $options[] = [
                'label' => ucfirst($option->value),
                'value' => $option->value,
            ];
        }

        return $options;
    }
}
