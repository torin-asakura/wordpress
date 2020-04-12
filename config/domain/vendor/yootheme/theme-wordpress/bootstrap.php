<?php

namespace YOOtheme\Theme\Wordpress;

use YOOtheme\Config;
use YOOtheme\Path;
use YOOtheme\Theme\SystemCheck as SysCheck;
use YOOtheme\View;

require 'supports.php';
require 'functions.php';

return [

    'config' => [

        'app' => [
            'isCustomizer' => is_customize_preview(),
        ],

    ],

    'theme' => function (Config $config) {
        return $config->loadFile(Path::get('./config/theme.json'));
    },

    'events' => [

        'app.request' => [
            SystemListener::class => 'checkPermission',
        ],

        'url.resolve' => [
            UrlListener::class => 'routeQueryParams',
        ],

    ],

    'actions' => [

        'wp_head' => [
            ThemeListener::class => ['addScript', 20],
        ],

        'wp_loaded' => [
            UrlListener::class => 'initLoader',
        ],

        'get_header' => [
            ThemeListener::class => 'onHeader',
        ],

        'wp_enqueue_scripts' => [
            ThemeListener::class => 'addJQuery',
            CommentListener::class => 'addScript',
        ],

        'customize_register' => [
            CustomizerListener::class => 'initConfig',
        ],

        'customize_controls_init' => [
            CustomizerListener::class => 'addAssets',
        ],

        'init' => [
            MenuListener::class => 'registerMenus',
            ChildThemeListener::class => 'initConfig',
        ],

        'admin_init' => [
            UpgradeListener::class => 'initUpdate',
        ],

        'after_switch_theme' => [
            ChildThemeListener::class => 'copyConfig',
        ],

    ],

    'filters' => [

        'upload_mimes' => [
            ThemeListener::class => 'addSvg',
        ],

        'wp_check_filetype_and_ext' => [
            ThemeListener::class => ['addSvgType', 10, 4],
        ],

        'site_icon_meta_tags' => [
            ThemeListener::class => 'filterMetaTags',
        ],

        'wp_nav_menu_args' => [
            MenuListener::class => 'filterMenuArgs',
        ],

        'widget_nav_menu_args' => [
            MenuListener::class => ['filterWidgetArgs', 10, 4],
        ],

        'wp_link_pages_link' => [
            PostListener::class => 'filterPageLink',
        ],

        'post_gallery' => [
            PostListener::class => ['filterGallery', 10, 3],
        ],

        'comment_class' => [
            CommentListener::class => 'filterClass',
        ],

        'comment_reply_link' => [
            CommentListener::class => 'filterReplyLink',
        ],

        'cancel_comment_reply_link' => [
            CommentListener::class => 'filterCancelLink',
        ],

        'get_comment_author_link' => [
            CommentListener::class => 'filterAuthorLink',
        ],

    ],

    'extend' => [

        View::class => function (View $view, $app) {

            if (!$app->config->get('~theme')) {
                return;
            }

            $view->addFunction('trans', function ($id) {
                return __($id, 'yootheme');
            });
        },

    ],

    'services' => [

        SysCheck::class => SystemCheck::class,

    ],

    'loaders' => [

        'theme' => new ThemeLoader($app),

    ],

];
