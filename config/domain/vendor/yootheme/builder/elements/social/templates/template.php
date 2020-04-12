<?php

$links = array_filter(!empty($props['links']) ? (array) $props['links'] : []);

$el = $this->el('div');

// Grid
$grid = $this->el('div', [

    'class' => [
        'uk-child-width-auto',
        'uk-grid-{gap}',
        'uk-flex-{text_align}[@{text_align_breakpoint} [uk-flex-{text_align_fallback}]]',
    ],

    'uk-grid' => true,
]);

// Icon
$icon = $this->el('a', [

    'class' => [
        'el-link',
        'uk-icon-link {@!link_style}',
        'uk-icon-button {@link_style: button}',
        'uk-link-{link_style: muted|text|reset}',
    ],

    'target' => ['_blank {@link_target}'],

    'rel' => 'noreferrer'
]);

?>

<?= $el($props, $attrs) ?>
    <?= $grid($props) ?>

    <?php for ($i = 1; $i <= 5; $i++) : ?>
        <?php if ($props["link_{$i}"]) : ?>
        <div>
            <?= $icon($props, ['href' => $props["link_{$i}"], 'uk-icon' => [
                "icon: {$this->e($props["link_{$i}"], 'social')};",
                'ratio: {icon_ratio}; {@!link_style: button}',
            ]], '') ?>
        </div>
        <?php endif ?>
    <?php endfor ?>

    <?= $grid->end() ?>
<?= $el->end() ?>
