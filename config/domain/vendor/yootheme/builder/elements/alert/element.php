<?php

return [

    'transforms' => [

        'render' => function ($node) {

            // Don't render element if content fields are empty
            return $node->props['title'] || $node->props['content'];

        },

    ],

    'updates' => [

        '1.20.0-beta.4' => function ($node, array $params) {

            if (isset($node->props['maxwidth_align'])) {
                $node->props['block_align'] = $node->props['maxwidth_align'];
                unset($node->props['maxwidth_align']);
            }

        },

    ],

];
