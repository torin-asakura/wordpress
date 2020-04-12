<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Config;
use YOOtheme\File;

class WooCommerceListener
{
    /**
     * Initialize WooCommerce config.
     *
     * @param Config $config
     */
    public static function initConfig(Config $config)
    {
        $file = File::find("~theme/css/theme{.{$config('theme.id')},}.css");
        $compiled = strpos(File::getContents($file), '.woocommerce');

        // check if theme css needs to be updated
        if (class_exists('WooCommerce') xor $compiled) {
            $config->set('customizer.sections.styler.update', true);
        }

        // ignore files from being compiled into theme.css
        if (!class_exists('WooCommerce')) {
            $config->set('styler.ignore_less.woocommerce', 'woocommerce.less');
        }
    }

    /**
     * Remove WooCommerce general style.
     *
     * @param array $styles
     *
     * @return array
     */
    public static function removeStyle($styles)
    {
        unset($styles['woocommerce-general']);

        return $styles;
    }

    /**
     * Since WooCommerce 3.3.x, setting is available in the WP customizer.
     *
     * @param int    $items
     * @param Config $config
     *
     * @return int
     */
    public static function itemsPerPage(Config $config, $items)
    {
        return $config('~theme.woocommerce.items') ?: $items;
    }
}
