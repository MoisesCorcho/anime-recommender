<?php

declare(strict_types=1);

return [

    'registration_bonus' => (int) env('CREDITS_REGISTRATION_BONUS', 50),

    'pro_monthly_allowance' => (int) env('CREDITS_PRO_MONTHLY_ALLOWANCE', 5000),

    'rate_limit_free' => (int) env('CREDITS_RATE_LIMIT_FREE', 5),

    'rate_limit_pro' => (int) env('CREDITS_RATE_LIMIT_PRO', 10),

    'prices' => [
        'pro'       => env('STRIPE_PRICE_PRO_MONTHLY', ''),
        'pack_100'  => env('STRIPE_PRICE_PACK_100', ''),
        'pack_500'  => env('STRIPE_PRICE_PACK_500', ''),
        'pack_1000' => env('STRIPE_PRICE_PACK_1000', ''),
    ],

    'pack_credits' => [
        'pack_100'  => 100,
        'pack_500'  => 500,
        'pack_1000' => 1000,
    ],

];
