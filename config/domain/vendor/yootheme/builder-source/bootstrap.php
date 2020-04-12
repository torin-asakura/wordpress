<?php

namespace YOOtheme\Builder;

use YOOtheme\Builder;
use YOOtheme\Builder\Source\SourceListener;
use YOOtheme\Builder\Source\SourceTransform;
use YOOtheme\Event;

return [

    'events' => [

        'customizer.init' => [
            SourceListener::class => 'initCustomizer',
        ],

    ],

    'extend' => [

        Source::class => function (Source $source) {
            Event::emit('source.init', $source);
        },

        // Before Placeholder Transform, after Normalize and Id Transform
        Builder::class => function (Builder $builder, $app) {
            $builder->addTransform('prerender', $app(SourceTransform::class), 2);
        },

    ],

    'services' => [

        Source::class => '',

    ],

];
