<?php

namespace YOOtheme;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;

return [

    'events' => [

        'app.request' => [
            CsrfMiddleware::class => ['@handle', 10],
            RouterMiddleware::class => [['@handleRoute', 30], ['@handleStatus', 20]],
        ],

        'app.error' => [
            RouterMiddleware::class => ['@handleError', 10],
        ],

        'url.resolve' => [
            UrlResolver::class => 'resolve',
        ],

    ],

    'extend' => [

        View::class => function (View $view, $app) {
            $view->addFunction('url', $app->wrap(Url::class . '@to'));
            $view->addFunction('route', $app->wrap(Url::class . '@route'));
        },

    ],

    'aliases' => [

        Routes::class => 'routes',
        Request::class => ['request', ServerRequestInterface::class],
        Response::class => ['response', ResponseInterface::class],

    ],

    'services' => [

        Response::class => '',
        Request::class => [
            'factory' => [Request::class, 'fromGlobals'],
            'arguments' => ['$uri' => $app->wrap(Config::class, ['req.href'])],
        ],

        Routes::class => '',
        Router::class => '',
        RouterMiddleware::class => '',
    ],

];
