<?php

return [

    'transforms' => [

        'render' => function ($node) {

            // Don't render element if content fields are empty
            return $node->props['position_x'] && $node->props['position_y'] && ($node->props['title'] || $node->props['meta'] || $node->props['content'] || $node->props['image']);

        },

    ],

];