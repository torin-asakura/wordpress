<?php

namespace YOOtheme;

return [

    'transforms' => [

        'render' => function ($node) {

            $getItem = function ($text, $link, $active = true) {
                return (object) compact('active', 'text', 'link');
            };

            if (is_single()) {

                $pagination = [];

                if ($previous = get_adjacent_post(false, '', true, 'category')) {
                    $pagination['previous'] = $getItem(__('Previous', 'yootheme'), get_permalink($previous));
                }

                if ($next = get_adjacent_post(false, '', false, 'category')) {
                    $pagination['next'] = $getItem(__('Next', 'yootheme'), get_permalink($next));
                }

                $node->props['pagination_type'] = 'previous/next';

            } else {

                global $wp_query;

                if ($wp_query->max_num_pages <= 1) {
                    return false;
                }

                $total = isset($wp_query->max_num_pages) ? $wp_query->max_num_pages : 1;
                $current = (int) get_query_var('paged') ?: 1;
                $endSize = 1;
                $midSize = 1;
                $dots = false;

                if ($node->props['pagination_start_end'] && $current !== 1) {
                    $pagination['start'] = $getItem(__('Start', 'yootheme'), get_pagenum_link(1, false), false);
                }

                if ($current > 1 and $previous = previous_posts(false)) {
                    $pagination['previous'] = $getItem(__('Previous', 'yootheme'), $previous, false);
                }

                for ($n = 1; $n <= $total; $n++) {

                    $active = $n <= $endSize || $current && $n >= $current - $midSize && $n <= $current + $midSize || $n > $total - $endSize;

                    if ($active || $dots) {
                        $pagination[$n] = $getItem(!$active ? __('&hellip;') : number_format_i18n($n), get_pagenum_link($n, false), $n === $current && $active);
                        $dots = $active;
                    }

                }

                if ($current < $total and $next = next_posts($wp_query->max_num_pages, false)) {
                    $pagination['next'] = $getItem(__('Next', 'yootheme'), $next, false);
                }

                if ($current < $total && $node->props['pagination_start_end']) {
                    $pagination['end'] = $getItem(__('End', 'yootheme'), get_pagenum_link($total, false), false);
                }

            }

            $node->props['pagination'] = $pagination;

        },

    ],

];
