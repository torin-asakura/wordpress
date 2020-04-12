<?php

namespace YOOtheme\Builder\Templates;

use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Storage;

class TemplateListener
{
    public static function onBuilderData(Builder $builder, Storage $storage, $data)
    {
        $templates = $storage('templates', []);

        foreach ($templates as &$template) {
            if (isset($template['layout'])) {
                $template['layout'] = $builder->load(json_encode($template['layout']));
            }
        }

        return $data + compact('templates');
    }

    public static function onEarlyBuilderTemplate(Config $config, Storage $storage, $template)
    {
        $config->set('builder.templates', $storage('templates', []));

        return $template;
    }

    public static function onBuilderTemplate(Config $config, $template, $type, $filter = [])
    {
        foreach ($config('builder.templates', []) as $id => $tmpl) {

            if (!empty($tmpl['status']) && $tmpl['status'] === 'disabled' && !$config('app.isCustomizer')) {
                continue;
            }

            if (empty($tmpl['type']) || $tmpl['type'] !== $type) {
                continue;
            }

            foreach($filter as $key => $value) {
                if (!empty($tmpl['query'][$key]) && !self::matchProperty($value, $tmpl['query'][$key])) {
                    continue 2;
                }
            }

            return $tmpl + compact('id');
        }

        return $template;
    }

    public static function onLateBuilderTemplate(Config $config, $template)
    {
        if ($config->get('app.isCustomizer')) {
            $config->set('customizer.template', $template ? $template['id'] : false);
        }

        return $template;
    }

    protected static function matchProperty($value, $prop)
    {
        return is_array($prop)
            ? (
                is_array($value)
                    ? (bool) array_intersect($value, $prop)
                    : in_array($value, $prop)
            )
            : $value === $prop;
    }
}
