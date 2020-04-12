<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class CustomTaxonomyQueryType
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
        $term = Str::camelCase($this->taxonomy->name, true);
        $terms = Str::camelCase($this->taxonomy->rest_base, true);

        return [

            'fields' => [

                $single = "custom{$term}" => [
                    'type' => $term,
                    'args' => [
                        'id' => [
                            'type' => 'Int',
                        ],
                    ],
                    'metadata' => [$this, 'getTermMetadata'],
                ],

                $multiple = "custom{$terms}" => [
                    'type' => [
                        'listOf' => $term,
                    ],
                    'args' => [
                        'id' => [
                            'type' => 'Int',
                        ],
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                        'order' => [
                            'type' => 'String',
                        ],
                        'order_direction' => [
                            'type' => 'String',
                        ],
                    ],
                    'metadata' => [$this, 'getTermsMetadata'],
                ],

            ],

            'resolvers' => $source->mapResolvers($this, [
                $single => 'customTerm',
                $multiple => 'customTerms',
            ]),

        ];
    }

    public function customTerm($root, array $args)
    {
        $args += ['id' => 0];
        $term = get_term($args['id']);

        return $term instanceof \WP_Term ? $term : null;
    }

    public function customTerms($root, array $args)
    {
        $query = [
            'taxonomy' => $this->taxonomy->name,
            'parent' => $args['id'],
            'orderby' => $args['order'],
            'order' => $args['order_direction'],
            'number' => $args['limit'],
            'offset' => $args['offset'],
            'hide_empty' => false,
        ];

        return get_terms($query);
    }

    public function getTermMetadata()
    {
        return [
            'label' => "Custom {$this->taxonomy->labels->singular_name}",
            'group' => 'Custom',
            'fields' => [
                'id' => [
                    'label' => $this->taxonomy->labels->singular_name,
                    'type' => 'select-term',
                    'taxonomy' => $this->taxonomy->name
                ],
            ],
        ];
    }

    public function getTermsMetadata()
    {

        $singular_name_lower = Str::lower($this->taxonomy->labels->singular_name);
        $plural_name_lower = Str::lower($this->taxonomy->label);

        return [
            'label' => "Custom {$this->taxonomy->label}",
            'group' => 'Custom',
            'fields' => [
                'id' => [
                    'label' => "Parent {$this->taxonomy->labels->singular_name}",
                    'description' => "Select the parent {$singular_name_lower} from which {$plural_name_lower} should be loaded.",
                    'type' => 'select-term',
                    'taxonomy' => $this->taxonomy->name,
                    'root' => true,
                    'default' => 0
                ],
                '_offset' => [
                    'description' => "Set the starting point and limit the number of {$plural_name_lower}.",
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
                '_order' => [
                    'type' => 'grid',
                    'width' => '1-2',
                    'fields' => [
                        'order' => [
                            'label' => 'Order',
                            'type' => 'select',
                            'default' => 'term_order',
                            'options' => [
                                'Default' => 'term_order',
                                'Alphabetical' => 'name',
                            ],
                        ],
                        'order_direction' => [
                            'label' => 'Direction',
                            'type' => 'select',
                            'default' => 'ASC',
                            'options' => [
                                'Ascending' => 'ASC',
                                'Descending' => 'DESC',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
