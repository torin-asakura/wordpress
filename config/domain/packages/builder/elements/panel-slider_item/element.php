<?php

namespace YOOtheme;

return [
    'transforms' => [
        'render' => function ($node, $params) {

            // TODO Remove later
            $params['parent']->props['show_video'] = '';
            $params['parent']->props['show_hover_image'] = '';
            $params['parent']->props['show_hover_video'] = '';
            $node->props['video'] = '';
            $node->props['hover_image'] = '';
            $node->props['hover_video'] = '';

            // Display
            foreach (['title', 'meta', 'content', 'link', 'image', 'video', 'hover_image', 'hover_image'] as $key) {
                if (!$params['parent']->props["show_{$key}"]) {
                    $node->props[$key] = '';
                    if ($key === 'image') {
                        $node->props['icon'] = '';
                    }
                }
            }

            // Don't render element if content fields are empty
            return $node->props['title'] != '' ||
                $node->props['meta'] != '' ||
                $node->props['content'] != '' ||
                $node->props['image'] ||
                $node->props['video'] ||
                $node->props['icon'];
        },
    ],
];
