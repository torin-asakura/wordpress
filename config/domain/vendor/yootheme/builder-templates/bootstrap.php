<?php

namespace YOOtheme;

use YOOtheme\Builder\Templates\CustomizerListener;
use YOOtheme\Builder\Templates\TemplateController;
use YOOtheme\Builder\Templates\TemplateListener;

return [

    'routes' => [

        ['post', '/builder/template', [TemplateController::class, 'saveTemplate']],
        ['delete', '/builder/template', [TemplateController::class, 'deleteTemplate']],
        ['post', '/builder/template/reorder', [TemplateController::class, 'reorderTemplates']],

    ],

    'events' => [

        'builder.data' => [
            TemplateListener::class => 'onBuilderData',
        ],

        'builder.template' => [
            TemplateListener::class => [['onEarlyBuilderTemplate', 50], ['onBuilderTemplate'], ['onLateBuilderTemplate', -50]],
        ],

        'customizer.init' => [
            CustomizerListener::class => 'initCustomizer',
        ],

    ],

];
