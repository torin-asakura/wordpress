<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Config;
use YOOtheme\Event;
use YOOtheme\View;

class ThemeListener
{
    /**
     * Fires before the header template file is loaded.
     *
     * @link https://developer.wordpress.org/reference/hooks/get_header/
     *
     * @param Config $config
     * @param View   $view
     */
    public static function onHeader(Config $config, View $view)
    {
        $config->set('~theme.direction', is_rtl() ? 'rtl' : 'lrt');
        $config->set('~theme.site_url', rtrim(get_bloginfo('url'), '/'));
        $config->set('~theme.page_class', ''); // TODO: implement page class builder

        if ($config('~theme.disable_wpautop')) {
            remove_filter('the_content', 'wpautop');
            remove_filter('the_excerpt', 'wpautop');
        }

        if ($config('~theme.disable_emojis')) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        }

        $view['sections']->add('breadcrumbs', function () use ($view) {
            return $view->render('~theme/templates/breadcrumbs', ['items' => Breadcrumbs::getItems()]);
        });

        Event::emit('theme.head');
    }

    /**
     * Filters list of allowed mime types and file extensions.
     *
     * @link https://developer.wordpress.org/reference/hooks/upload_mimes/
     *
     * @param mixed $mimes
     */
    public static function addSvg($mimes)
    {
        $mimes['svg|svgz'] = 'image/svg+xml';

        return $mimes;
    }

    /**
     * Filters the “real” file type of the given file..
     *
     * @link https://developer.wordpress.org/reference/hooks/wp_check_filetype_and_ext/
     *
     * @param mixed $data
     * @param mixed $file
     * @param mixed $filename
     * @param mixed $mimes
     */
    public static function addSvgType($data, $file, $filename, $mimes)
    {
        if (empty($data['type']) && substr($filename, -4) === '.svg') {
            $data['ext'] = 'svg';
            $data['type'] = 'image/svg+xml';
        }

        return $data;
    }

    /**
     * Prints scripts or data in the head tag on the front end.
     *
     * @link https://developer.wordpress.org/reference/hooks/wp_head/
     *
     * @param Config $config
     */
    public static function addScript(Config $config)
    {
        if ($custom = $config('~theme.custom_js', '')) {

            if (stripos(trim($custom), '<script') === 0) {
                echo $custom;
            } else {
                echo "<script>try { {$custom}\n } catch (e) { console.error('Custom Theme JS Code: ', e); }</script>";
            }

        }
    }

    /**
     * Fires when scripts and styles are enqueued.
     *
     * @link https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/
     *
     * @param Config $config
     */
    public static function addJQuery(Config $config)
    {
        if ($config('~theme.jquery') || strpos($config('~theme.custom_js', ''), 'jQuery') !== false) {
            wp_enqueue_script('jquery');
        }
    }

    /**
     * Disables the site icon meta tags.
     *
     * @link https://developer.wordpress.org/reference/hooks/site_icon_meta_tags/
     */
    public static function filterMetaTags()
    {
        return [];
    }
}
