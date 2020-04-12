<?php

namespace YOOtheme;

use YOOtheme\Theme\Wordpress\Breadcrumbs;

return [

    'transforms' => [

        'render' => function ($node) {

            $items = Breadcrumbs::getItems();

            if (!$node->props['show_home']) {
                array_shift($items);
            } elseif ($node->props['home_text']) {
                $items[0]->name = __($node->props['home_text'], 'yootheme');
            }

            $node->props['items'] = $items;
        },

    ],

];
