<?php
return [
    // Minimum pieces per flour bag (only a lower bound)
    'yield_min_per_bag' => [
        'buns' => 150,
    ],

    // Flour “bags” consumed per ONE unit (tune these anytime)
    'flour_equiv_bags_per_unit' => [
        'buns'           => 1/236,
        'small_breads'   => 1/60,
        'big_breads'     => 1/30,
        'donuts'         => 1/120,
        'half_cakes'     => 1/8,
        'block_cakes'    => 1/5,
        'slab_cakes'     => 1/4,
        'birthday_cakes' => 1/3,
    ],
];
