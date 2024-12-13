<?php

// Resets
if ($props['icon'] && !($props['image'] || $props['video'])) { $props['panel_image_no_padding'] = ''; }
if ($props['panel_style'] || !($props['image'] || $props['video'])) {
    $props['image_box_decoration'] = '';
    $props['image_transition_border'] = '';
}
if ($props['panel_link']) {
    $props['title_link'] = '';
    $props['image_link'] = '';
}
if (!$props['height_expand'] && !(($props['image'] || $props['video']) && in_array($props['image_align'], ['left', 'right']))) {
    $props['panel_expand'] = '';
}
if (!($props['panel_style'] || (!$props['panel_style'] && ($props['image'] || $props['video']) && $props['image_align'] != 'between'))) {
    $props['panel_padding'] = '';
}

// New logic shortcuts
$props['flex_column_align'] = '';
$props['flex_column_align_fallback'] = '';
if ($props['panel_expand']) {
    $horizontal = ['left', 'center', 'right'];
    $vertical = ['top', 'middle', 'bottom'];
    $props['flex_column_align'] = str_replace($horizontal, $vertical, $props['text_align'] ?? '');
    $props['flex_column_align_fallback'] = str_replace($horizontal, $vertical, $props['text_align_fallback'] ?? '');

    // `uk-flex-top` prevents child inline elements from taking the whole width, needed for floating shadow + hover image
    if (!$props['panel_style']) {
        if ($props['text_align'] && $props['text_align_breakpoint'] && !$props['text_align_fallback']) {
            $props['flex_column_align_fallback'] = 'top';
        } elseif (!$props['text_align']) {
            $props['flex_column_align'] = 'top';
        }
    }
}

// Image
$props['image'] = $this->render("{$__dir}/template-media", compact('props'));

// Panel/Card/Tile
$el = $this->el($props['html_element'] ?: 'div', [

    'class' => [
        // Expand to column height
        'uk-flex-1 {@height_expand}',
        // Match link container height
        'uk-grid-item-match {@link} {@panel_link}',
    ],

]);

// Link Container
$link_container = $props['link'] && $props['panel_link'] ? $this->el('a') : null;

($link_container ?: $el)->attr([

    'class' => [
        'uk-panel [uk-{panel_style: tile-.*}] {@panel_style: |tile-.*}',
        'uk-card uk-{panel_style: card-.*} [uk-card-{!panel_padding: |default}]',
        'uk-tile-hover {@panel_style: tile-.*} {@panel_link} {@link}',
        'uk-card-hover {@!panel_style: |card-hover|tile-.*} {@panel_link} {@link}',
        'uk-transition-toggle {@image} {@panel_link}' => $props['image_transition'] || $props['image_transition_border'] || $props['hover_image'] || $props['hover_video'],
        'uk-flex uk-flex-column {@panel_expand}',
        // Image not wrapped in card media container or grid
        'uk-flex-{flex_column_align}[@{text_align_breakpoint} [uk-flex-{flex_column_align_fallback}]] {@panel_expand}' => !($props['panel_style'] && $props['image'] && $props['panel_image_no_padding'] && $props['image_align'] != 'between') && !in_array($props['image_align'], ['left', 'right']),
    ],

]);

// Padding
$content_container = '';
if ($props['panel_padding'] && $props['image'] && (!$props['panel_style'] || $props['panel_image_no_padding']) && $props['image_align'] != 'between') {

    $content_container = $this->el('div', [
        'class' => [
            'uk-flex-1 {@panel_expand: image|both}' => $props['image_vertical_align'] && $props['panel_style'] && $props['image'] && $props['panel_image_no_padding'] && in_array($props['image_align'], ['left', 'right']),
            'uk-flex-1 uk-flex uk-flex-column {@panel_expand: content|both}' => $props['panel_style'] && $props['image'] && $props['panel_image_no_padding'] && $props['image_align'] != 'between',
        ],
    ]);

}

($content_container ?: $link_container ?: $el)->attr('class', [
    'uk-padding[-{!panel_padding: default}] {@panel_style: |tile-.*} {@panel_padding}',
    'uk-card-body {@panel_style: card-.*} {@panel_padding}',
]);

