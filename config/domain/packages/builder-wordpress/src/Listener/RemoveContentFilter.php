<?php

namespace YOOtheme\Builder\Wordpress\Listener;

use YOOtheme\Builder\Wordpress\PostHelper;

class RemoveContentFilter
{
    public function handle($content)
    {
        // Prevent content filters from corrupting builder JSON in post_content on save.
        if (PostHelper::matchContent($content)) {
            if (function_exists('kses_remove_filters')) {
                kses_remove_filters();
            }

            if (function_exists('wp_remove_targeted_link_rel_filters')) {
                wp_remove_targeted_link_rel_filters();
            }
        }

        return $content;
    }
}
