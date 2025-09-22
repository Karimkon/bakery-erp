<?php

return [
    // If the total value the driver carries today (opening + dispatched) is >= this,
    // use the full per-piece rates. Otherwise use half.
    'threshold' => 1_000_000, // UGX

    'rates' => [
        'big_breads'     => 200,
        'small_breads'   => 100,
        'buns'           => 200,
        'donuts'         => 100,
        'half_cakes'     => 100,
        'block_cakes'    => 200,
        'slab_cakes'     => 200,
        'birthday_cakes' => 200,
    ],

    // How to measure the threshold. Defaults to value of (opening + dispatched).
    // Options: 'available' (opening + dispatched), 'dispatched', 'sold'
    'threshold_basis' => 'available',
];
