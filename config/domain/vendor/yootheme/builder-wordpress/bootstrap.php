<?php

namespace YOOtheme;

use YOOtheme\Builder\Wordpress\BuilderController;
use YOOtheme\Builder\Wordpress\ContentListener;

return [

    'routes' => [
        ['post', '/page', [ContentListener::class, 'savePage']],
        ['post', '/builder/image', [BuilderController::class, 'loadImage']],
    ],

    'filters' => [

        'pre_post_content' => [
            ContentListener::class => 'onPrePostContent',
        ],

        'template_include' => [
            ContentListener::class => [['onTemplateInclude'], ['onLateTemplateInclude', 50]],
        ],

    ],

    'extend' => [

        View::class => function (View $view) {

            $loader = function ($name, $parameters, callable $next) {

                // Remove wpautop filter
                $priority = has_filter('the_content', 'wpautop');

                if ($priority !== false) {
                    remove_filter('the_content', 'wpautop');
                }

                $content = $next($name, $parameters);
                $content = apply_filters('the_content', $content);
                $content = str_replace(']]>', ']]&gt;', $content);

                if ($priority !== false) {
                    add_filter('the_content', 'wpautop', $priority);
                }

                return $content;
            };

            $view->addLoader($loader, '*/builder/elements/layout/templates/template.php');
        },

        Builder::class => function (Builder $builder, $app) {

            $builder->addTypePath(Path::get('./elements/*/element.json'));

            if ($childDir = $app->config->get('theme.childDir')) {
                $builder->addTypePath("{$childDir}/builder/*/element.json");
            }

        },

    ],

];
