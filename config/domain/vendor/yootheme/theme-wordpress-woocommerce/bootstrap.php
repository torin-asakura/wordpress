<?php

namespace YOOtheme\Theme\Wordpress;

return [

    'actions' => [

        'customize_controls_init' => [
            WooCommerceListener::class => 'initConfig',
        ],

    ],

    'filters' => [

        'loop_shop_per_page' => [
            WooCommerceListener::class => ['itemsPerPage', 20],
        ],

        'woocommerce_enqueue_styles' => [
            WooCommerceListener::class => 'removeStyle',
        ],

    ],

];
