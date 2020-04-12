<?php

namespace YOOtheme\Builder\Templates;

use YOOtheme\Config;

class CustomizerListener
{
    public static function initCustomizer(Config $config)
    {
        $templates = $config('customizer.templates', []);
        $config->add(
            'customizer.sections.builder-templates.fieldset.default.fields.type.options',
            array_combine(array_column($templates, 'label'), array_keys($templates))
        );
    }
}
