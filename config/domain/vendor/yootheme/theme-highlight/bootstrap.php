<?php

namespace YOOtheme;

use YOOtheme\Theme\HighlightListener;

return [

    'actions' => [

        'onBeforeRender' => [
            HighlightListener::class => 'beforeRender',
        ],

    ],

    'filters' => [

        'the_content' => [
            HighlightListener::class => 'checkContent',
        ],

    ],

];
