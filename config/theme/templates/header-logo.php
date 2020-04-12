<?php

// Config
$config->addAlias('~logo', '~theme.logo');

// Link
$attrs_link = [];
$attrs_link['href'] = $config('~theme.site_url');
$attrs_link['class'][] = isset($class) ? $class : '';
$attrs_link['class'][] = 'uk-logo';

// Logo
$logo_el = __($config('~logo.text', 'yootheme'));
$logo_img = function ($image, $width, $height, array $attrs = []) use ($config) {

    $attrs['alt'] = __($config('~logo.text', 'yootheme'));
    $attrs['uk-gif'] = $this->isImage($image) === 'gif';

    if ($this->isImage($image) === 'svg') {
        $logo = $this->image($image, array_merge($attrs, compact('width', 'height')));
    } else {
        $logo = $this->image([$image, 'thumbnail' => [$width, $height], 'srcset' => true], $attrs);
    }

    return $logo;
};

// Logo Image
if ($config('~logo.image')) {

    $logo_el = $logo_img($config('~logo.image'), $config('~logo.image_width'), $config('~logo.image_height'));

    // Inverse
    if ($config('~logo.image_inverse')) {
        $logo_el .= $logo_img($config('~logo.image_inverse'), $config('~logo.image_width'), $config('~logo.image_height'), ['class' => ['uk-logo-inverse']]);
    }
}
?>

<a<?= $this->attrs($attrs_link) ?>>
    <?= $logo_el ?>
</a>
