<?php

namespace YOOtheme\Builder\Wordpress\Source;

use YOOtheme\Builder;
use YOOtheme\Path;

return [

    'routes' => [
        ['get', '/wordpress/posts', [SourceController::class, 'posts']],
    ],

    'events' => [

        'source.init' => [
            SourceListener::class => 'initSource',
        ],

        'customizer.init' => [
            SourceListener::class => ['initCustomizer', 10],
        ],

        'builder.template' => [
            TemplateListener::class => ['onTemplate', 5],
        ],

    ],

    'filters' => [

        'template_include' => [
            TemplateListener::class => ['onTemplateInclude', 20],
        ],

        'wp_link_query_args' => [
            SourceListener::class => 'addPostTypeFilter',
        ],

    ],

    'extend' => [

        Builder::class => function (Builder $builder) {
            $builder->addTypePath(Path::get('./elements/*/element.json'));
        },

    ],

];
