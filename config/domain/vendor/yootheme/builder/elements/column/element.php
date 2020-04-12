<?php

return [

    'transforms' => [

        'render' => function ($node, array $params) {

            /**
             * @var $parent
             */
            extract($params);

            // Width
            $breakpoints = ['s', 'm', 'l', 'xl'];
            $breakpoint = $parent->props['breakpoint'];

            // Above Breakpoint
            // `$node->widths` is being set through the parent row node
            $width = !empty($node->widths[0]) ? $node->widths[0] : 'expand';
            $node->attrs['class'][] = !in_array($width, ['expand', 'fixed']) ? 'uk-flex-auto' : '';

            $width = $width === 'fixed' ? $parent->props['fixed_width'] : $width;
            $node->attrs['class'][] = "uk-width-{$width}" . ($breakpoint ? "@{$breakpoint}" : '');

            // Intermediate Breakpoint
            if (isset($node->widths[1]) && $pos = array_search($breakpoint, $breakpoints)) {
                $breakpoint = $breakpoints[$pos - 1];
                $width = $node->widths[1] ?: 'expand';
                $node->attrs['class'][] = "uk-width-{$width}@{$breakpoint}";
            }

            // Order
            if (end($parent->children) === $node && !empty($parent->props['order_last'])) {
                $node->attrs['class'][] = "uk-flex-first@{$breakpoint}";
            }

        },

    ],

    'updates' => [

        '1.22.0-beta.0.1' => function ($node, array $params) {
            unset($node->props['widths']);
        },

        '1.18.0' => function ($node, array $params) {

            /**
             * @var $parent
             */
            extract($params);

            if (!isset($node->props['vertical_align']) && @$parent->props['vertical_align'] === true) {
                $node->props['vertical_align'] = 'middle';
            }

        },

    ],

];
