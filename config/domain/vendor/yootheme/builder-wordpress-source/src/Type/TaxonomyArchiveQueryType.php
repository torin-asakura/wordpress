<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class TaxonomyArchiveQueryType
{
    /**
     * @var \WP_Taxonomy
     */
    protected $taxonomy;

    /**
     * Constructor.
     *
     * @param \WP_Taxonomy $taxonomy
     */
    public function __construct(\WP_Taxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    /**
     * @param Source $source
     *
     * @return array
     */
    public function __invoke(Source $source)
    {
        $metadata = [
            'group' => 'Page',
            'view' => ["taxonomy-{$this->taxonomy->name}"],
        ];

        $config = [

            'fields' => [

                $field = Str::camelCase(['taxonomy', $this->taxonomy->name]) => [
                    'type' => Str::camelCase($this->taxonomy->name, true),
                    'metadata' => $metadata + [
                        'label' => $this->taxonomy->labels->singular_name,
                    ],
                ],

            ],

            'resolvers' => [$field => [$this, 'taxonomy']],

        ];

        foreach ($this->taxonomy->object_type as $name) {
            $config = $this->mapObjectType($config, $name, $metadata);
        }

        return $config;
    }

    public function taxonomy()
    {
        global $wp_query;

        return $wp_query->queried_object;
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

    protected function mapObjectType($config, $name, array $metadata)
    {
        global $wp_post_types;

        $type = $wp_post_types[$name];
        $field = Str::camelCase([$this->taxonomy->name, $name]);

        $config['fields'][$field] = [

            'type' => [
                'listOf' => Str::camelCase($name, true),
            ],

            'args' => [
                'offset' => [
                    'type' => 'Int',
                ],
                'limit' => [
                    'type' => 'Int',
                ],
            ],

            'metadata' => $metadata + [
                'label' => $type->label,
                'fields' => [
                    '_offset' => [
                        'description' => "Set the starting point and limit the number of {$type->label}.",
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
                                'attrs' => [
                                    'placeholder' => 'No limit',
                                    'min' => 0,
                                ],
                            ],
                        ],
                    ],
                ],
            ],

        ];

        $config['resolvers'][$field] = [$this, 'posts'];

        return $config;
    }
}
