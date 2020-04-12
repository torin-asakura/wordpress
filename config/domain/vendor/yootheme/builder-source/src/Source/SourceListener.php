<?php

namespace YOOtheme\Builder\Source;

use YOOtheme\Builder\Source;
use YOOtheme\Config;

class SourceListener
{
    public static function initCustomizer(Config $config, Source $source)
    {
        $result = $source->queryIntrospection()->toArray();
        $config->add('customizer.schema', isset($result['data']) ? $result['data']['__schema'] : $result);
    }
}
