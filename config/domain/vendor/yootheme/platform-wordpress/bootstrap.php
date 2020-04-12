<?php

namespace YOOtheme;

use YOOtheme\Wordpress\FilterLoader;
use YOOtheme\Wordpress\Platform;
use YOOtheme\Wordpress\Router;

global $wpdb;

$webroot_dir = ABSPATH;
while (!Str::startsWith(WP_CONTENT_DIR, $webroot_dir)) {
    $webroot_dir = dirname($webroot_dir);
}

/*
 * baseUrl depending on environment
 * can not use home_url(), with WPML this can change based on the current language
 * site_url() provides the url without language code, but points to the /wp folder in bedrock
 * WP_HOME is available in bedrock
 */
Url::setBase(defined('WP_HOME') ? WP_HOME : site_url());
Path::setAlias('~', $webroot_dir);

return [

    'config' => function () use ($webroot_dir) {

        global $wp_version;

        return [

            'app' => [
                'platform' => 'wordpress',
                'version' => $wp_version,
                'secret' => NONCE_KEY,
                'debug' => WP_DEBUG,
                'rootDir' => strtr($webroot_dir, '\\', '/'),
                'tempDir' => strtr(get_temp_dir(), '\\', '/'),
                'adminDir' => Path::resolve(ABSPATH, 'wp-admin'),
                'pluginDir' => strtr(WP_PLUGIN_DIR, '\\', '/'),
                'contentDir' => strtr(WP_CONTENT_DIR, '\\', '/'),
                'isSite' => !is_admin(),
                'isAdmin' => is_admin(),
            ],

            // TODO
            'req' => [
                'baseUrl' => home_url('', 'relative'),
                'rootUrl' => home_url('', 'relative'),
                'siteUrl' => site_url(),
            ],

            'locale' => [
                'rtl' => is_rtl(),
                'code' => get_user_locale(),
            ],

            'session' => [
                'token' => wp_create_nonce(),
            ],

            'user' => wp_get_current_user(),

        ];

    },

    'events' => [

        'url.route' => [
            Router::class => 'generate',
        ],

        'app.error' => [
            Platform::class => 'handleError',
        ],

    ],

    'actions' => [

        'wp_ajax_kernel' => [
            Platform::class => 'handleRoute',
        ],

        'wp_ajax_nopriv_kernel' => [
            Platform::class => 'handleRoute',
        ],

        'wp_footer' => [
            Platform::class => 'registerScriptsFooter',
        ],

        'wp_head' => [
            Platform::class => [['printStyles', 8], ['printScripts', 20]],
        ],

        'admin_print_scripts' => [
            Platform::class => [['printStyles', 8], ['printScripts', 20]],
        ],

    ],

    'loaders' => [

        'filters' => new FilterLoader(),
        'actions' => new FilterLoader(),

    ],

    'services' => [

        Database::class => [
            'class' => Wordpress\Database::class,
            'arguments' => ['$db' => $wpdb],
        ],

        CsrfMiddleware::class => [
            'arguments' => ['$token' => $app->wrap(Config::class, ['session.token']), '$verify' => 'wp_verify_nonce'],
        ],

        Storage::class => Wordpress\Storage::class,

        HttpClientInterface::class => Wordpress\HttpClient::class,

        Wordpress\Update::class => '',
    ],

];
