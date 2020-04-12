<?php

namespace YOOtheme\Builder;

use YOOtheme\Builder;
use YOOtheme\Event;
use YOOtheme\Metadata;
use YOOtheme\Storage;

class BuilderListener
{
    public static function initCustomizer(Metadata $metadata, Storage $storage, Builder $builder)
    {
        $library = $storage('library', []);
        $library = array_map('json_encode', $library);
        $library = array_map([$builder, 'load'], $library);

        $data = json_encode(Event::emit('builder.data|filter', [
            'library' => $library,
            'elements' => $builder->types,
        ]));

        $metadata->set('script:builder-data', "var \$builder = {$data};");
    }
}
