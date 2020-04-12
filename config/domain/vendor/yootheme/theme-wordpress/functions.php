<?php

namespace YOOtheme;

/**
 * Helper functions.
 *
 * @param array $args
 */
function get_view(...$args) {
    return app(View::class)->render(...$args);
}

function get_attrs(...$args) {
    return app(View::class)->attrs(...$args);
}

function get_section(...$args) {
    return app(View::class)->section(...$args);
}

function get_margin(...$args) {
    return app(View::class)->margin(...$args);
}

function get_builder($node, $params = []) {

    // support old builder arguments
    if (!is_string($node)) {
        $node = json_encode($node);
    }

    if (is_string($params)) {
        $params = ['prefix' => $params];
    }

    return app(Builder::class)->render($node, $params);
}

function get_readmore() {

    $post = get_post();
    $text = get_extended($post->post_content);
    $content_length = app(Config::class)->get('~theme.blog.content_length');

    return !empty($text['extended']) || !empty($content_length) ? $text['more_text'] ?: __('Continue reading', 'yootheme') : false;
}

function get_post_date($post = null) {
    return '<time datetime="' . esc_attr(get_the_date('c', $post)) . '">' . esc_html(get_the_date('', $post)) . '</time>';
}

function get_post_author($post = null) {

    if ($post) {
        $authordata = get_userdata($post->post_author);
    } else {
        global $authordata;
    }

    return '<a href="' . esc_url(get_author_posts_url($authordata->ID)) . '">' . esc_html(apply_filters('the_author', $authordata->display_name)) . '</a>';
}
