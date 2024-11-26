<?php

// Resets
if ($props['panel_link']) {
    $props['title_link'] = '';
    $props['image_link'] = '';
}
if ($props['slider_parallax']) {
    $props['slidenav'] = '';
}

// Override default settings
if (!$props['slider_width']) {
    $props['panel_match'] = true;
    $props['panel_expand'] = 'image';
}

$el = $this->el('div', [

    'class' => [
        'uk-slider-container {@!slidenav: outside}',
        'uk-slider-container-offset {@panel_style} {@panel_card_offset} {@!slidenav: outside}',
    ],

    'uk-slider' => $this->expr([
        'sets: {slider_sets}; {@!slider_parallax}',
        'center: {slider_center};',
        'finite: {slider_finite};',
        'velocity: {slider_velocity}; {@!slider_parallax}',
        'autoplay: {slider_autoplay}; [pauseOnHover: false; {@!slider_autoplay_pause}] [autoplayInterval: {slider_autoplay_interval}000;] {@!slider_parallax}',
        // Parallax
        'parallax: true; {@slider_parallax}',
        'parallax-easing: {slider_parallax_easing}; {@slider_parallax}',
        'parallax-target: {slider_parallax_target}; {@slider_parallax}',
        'parallax-start: {slider_parallax_start}; {@slider_parallax}',
        'parallax-end: {slider_parallax_end}; {@slider_parallax}',
    ], $props) ?: true,

]);

// Slider Items
$slider_items = $this->el('div', [

    'class' => [
        'uk-slider-items',
        'uk-grid [uk-grid-{!slider_gap: default}] {@slider_gap}',
        'uk-grid-divider {@slider_gap} {@slider_divider}',
        'uk-grid-match {@panel_match}',
    ],

]);

$slider_item = $this->el('div', [

    'class' => [
        'uk-width-{slider_width_default} {@slider_width}',
        'uk-width-{slider_width_small}@s {@slider_width}',
        'uk-width-{slider_width_medium}@m {@slider_width}',
        'uk-width-{slider_width_large}@l {@slider_width}',
        'uk-width-{slider_width_xlarge}@xl {@slider_width}',
    ],

]);

// Container
$container = $this->el('div', [

    'class' => [
        'uk-position-relative',
        'uk-visible-toggle {@slidenav} {@slidenav_hover}',
    ],

    'tabindex' => ['-1 {@slidenav} {@slidenav_hover}'],

]);

?>

<?= $el($props, $attrs) ?>

    <?= $container($props) ?>

        <?php if ($props['slidenav'] == 'outside') : ?>
        <div class="uk-slider-container<?= $props['panel_style'] && $props['panel_card_offset'] ? ' uk-slider-container-offset' : '' ?>">
        <?php endif ?>

            <?= $slider_items($props) ?>
                <?php foreach ($children as $child) : ?>
                <?= $slider_item($props, $builder->render($child, ['element' => $props])) ?>
                <?php endforeach ?>
            <?= $slider_items->end() ?>

        <?php if ($props['slidenav'] == 'outside') : ?>
        </div>
        <?php endif ?>

        <?php if ($props['slidenav']) : ?>
        <?= $this->render("{$__dir}/template-slidenav") ?>
        <?php endif ?>

    <?= $container->end() ?>

    <?php if ($props['nav']): ?>
    <?= $this->render("{$__dir}/template-nav") ?>
    <?php endif ?>

<?= $el->end() ?>
