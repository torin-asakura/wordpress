<?php

namespace YOOtheme;

return [

    'transforms' => [

        'prerender' => function ($node, array $params) {

            // Sets `$node->widths` property on column child nodes
            if (!empty($node->props['layout'])) {
                foreach (explode('|', $node->props['layout']) as $widths) {
                    foreach (explode(',', $widths) as $index => $width) {
                        if (isset($node->children[$index])) {
                            $node->children[$index]->widths[] = $width;
                        }
                    }
                }
            }

        },

    ],

    'updates' => [

        '2.0.0-beta.5.1' => function ($node) {

            /**
             * @var Config $config
             */
            $config = app(Config::class);

            list($style) = explode(':', $config('~theme.style'));

            if (!in_array($style, ['jack-baker', 'morgan-consulting', 'vibe'])) {
                if (@$node->props['width'] === 'large') {
                    $node->props['width'] = 'xlarge';
                }
            }

            if (in_array($style, ['craft', 'district', 'florence', 'makai', 'matthew-taylor', 'pinewood-lake', 'summit', 'tomsen-brody', 'trek', 'vision', 'yard'])) {
                if (@$node->props['width'] === 'default') {
                    $node->props['width'] = 'large';
                }
            }

        },

        '1.22.0-beta.0.1' => function ($node, array $params) {

            if (isset($node->props['gutter'])) {
                $node->props['column_gap'] = $node->props['gutter'];
                $node->props['row_gap'] = $node->props['gutter'];
                unset($node->props['gutter']);
            }

            if (empty($node->props['layout'])) {
                return;
            }

            switch ($node->props['layout']) {
                case '2-3,':
                    $node->props['layout'] = '2-3,1-3';
                    break;
                case ',2-3':
                    $node->props['layout'] = '1-3,2-3';
                    break;
                case '3-4,':
                    $node->props['layout'] = '3-4,1-4';
                    break;
                case ',3-4':
                    $node->props['layout'] = '1-4,3-4';
                    break;
                case '1-2,,|1-1,1-2,1-2':
                    $node->props['layout'] = '1-2,1-4,1-4|1-1,1-2,1-2';
                    break;
                case ',,1-2|1-2,1-2,1-1':
                    $node->props['layout'] = '1-4,1-4,1-2|1-2,1-2,1-1';
                    break;
                case ',1-2,':
                case ',1-2,|1-2,1-1,1-2':
                    $node->props['layout'] = '1-4,1-2,1-4';
                    break;
                case ',,,|1-2,1-2,1-2,1-2':
                    $node->props['layout'] = '1-4,1-4,1-4,1-4|1-2,1-2,1-2,1-2';
                    break;
            }

        },

    ],

];