// Image align
$grid = '';
$cell_image = '';
$cell_content = '';
if ($props['image'] && in_array($props['image_align'], ['left', 'right'])) {

    if($link_container) {
        $link_container->attr([
            'class' => [
                'uk-flex-stretch',
            ],
        ]);
    }

    $grid = $this->el('div', [

        'class' => [
            $props['panel_style'] && $props['panel_image_no_padding']
                ? 'uk-grid-collapse'
                : ($props['image_grid_column_gap'] == $props['image_grid_row_gap']
                    ? 'uk-grid-{image_grid_column_gap}'
                    : '[uk-grid-column-{image_grid_column_gap}] [uk-grid-row-{image_grid_row_gap}]'),
            'uk-flex-middle {@image_vertical_align} {@!panel_expand}',
            'uk-flex-1 {@panel_expand}',
            // Breakpoint
            'uk-flex-column {@panel_expand} uk-flex-row@{image_grid_breakpoint}',
        ],

        'uk-grid' => true,
    ]);

    $cell_image = $this->el('div', [

        'class' => [
            'uk-width-{image_grid_width}[@{image_grid_breakpoint}]',
            'uk-flex-last[@{image_grid_breakpoint}] {@image_align: right}',
            'uk-flex uk-flex-middle {@image_vertical_align} {@panel_expand: content}',
            'uk-flex {@panel_expand: image|both}',
            // Breakpoint
            'uk-flex-1 {@panel_expand: image|both} uk-flex-none@{image_grid_breakpoint}',
            'uk-flex-none {@panel_expand: content} [uk-flex-initial@{image_grid_breakpoint} {@image_grid_width: auto}] [uk-flex-1@{image_grid_breakpoint} {@!image_grid_width: auto}]',
            'uk-flex-{text_align}',
        ],

    ]);

    $cell_content = $this->el('div', [

        'class' => [
            'uk-width-expand', // Expand has to be set on cell because `.uk-flex-none` doesn't override grid CSS
            $props['image_vertical_align'] && $props['panel_expand'] == 'image'
                ? ($content_container
                    ? 'uk-flex uk-flex-middle'
                    : 'uk-flex uk-flex-column uk-flex-center')
                : '',
            'uk-flex uk-flex-column {@panel_expand: content|both}',
            // Breakpoint
            'uk-flex-none {@panel_expand: image|both} uk-flex-1@{image_grid_breakpoint}',
        ],

    ]);

}

($content_container ?: $cell_content ?: $link_container ?: $el)->attr('class', [
    'uk-margin-remove-first-child',
]);

// Link
$link = include "{$__dir}/template-link.php";

// Card Media (Needs to be after link)
if ($props['panel_style'] && $props['image'] && $props['panel_image_no_padding'] && $props['image_align'] != 'between') {

    $props['image'] = $this->el('div', [
        'class' => [
            'uk-card-media-{image_align} {@panel_style: card-.*}',
            'uk-flex {@panel_expand: image|both} [uk-flex-1 {@!image_align: left|right}]',
            'uk-flex-{text_align}[@{text_align_breakpoint} [uk-flex-{text_align_fallback}]] {@panel_expand}',
            // Breakpoint
            'uk-flex-1 [uk-flex-initial@{image_grid_breakpoint} {@image_align: left|right}] {@panel_expand: image|both}',
        ],

        'uk-toggle' => [
            'cls: uk-card-media-{image_align} uk-card-media-top; mode: media; media: @{image_grid_breakpoint} {@image_align: left|right} {@panel_style: card-.*}',
        ]
    ], $props['image'])->render($props);

}

?>

<?= $el($props, $attrs) ?>

    <?php if ($link_container) : ?>
    <?= $link_container($props) ?>
    <?php endif ?>

        <?php if ($grid) : ?>
        <?= $grid($props) ?>
        <?php endif ?>

            <?php if ($cell_image) : ?>
            <?= $cell_image($props) ?>
            <?php endif ?>

                <?php if (in_array($props['image_align'], ['left', 'right'])) : ?>
                <?= $props['image'] ?>
                <?php endif ?>

            <?php if ($cell_image) : ?>
            <?= $cell_image->end() ?>
            <?php endif ?>

            <?php if ($cell_content) : ?>
            <?= $cell_content($props) ?>
            <?php endif ?>

                <?php if ($props['image_align'] == 'top') : ?>
                <?= $props['image'] ?>
                <?php endif ?>

                <?php if ($content_container) : ?>
                <?= $content_container($props) ?>
                <?php endif ?>

                    <?= $this->render("{$__dir}/template-content", compact('props', 'link')) ?>

                <?php if ($content_container) : ?>
                <?= $content_container->end() ?>
                <?php endif ?>

                <?php if ($props['image_align'] == 'bottom') : ?>
                <?= $props['image'] ?>
                <?php endif ?>

            <?php if ($cell_content) : ?>
            <?= $cell_content->end() ?>
            <?php endif ?>

        <?php if ($grid) : ?>
        <?= $grid->end() ?>
        <?php endif ?>

    <?php if ($link_container) : ?>
    <?= $link_container->end() ?>
    <?php endif ?>

<?= $el->end() ?>
