<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class PostArchiveQueryType
{
    /**
     * @var array
     */
    public $fields;

    /**
     * @var array
     */
    public $resolvers;

    /**
     * @var \WP_Post_Type
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param \WP_Post_Type $type
     */
    public function __construct(\WP_Post_Type $type)
    {
        $this->type = $type;
    }

    /**
     * @param Source $source
     *
     * @return array
     */
    public function __invoke(Source $source)
    {
        $name = Str::camelCase($this->type->name, true);
        $field = Str::camelCase(['archive', $this->type->name]);

        return [

            'fields' => [

                $field => [

                    'type' => [
                        'listOf' => $name,
                    ],

                    'args' => [
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                    ],

                    'metadata' => [$this, 'getMetadata'],
                ],

            ],

            'resolvers' => [

                $field => [$this, 'posts'],

            ],

        ];
    }

    public function posts($root, array $args)
    {
        global $wp_query;

        $args += [
            'offset' => 0,
            'limit' => null,
        ];

        if ($args['offset'] || $args['limit']) {
            return array_slice($wp_query->posts, (int) $args['offset'], (int) $args['limit'] ?: null);
        }

        return $wp_query->posts;
    }

    public function getMetadata()
    {
        return [

            'label' => $this->type->label,
            'group' => 'Page',
            'view' => ["archive-{$this->type->name}"],
            'fields' => [
                '_offset' => [
                    'description' => "Set the starting point and limit the number of {$this->type->label}.",
                    'type' => 'grid',
                    'width' => '1-2',
                    'fields' => [
                        'offset' => [
                            'label' => 'Start',
                            'type' => 'number',
                            'default' => 0,
                            'modifier' => 1,
                            'attrs' => [
                                'min' => 1,
                                'required' => true,
                            ],
                        ],
                        'limit' => [
                            'label' => 'Quantity',
                            'type' => 'limit',
                            'default' => 10,
                            'attrs' => [
                                'min' => 1,
                            ],
                        ],
                    ],
                ],
            ],

        ];
    }
}
