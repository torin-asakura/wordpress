<?php

// => gallery
$nav = $this->el('ul', [

    'class' => [
        'el-nav',
        'uk-margin[-{filter_margin}] {@filter_position: top}',
    ],

    'uk-tab' => [
        'media: @{filter_grid_breakpoint}; {@filter_position: left|right} {@filter_style: tab}',
    ],

]);

$nav_horizontal = [
    'uk-subnav {@filter_style: subnav-.*}',
    'uk-{filter_style}',
    'uk-flex-{filter_align: right|center}',
    'uk-child-width-expand {@filter_align: justify}',
];

$nav_vertical = [
    'uk-nav uk-nav-{0} [uk-text-left {@text_align}] {@filter_style: subnav.*}' => $props['filter_style_primary'] ? 'primary' : 'default',
    'uk-tab-{filter_position} {@filter_style: tab}',
];

$nav_attrs = $props['filter_position'] === 'top'
    ? ['class' => $nav_horizontal]
    : ['class' => $nav_vertical,
        'uk-toggle' => $props['filter_style'] != 'tab'
            ? [
                "cls: {$this->expr(array_merge($nav_vertical, $nav_horizontal), $props)};",
                'mode: media;',
                'media: @{filter_grid_breakpoint};',
            ] : false,
    ];

?>

<?= $nav($props, $nav_attrs) ?>

    <?php if ($props['filter_all']) : ?>
    <li class="uk-active" uk-filter-control><a href><?= $this->trans($props['filter_all_label'] ?: 'All') ?></a></li>
    <?php endif ?>

    <?php $first = key($tags); ?>
    <?php foreach ($tags as $tag => $name) : ?>
    <li <?= (string) $tag === $first && !$props['filter_all'] ? 'class="uk-active" ' : '' ?>uk-filter-control="[data-tag~='<?= $tag ?>']">
        <a href="#"><?= ucwords($name) ?></a>
    </li>
    <?php endforeach ?>

</ul>
