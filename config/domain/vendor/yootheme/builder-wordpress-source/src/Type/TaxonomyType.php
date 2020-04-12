<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class TaxonomyType
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
        return [

            'fields' => [

                'name' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Name',
                        'filters' => ['limit'],
                    ],
                ],

                'description' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Description',
                        'filters' => ['limit'],
                    ],
                ],

                'link' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Link',
                    ],
                ],

                'count' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Item Count',
                    ],
                ],

                'field' => [
                    'type' => Str::camelCase([$this->taxonomy->name, 'Fields'], true),
                    'metadata' => [
                        'label' => 'Fields',
                    ],
                ],

            ] + ($this->taxonomy->hierarchical ? [

                'parent' => [
                    'type' => Str::camelCase($this->taxonomy->name, true),
                    'metadata' => [
                        'label' => 'Parent ' . $this->taxonomy->labels->singular_name,
                    ],
                ],

                'children' => [
                    'type' => [
                        'listOf' => Str::camelCase($this->taxonomy->name, true),
                    ],
                    'metadata' => [
                        'label' => 'Child ' . $this->taxonomy->labels->name,
                    ],
                ],

            ] : []),

            'metadata' => [
                'type' => true,
                'label' => $this->taxonomy->labels->singular_name,
            ],

            'resolvers' => $source->mapResolvers($this),

        ];
    }

    public function link(\WP_Term $term)
    {
        return get_term_link($term);
    }

    public function field(\WP_Term $term)
    {
        return $term;
    }

    public function parent(\WP_Term $term)
    {
        return $term->parent ? get_term($term->parent) : new \WP_Term((object) ['id' => 0, 'name' => 'ROOT', 'taxonomy' => $this->taxonomy->name]);
    }

    public function children(\WP_Term $term)
    {
        return get_terms(['taxonomy' => $this->taxonomy->name, 'hide_empty' => false, 'parent' => $term->term_id]);
    }
}
