<?php

declare(strict_types=1);

return [
    'models' => [
        'promocodes' => [
            'model'      => \NagSamayam\Promocodes\Models\Promocode::class,
            'table_name' => 'promocodes',
            'foreign_id' => 'promocode_id',
        ],

        'users' => [
            'model'      => \App\Models\User::class,
            'table_name' => 'users',
            'foreign_id' => 'user_id',
        ],

        'pivot' => [
            'model'      => \NagSamayam\Promocodes\Models\PromocodeUser::class,
            'table_name' => 'promocode_user',
        ],
    ],
    'code_mask'       => '**-****-****',
    'allowed_symbols' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789',
];
