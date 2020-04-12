<?php

return [

    'render' => function ($node) {

        // Don't render element if content fields are empty
        return $node->props['links_1'] || $node->props['links_2'] || $node->props['links_3'] || $node->props['links_4'] || $node->props['links_5'];

    },

    'updates' => [

        '2.0.5.1' => function ($node, array $params) {

            $links = !empty($node->props['links']) ? (array) $node->props['links'] : [];
            for ($i = 0; $i <= 4; $i++) {
                if (isset($links[$i])) {
                    $node->props['link_'.($i+1)] = $links[$i];
                }
            }
            unset($node->props['links']);

        },

        '1.22.0-beta.0.1' => function ($node, array $params) {

            if (isset($node->props['gutter'])) {
                $node->props['gap'] = $node->props['gutter'];
                unset($node->props['gutter']);
            }

        },

        '1.20.0-beta.4' => function ($node, array $params) {

            if (isset($node->props['maxwidth_align'])) {
                $node->props['block_align'] = $node->props['maxwidth_align'];
                unset($node->props['maxwidth_align']);
            }

        },

    ],

];
