<?php
return [
    'buns' => [
        'flour' => 1/236,   // in bags per 1 bun
        'oil'   => 0.1/150, // 0.1 liters per 150 buns → converted per bun
        'yeast' => 0.005/150, // kg per bun
        'sugar' => 0.01/150,  // kg per bun
    ],
    'small_breads' => [
        'flour' => 1/60,
        'oil'   => 0.02/60,
        'yeast' => 0.003/60,
    ],
    'big_breads' => [
        'flour' => 1/30,
        'oil'   => 0.05/30,
        'yeast' => 0.005/30,
    ],
    'donuts' => [
        'flour' => 1/120,
        'oil'   => 0.03/120,
        'yeast' => 0.004/120,
        'sugar' => 0.01/120,
    ],
    'half_cakes' => [
        'flour' => 1/8,
        'oil'   => 0.05/8,
        'sugar' => 0.02/8,
        'eggs'  => 1/8,   // 1 egg per half cake
    ],
    'block_cakes' => [
        'flour' => 1/5,
        'oil'   => 0.1/5,
        'sugar' => 0.05/5,
        'eggs'  => 2/5,
    ],
    'slab_cakes' => [
        'flour' => 1/4,
        'oil'   => 0.1/4,
        'sugar' => 0.05/4,
        'eggs'  => 2/4,
    ],
    'birthday_cakes' => [
        'flour' => 1/3,
        'oil'   => 0.15/3,
        'sugar' => 0.1/3,
        'eggs'  => 4/3,
    ],
];
