<?php

namespace YOOtheme;

use YOOtheme\View\FileLoader;
use YOOtheme\View\HtmlHelper;
use YOOtheme\View\MetadataHelper;
use YOOtheme\View\SectionHelper;
use YOOtheme\View\StrHelper;

return [

    'services' => [

        View::class => function (FileLoader $loader, MetadataHelper $helper) {

            $view = new View($loader);
            $view->addGlobal('view', $view);
            $view->addHelper(StrHelper::class);
            $view->addHelper(HtmlHelper::class);
            $view->addHelper(SectionHelper::class);
            $view->addHelper([$helper, 'register']);

            return $view;
        },

    ],

];
