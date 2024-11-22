<?php

if ($iframe = $this->iframeVideo($src)) {

    $video = $this->el('iframe', [

        'class' => [
            'uk-disabled',
        ],

        'src' => $iframe,
        'allow' => 'autoplay',
        'uk-responsive' => !$element['image_min_height'],
        'loading' => ['lazy {@image_loading}'],
        'title' => $props['video_title'],

    ]);

} else {

    $video = $this->el('video', [

        'src' => $src,
        'controls' => false,
        'loop' => true,
        'autoplay' => true,
        'muted' => true,
        'playsinline' => true,
        'preload' => ['none {@image_loading}'],

    ]);

}

$video->attr([

    'width' => $element['image_width'],
    'height' => $element['image_height'],

    'uk-video' => [
        'automute: true' => !$element['image_min_height'],
    ],

]);

return $video;
