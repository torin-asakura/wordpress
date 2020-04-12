<?php

namespace YOOtheme\Builder\Source\Type;

class SiteType
{
    /**
     * Constructor.
     */
    public function __invoke()
    {
        $fields = [

            'title' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Site Title',
                    'filters' => ['limit'],
                ],
            ],

            'page_title' => [
                'type' => 'String',
                'metadata' => [
                    'label' => 'Page Title',
                    'filters' => ['limit'],
                ],
            ],

        ];

        $metadata = [
            'type' => true,
            'label' => 'Site',
        ];

        return compact('fields', 'metadata');
    }
}
