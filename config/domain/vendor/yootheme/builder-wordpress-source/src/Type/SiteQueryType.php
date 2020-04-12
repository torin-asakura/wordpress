<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;

class SiteQueryType
{
    /**
     * @param Source $source
     *
     * @return array
     */
    public function __invoke(Source $source)
    {
        return [

            'fields' => [

                'site' => [
                    'type' => 'Site',
                    'metadata' => [
                        'label' => 'Site',
                    ],
                ],

            ],

            'resolvers' => $source->mapResolvers($this),

        ];

    }

    public function site()
    {
        return [
            'title' => get_bloginfo('name', 'display'),
            'page_title' => wp_title('&raquo;', false),
        ];
    }
}
