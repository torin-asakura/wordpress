<?php

// New logic shortcuts
$props['is_video'] = (!$props['image'] && $props['video']) || (!$props['image'] && !$props['video'] && !$props['hover_image'] && $props['hover_video']);

if ($props['image']) {
    $src = $props['image'];
    $media = include "{$__dir}/template-image.php";
} elseif ($props['video']) {
    $src = $props['video'];
    $media = include "{$__dir}/template-video.php";
} elseif ($props['hover_image']) {
    $src = $props['hover_image'];
    $media = include "{$__dir}/template-image.php";
} elseif ($props['hover_video']) {
    $src = $props['hover_video'];
    $media = include "{$__dir}/template-video.php";
}

// Min-height Placeholder
$placeholder = '';
if ($props['image_min_height']) {

    $placeholder = clone $media;

    $placeholder->attr('class', ['uk-invisible']);

    if ($props['is_video']) {
        $placeholder->attr('autoplay', false);
    }

}

// Media
$media->attr([

    'class' => [
        'el-image',
        'uk-blend-{0}' => $props['media_blend_mode'],
        'uk-transition-{image_transition}',
        'uk-transition-opaque' => $props['image'] || $props['video'],
        'uk-transition-fade {@!image_transition}' => ($props['hover_image'] || $props['hover_video']) && !($props['image'] || $props['video']),
    ],

    'uk-cover' => (bool) $props['image_min_height'],

]);

// Hover Media
$hover_media = '';
if (($props['image'] || $props['video']) && ($props['hover_image'] || $props['hover_video'])) {

    if ($props['hover_image']) {

        $src = $props['hover_image'];
        $hover_media = include "{$__dir}/template-image.php";

        $hover_media->attr([
            'alt' => true,
            'loading' => false,
        ]);


    } elseif ($props['hover_video']) {

        $src = $props['hover_video'];
        $hover_media = include "{$__dir}/template-video.php";

    }

    $hover_media->attr([

        'class' => [
            'el-hover-image',
            'uk-transition-{image_transition}',
            'uk-transition-fade {@!image_transition}',
        ],

        'uk-cover' => true,

    ]);

}

?>

<?php if ($placeholder) : ?>
<?= $placeholder($props, '') ?>
<?php endif ?>

<?= $media($props, '') ?>

<?php if ($hover_media) : ?>
<?= $hover_media($props, '') ?>
<?php endif ?>
