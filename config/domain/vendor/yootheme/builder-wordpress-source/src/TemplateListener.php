<?php

namespace YOOtheme\Builder\Wordpress\Source;

use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Event;
use YOOtheme\View;

class TemplateListener
{
    public static function onTemplate(Config $config, $template)
    {
        if ($temp = get_theme_mod('template')) {
            $config->set("builder.templates.{$temp['id']}.layout", $temp['layout']);
        }

        return $template;
    }

    public static function onTemplateInclude(Config $config, Builder $builder, View $view, $tmpl)
    {
        global $post;

        $type = null;
        $filter = [];

        if ($config('customizer.page')) {
            $config->set('customizer.view', "single-{$post->post_type}");
        }

        if ($view['sections']->exists('builder')) {
            return $tmpl;
        }

        if (is_singular()) {

            if (post_password_required($post)) {
                return $tmpl;
            }

            $type = "single-{$post->post_type}";
            $filter = ['terms' => array_column(wp_get_object_terms($post->ID, get_object_taxonomies($post)), 'term_id')];

        } elseif ($tmpl === get_index_template()) {

            $type = 'archive-post';

        } elseif (is_post_type_archive()) {

            $obj = get_queried_object();
            $type = "archive-{$obj->name}";

        } elseif (is_tax() || is_category() || is_tag()) {

            $obj = get_queried_object();
            $type = "taxonomy-{$obj->taxonomy}";
            $filter = ['terms' => $obj->term_id];
        }

        $config->set('customizer.view', $type);

        $template = Event::emit('builder.template|filter', [], $type, $filter);

        if (!empty($template['layout'])) {
            $view['sections']->set(
                'builder',
                $builder->render(json_encode($template['layout']), [
                    'prefix' => "template-{$template['id']}",
                    'template' => $template,
                ])
            );
        }

        return $tmpl;
    }
}
