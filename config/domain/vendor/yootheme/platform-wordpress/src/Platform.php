<?php

namespace YOOtheme\Wordpress;

use YOOtheme\Application;
use YOOtheme\Metadata;
use YOOtheme\Url;

class Platform
{
    /**
     * Handle application routes.
     *
     * @param Application $app
     */
    public static function handleRoute(Application $app)
    {
        $app->run(); exit;
    }

    /**
     * Handle application kernel errors.
     *
     * @param object     $request
     * @param \Exception $exception
     *
     * @throws \Exception
     */
    public static function handleError($request, $exception)
    {
        throw $exception;
    }

    /**
     * Prints style tags.
     *
     * @param Metadata $metadata
     */
    public static function printStyles(Metadata $metadata)
    {
        foreach ($metadata->all('style:*') as $name => $style) {

            $metadata->del($name);

            if ($style->href) {
                $style = $style->withAttribute('href', Url::to($style->href, ['ver' => $style->version], is_ssl()))->withAttribute('version', '');
            }

            echo "{$style}\n";
        }
    }

    /**
     * Prints script tags.
     *
     * @param Metadata $metadata
     */
    public static function printScripts(Metadata $metadata)
    {
        foreach ($metadata->all('script:*') as $name => $script) {

            $metadata->del($name);

            if ($script->src) {
                $script = $script->withAttribute('src', Url::to($script->src, ['ver' => $script->version], is_ssl()));
            }

            echo "{$script}\n";
        }
    }

    /**
     * Callback to register scripts in footer.
     *
     * @param Metadata $metadata
     * @param Url      $url
     */
    public static function registerScriptsFooter(Metadata $metadata)
    {
        foreach ($metadata->all('style:*') as $style) {

            if (!$style->defer) {
                continue;
            }

            if ($style->href) {

                $params = ($ver = $style->version) ? ['ver' => $ver] : [];
                echo "<style>@import '" . Url::to($style->href, $params, is_ssl()) . "';</style>\n";

            } elseif ($value = $style->getValue()) {
                echo "<style>{$value}</style>\n";
            }
        }

        foreach ($metadata->all('script:*') as $script) {

            if (!$script->defer) {
                continue;
            }

            if ($script->src) {
                wp_enqueue_script($script->getName(), Url::to($script->src, [], is_ssl()), [], $script->version, true);
            } elseif ($value = $script->getValue()) {
                echo "<script>{$value}</script>\n";
            }
        }
    }
}
