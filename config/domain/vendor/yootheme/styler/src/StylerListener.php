<?php

namespace YOOtheme\Theme;

use YOOtheme\Config;
use YOOtheme\Event;
use YOOtheme\File;
use YOOtheme\Metadata;
use YOOtheme\Path;
use YOOtheme\Storage;
use YOOtheme\Url;

class StylerListener
{
    public static function initCustomizer(Config $config, Metadata $metadata, Storage $storage, Styler $styler)
    {
        // check if theme css needs to be updated
        $style = File::get("~theme/css/theme.{$config('theme.id')}.css");
        $update = !$style || filemtime(__FILE__) >= filemtime($style);

        $styles = array_map(function ($theme) {
            unset($theme['file']);
            return $theme;
        }, $styler->getThemes());

        $config->add('customizer.sections.styler', [
            'route' => Url::route('theme/styles'),
            'worker' => Url::to(Path::get('../app/worker.min.js'), ['ver' => $config('theme.version')]),
            'styles' => $styles,
            'update' => $update,
        ]);

        $data = json_encode(Event::emit('styler.data|filter', [
            'library' => (array) $storage('styler.library'),
        ]));

        $metadata->set('script:styler-data', "var \$styler = {$data};");
    }
}
