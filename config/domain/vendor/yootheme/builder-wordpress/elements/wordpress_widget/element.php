<?php

namespace YOOtheme;

use YOOtheme\Theme\WidgetsListener;

return [

    'transforms' => [

        'render' => function ($node) {

            global $wp_registered_widgets;

            /**
             * @var Config          $config
             * @var WidgetsListener $helper
             */
            list($config, $helper) = app(Config::class, WidgetsListener::class);

            $widget = isset($node->props['widget']) ? $node->props['widget'] : null;
            $instance = isset($wp_registered_widgets[$widget]) ? $wp_registered_widgets[$widget] : null;
            $filter = function ($widgets) use ($widget) {
                return in_array($widget, $widgets);
            };

            if (empty($instance['callback']) || !is_callable($instance['callback']) || !Arr::some(wp_get_sidebars_widgets(), $filter)) {
                return false;
            }

            call_user_func($instance['callback'], wp_parse_args($instance, [
                'name' => '',
                'id' => '',
                'description' => '',
                'class' => '',
                'before_widget' => '<content>',
                'after_widget' => '</content>',
                'before_title' => '<title>',
                'after_title' => '</title>',
                'widget_id' => $instance['id'],
                'widget_name' => $instance['name'],
                'yoo_element' => $node,
            ]), $instance['params'][0]);

            if ($widget = @array_pop($helper->widgets[$helper->sidebar])) {
                $node->widget = $widget;
                $node->props = array_merge(['showtitle' => null], $config("~theme.modules.{$widget->id}", []), $node->props);
            }

        },

    ],

    'updates' => [

        '1.20.0-beta.1.1' => function ($node) {

            if (isset($node->props['maxwidth_align'])) {
                $node->props['block_align'] = $node->props['maxwidth_align'];
                unset($node->props['maxwidth_align']);
            }

        },

        '1.20.0-beta.0.1' => function ($node) {

            if (@$node->props['title_style'] === 'heading-primary') {
                $node->props['title_style'] = 'heading-medium';
            }

            /**
             * @var Config $config
             */
            $config = app(Config::class);

            list($style) = explode(':', $config('~theme.style'));

            if (in_array($style, ['craft', 'district', 'jack-backer', 'tomsen-brody', 'vision', 'florence', 'max', 'nioh-studio', 'sonic', 'summit', 'trek'])) {

                if (@$node->props['title_style'] === 'h1' || (empty($node->props['title_style']) && @$node->props['title_element'] === 'h1')) {
                    $node->props['title_style'] = 'heading-small';
                }

            }

            if (in_array($style, ['florence', 'max', 'nioh-studio', 'sonic', 'summit', 'trek'])) {

                if (@$node->props['title_style'] === 'h2') {
                    $node->props['title_style'] = @$node->props['title_element'] === 'h1' ? '' : 'h1';
                } elseif (empty($node->props['title_style']) && @$node->props['title_element'] === 'h2') {
                    $node->props['title_style'] = 'h1';
                }

            }

            if (in_array($style, ['fuse', 'horizon', 'joline', 'juno', 'lilian', 'vibe', 'yard'])) {

                if (@$node->props['title_style'] === 'heading-medium') {
                    $node->props['title_style'] = 'heading-small';
                }

            }

            if (in_array($style, ['copper-hill'])) {

                if (@$node->props['title_style'] === 'heading-medium') {
                    $node->props['title_style'] = @$node->props['title_element'] === 'h1' ? '' : 'h1';
                } elseif (@$node->props['title_style'] === 'h1') {
                    $node->props['title_style'] = @$node->props['title_element'] === 'h2' ? '' : 'h2';
                } elseif (empty($node->props['title_style']) && @$node->props['title_element'] === 'h1') {
                    $node->props['title_style'] = 'h2';
                }

            }

            if (in_array($style, ['trek', 'fjord'])) {

                if (@$node->props['title_style'] === 'heading-medium') {
                    $node->props['title_style'] = 'heading-large';
                }

            }

        },

    ],

];
