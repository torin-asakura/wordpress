<?php

// Resets
if ($props['slideshow_height'] == 'viewport') {
    if ($props['slideshow_height_viewport'] > 100) {
        $props['slideshow_height_offset_top'] = false;
    } elseif (!$props['slideshow_height_viewport']) {
        $props['slideshow_height_viewport'] = 100;
    }
}
if ($props['slideshow_parallax']) {
    $props['slidenav'] = '';
}

$el = $this->el('div', [

    'uk-slideshow' => $this->expr([
        'ratio: {0};' => $props['slideshow_height'] ? 'false' : $props['slideshow_ratio'],
        'minHeight: {slideshow_min_height}; {@!slideshow_height}',
        'maxHeight: {slideshow_max_height}; {@!slideshow_height}',
        'animation: {slideshow_animation};',
        'velocity: {slideshow_velocity}; {@!slideshow_parallax}',
        'autoplay: {slideshow_autoplay}; [pauseOnHover: false; {!slideshow_autoplay_pause}; ] [autoplayInterval: {slideshow_autoplay_interval}000;] {@!slideshow_parallax}',
        // Parallax
        'parallax: true; {@slideshow_parallax}',
        'parallax-easing: {slideshow_parallax_easing}; {@slideshow_parallax}',
        'parallax-target: {slideshow_parallax_target}; {@slideshow_parallax}',
        'parallax-start: {slideshow_parallax_start}; {@slideshow_parallax}',
        'parallax-end: {slideshow_parallax_end}; {@slideshow_parallax}',
    ], $props) ?: true,

]);

// Container
$container = $this->el('div', [

    'class' => [
        'uk-position-relative',
        'uk-visible-toggle {@slidenav} {@slidenav_hover}',
    ],

    'tabindex' => ['-1 {@slidenav} {@slidenav_hover}'],

]);

// Box decoration
$decoration = $this->el('div', [

    'class' => [
        'uk-box-shadow-bottom uk-display-block {@slideshow_box_decoration: shadow}',
        'tm-mask-default {@slideshow_box_decoration: mask}',
        'tm-box-decoration-{slideshow_box_decoration: default|primary|secondary}',
        'tm-box-decoration-inverse {@slideshow_box_decoration_inverse} {@slideshow_box_decoration: default|primary|secondary}',
    ],

]);

// Items
$items = $this->el('div', [

    'class' => [
        'uk-slideshow-items',
        'uk-box-shadow-{slideshow_box_shadow}',
    ],

    'style' => [
        'min-height: max({0}px, {slideshow_height_viewport}vh); {@slideshow_height: viewport} {@!slideshow_height_offset_top}' => [$props['slideshow_min_height'] ?: '0'],
    ],

    'uk-height-viewport' => $props['slideshow_height'] && !($props['slideshow_height'] == 'viewport' && !$props['slideshow_height_offset_top']) ? [
        'offset-top: true; {@slideshow_height_offset_top}',
        'minHeight: {slideshow_min_height};',
        'offset-bottom: {0}; {@slideshow_height: viewport}' => $props['slideshow_height_viewport'] && $props['slideshow_height_viewport'] < 100 ? 100 - (int) $props['slideshow_height_viewport'] : false,
        'offset-bottom: !:is(.uk-section-default,.uk-section-muted,.uk-section-primary,.uk-section-secondary) +; {@slideshow_height: section}',
    ] : false,

]);

?>

<?= $el($props, $attrs) ?>

    <?= $container($props) ?>

        <?php if ($props['slideshow_box_decoration']) : ?>
        <?= $decoration($props) ?>
        <?php endif ?>

            <?= $items($props) ?>
                <?php foreach ($children as $i => $child) :

                    $item = $this->el('div', [

                        'class' => [
                            'el-item',
                            'uk-inverse-{0}' => $child->props['text_color'] ?: $props['text_color'],
                        ],

                        'style' => [
                            'background-color: {0};' => $child->props['media_background'] ?: false,
                        ],

                    ]);

                    ?>

                    <?= $item($props, $builder->render($child, ['i' => $i, 'element' => $props])) ?>

                <?php endforeach ?>
            <?= $items->end() ?>

        <?php if ($props['slideshow_box_decoration']) : ?>
        <?= $decoration->end() ?>
        <?php endif ?>

        <?php if ($props['slidenav']) : ?>
        <?= $this->render("{$__dir}/template-slidenav") ?>
        <?php endif ?>

        <?php if ($props['nav'] && !$props['nav_below']) : ?>
        <?= $this->render("{$__dir}/template-nav") ?>
        <?php endif ?>

    <?= $container->end() ?>

    <?php if ($props['nav'] && $props['nav_below']) : ?>
    <?= $this->render("{$__dir}/template-nav") ?>
    <?php endif ?>

<?= $el->end() ?>
