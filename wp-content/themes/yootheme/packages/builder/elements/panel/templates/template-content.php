<?php

// Title
$title = $this->el($props['title_element'], [

    'class' => [
        'el-title',
        'uk-{title_style}',
        'uk-card-title {@panel_style} {@!title_style}',
        'uk-heading-{title_decoration}',
        'uk-font-{title_font_family}',
        'uk-text-{title_color} {@!title_color: background}',
        'uk-link-{title_hover_style} {@title_link}', // Set here to style links which already come with dynamic content (WP taxonomy links)
        'uk-margin[-{title_margin}]-top {@!title_margin: remove}',
        'uk-margin-remove-top {@title_margin: remove}',
        'uk-margin-remove-bottom',
        'uk-flex-1 {@panel_expand: content|both}' => !$props['content'] && (!$props['meta'] || $props['meta_align'] == 'above-title') && (!$props['image'] || $props['image_align'] != 'between'),
    ],

]);

// Meta
$meta = $this->el($props['meta_element'], [

    'class' => [
        'el-meta',
        'uk-{meta_style}',
        'uk-text-{meta_color}',
        'uk-margin[-{meta_margin}]-top {@!meta_margin: remove}',
        'uk-margin-remove-bottom [uk-margin-{meta_margin: remove}-top]' => !in_array($props['meta_style'], ['', 'text-meta', 'text-lead', 'text-small', 'text-large']) || $props['meta_element'] != 'div',
        'uk-flex-1 {@panel_expand: content|both}' => $props['meta_align'] == 'below-content' || (!$props['content'] && ($props['meta_align'] == 'above-content' || ($props['meta_align'] == 'below-title' && (!$props['image'] || $props['image_align'] != 'between')))),
    ],
]);

// Content
$content = $this->el('div', [

    'class' => [
        'el-content uk-panel',
        'uk-{content_style}',
        '[uk-text-left{@content_align}]',
        'uk-dropcap {@content_dropcap}',
        'uk-column-{content_column}[@{content_column_breakpoint}]',
        'uk-column-divider {@content_column} {@content_column_divider}',
        'uk-margin[-{content_margin}]-top {@!content_margin: remove}',
        'uk-margin-remove-bottom [uk-margin-{content_margin: remove}-top]' => !in_array($props['content_style'], ['', 'text-meta', 'text-lead', 'text-small', 'text-large']),
        'uk-flex-1 {@panel_expand: content|both}' => !($props['meta'] && $props['meta_align'] == 'below-content'),
    ],

]);

// Link
$link_container = $this->el('div', [

    'class' => [
        'uk-margin[-{link_margin}]-top {@!link_margin: remove}',
        'uk-width-1-1 {@link_fullwidth} {@panel_expand}',
    ],

]);

// Title Grid
$grid = $this->el('div', [

    'class' => [
        'uk-child-width-expand',
        $props['title_grid_column_gap'] == $props['title_grid_row_gap'] ? 'uk-grid-{title_grid_column_gap}' : '[uk-grid-column-{title_grid_column_gap}] [uk-grid-row-{title_grid_row_gap}]',
        'uk-margin[-{title_margin}]-top {@!title_margin: remove} {@image_align: top}' => !$props['meta'] || $props['meta_align'] != 'above-title',
        'uk-margin[-{meta_margin}]-top {@!meta_margin: remove} {@image_align: top} {@meta} {@meta_align: above-title}',
        'uk-flex-1 {@panel_expand: content|both}',
    ],

    'uk-grid' => true,
]);

$cell_title = $this->el('div', [

    'class' => [
        'uk-width-{!title_grid_width: expand}[@{title_grid_breakpoint}]',
        'uk-margin-remove-first-child',
    ],

]);

$cell_content = $this->el('div', [

    'class' => [
        'uk-width-auto[@{title_grid_breakpoint}] {@title_grid_width: expand}',
        'uk-margin-remove-first-child',
        'uk-flex uk-flex-column {@panel_expand: content|both}',
    ],

]);

?>

<?php if ($props['title'] && $props['title_align'] == 'left') : ?>
<?= $grid($props) ?>
    <?= $cell_title($props) ?>
<?php endif ?>

        <?php if ($props['meta'] && $props['meta_align'] == 'above-title') : ?>
        <?= $meta($props, $props['meta']) ?>
        <?php endif ?>

        <?php if ($props['title']) : ?>
        <?= $title($props) ?>
            <?php if ($props['title_color'] == 'background') : ?>
            <span class="uk-text-background"><?= $props['title'] ?></span>
            <?php elseif ($props['title_decoration'] == 'line') : ?>
            <span><?= $props['title'] ?></span>
            <?php else : ?>
            <?= $props['title'] ?>
            <?php endif ?>
        <?= $title->end() ?>
        <?php endif ?>

        <?php if ($props['meta'] && $props['meta_align'] == 'below-title') : ?>
        <?= $meta($props, $props['meta']) ?>
        <?php endif ?>

    <?php if ($props['title'] && $props['title_align'] == 'left') : ?>
    <?= $cell_title->end() ?>
    <?= $cell_content($props) ?>
    <?php endif ?>

        <?php if ($props['image_align'] == 'between') : ?>
        <?= $props['image'] ?>
        <?php endif ?>

        <?php if ($props['meta'] && $props['meta_align'] == 'above-content') : ?>
        <?= $meta($props, $props['meta']) ?>
        <?php endif ?>

        <?php if ($props['content']) : ?>
        <?= $content($props, $props['content']) ?>
        <?php endif ?>

        <?php if ($props['meta'] && $props['meta_align'] == 'below-content') : ?>
        <?= $meta($props, $props['meta']) ?>
        <?php endif ?>

        <?php if ($props['link'] && $props['link_text']) : ?>
        <?= $link_container($props, $link($props, $props['link_text'])) ?>
        <?php endif ?>

<?php if ($props['title'] && $props['title_align'] == 'left') : ?>
    <?= $cell_content->end() ?>
<?= $grid->end() ?>
<?php endif ?>
