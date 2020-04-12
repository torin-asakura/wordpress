<?php

namespace YOOtheme\Theme\Wordpress;

class PostListener
{
    /**
     * Filters post galleries.
     *
     * @link https://developer.wordpress.org/reference/hooks/post_gallery/
     *
     * @param mixed $output
     * @param mixed $attr
     */
    public static function filterGallery($output = '', $attr)
    {
        ob_start();

        set_query_var('gallery_attr', $attr);
        get_template_part('templates/gallery');

        return ob_get_clean();
    }

    /**
     * Filters page links.
     *
     * @link https://developer.wordpress.org/reference/hooks/wp_link_pages/
     *
     * @param mixed $link
     */
    public static function filterPageLink($link)
    {
        return is_numeric($link) ? "<li class=\"uk-active\"><span>{$link}</span></li>" : "<li>{$link}</li>";
    }
}
