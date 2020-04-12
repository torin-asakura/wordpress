<?php

return [

    'transforms' => [

        'render' => function ($node) {
            return !empty($node->props['content']) && is_active_sidebar($node->props['content']);
        },

    ],

    'updates' => [

        '1.22.0-beta.0.1' => function ($node) {

            if (isset($node->props['grid_gutter'])) {
                $node->props['column_gap'] = $node->props['grid_gutter'];
                $node->props['row_gap'] = $node->props['grid_gutter'];
                unset($node->props['grid_gutter']);
            }

            if (isset($node->props['grid_divider'])) {
                $node->props['divider'] = $node->props['grid_divider'];
                unset($node->props['grid_divider']);
            }

        },

        '1.20.0-beta.1.1' => function ($node) {

            if (isset($node->props['maxwidth_align'])) {
                $node->props['block_align'] = $node->props['maxwidth_align'];
                unset($node->props['maxwidth_align']);
            }

        },

    ],

];
