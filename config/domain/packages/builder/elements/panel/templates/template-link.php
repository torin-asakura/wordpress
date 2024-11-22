<?php

$link = $props['link'] ? $this->el('a', [
    'href' => ['{link}'],
    'aria-label' => ['{link_aria_label}'],
    'target' => ['_blank {@link_target}'],
    'uk-scroll' => str_contains((string) $props['link'], '#'),
]) : null;

if ($link && $props['panel_link']) {

    $link_container->attr($link->attrs + [

        'class' => [
            'uk-link-toggle',
        ],

    ]);

    $props['title'] = $this->striptags($props['title']);
    $props['meta'] = $this->striptags($props['meta']);
    $props['content'] = $this->striptags($props['content']);

    if ($props['title'] && $props['title_hover_style'] != 'reset') {
        $props['title'] = $this->el('span', [
            'class' => [
                'uk-link-{title_hover_style: heading}',
                'uk-link {!title_hover_style}',
            ],
        ], $props['title'])->render($props);
    }

}

if ($link && $props['title'] && $props['title_link']) {

    $props['title'] = $link($props, [], $this->striptags($props['title'])); // title_hover_style is set on title

}

if ($link && $props['image'] && $props['image_link']) {

    $props['image'] = $link($props, [
        'class' => [
            'uk-flex {@panel_expand: image|both}',
            // Flex-1 only necessary if flex-column is parent which is the case for image not wrapped in card media or grid
            'uk-flex-1 {@panel_expand: image|both}' => !($props['panel_style'] && $props['image'] && $props['panel_image_no_padding'] && $props['image_align'] != 'between') && !in_array($props['image_align'], ['left', 'right']),
        ],
    ], $props['image']);

}

if ($link && $props['link_text']) {

    if ($props['panel_link']) {
        $link = $this->el('div');
    }

    $link->attr('class', [
        'el-link',
        'uk-{link_style: link-(muted|text)}',
        'uk-button uk-button-{!link_style: |link-muted|link-text} [uk-button-{link_size}] [uk-width-1-1 {@link_fullwidth}]',
        // Keep link style if panel link
        'uk-link {@link_style:} {@panel_link}',
        'uk-text-muted {@link_style: link-muted} {@panel_link}',
    ]);

}

return $link;
