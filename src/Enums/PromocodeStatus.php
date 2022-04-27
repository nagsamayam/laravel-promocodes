<?php

declare(strict_types=1);

namespace NagSamayam\Promocodes\Enums;

enum PromocodeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

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

    public function badge(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge rounded-pill badge-light-success',
            self::INACTIVE => 'badge rounded-pill badge-light-danger',
            default => '',
        };
    }
}
