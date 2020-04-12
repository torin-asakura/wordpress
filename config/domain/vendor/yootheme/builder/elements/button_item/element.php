<?php

return [

    'transforms' => [

        'render' => function ($node) {

            // Don't render element if content fields are empty
            return $node->props['content']  && ($node->props['link'] || $node->props['icon']);

        },

    ],

    'updates' => [

        '1.18.0' => function ($node, array $params) {

            if (@$node->props['link_target'] === true) {
                $node->props['link_target'] = 'blank';
            }

            if (@$node->props['button_style'] === 'muted') {
                $node->props['button_style'] = 'link-muted';
            }

        },

    ],

];
