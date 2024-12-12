<?php

// New logic shortcuts
$element['is_video'] = (!$props['image'] && $props['video']) || (!$props['image'] && !$props['video'] && !$props['hover_image'] && $props['hover_video']);

if ($props['image']) {
    $src = $props['image'];
    $focal = $props['image_focal_point'];
    $media = include "{$__dir}/template-image.php";
} elseif ($props['video']) {
    $src = $props['video'];
    $media = include "{$__dir}/template-video.php";
} elseif ($props['hover_image']) {
    $src = $props['hover_image'];
    $focal = $props['hover_image_focal_point'];
    $media = include "{$__dir}/template-image.php";
} elseif ($props['hover_video']) {
    $src = $props['hover_video'];
    $media = include "{$__dir}/template-video.php";
}

// Min-height Placeholder
$placeholder = '';
if ($element['has_placeholder']) {

    $placeholder = clone $media;

    $placeholder->attr('class', ['uk-invisible']);

    if ($element['is_video']) {
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

    'uk-cover' => $element['has_cover'],

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

<?= $media($element, '') ?>

<?php if ($hover_media) : ?>
<?= $hover_media($element, '') ?>
<?php endif ?>
