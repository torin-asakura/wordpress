<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Builder\Wordpress\Source\AcfHelper;
use YOOtheme\Str;

class CustomPostsQueryType
{
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
        $field = Str::camelCase(['custom', $this->type->rest_base]);

        return [

            'fields' => [

                $field => [
                    'type' => [
                        'listOf' => $name,
                    ],
                    'args' => [
                        'terms' => [
                            'type' => [
                                'listOf' => 'Int',
                            ],
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
                    'metadata' => [$this, 'getMetadata'],
                ],

            ],

            'resolvers' => $source->mapResolvers($this, [$field => 'customPosts']),

        ];

    }

    public function customPosts($root, array $args)
    {
        $query = [
            'post_status' => 'publish',
            'post_type' => $this->type->name,
            'orderby' => $args['order'],
            'order' => $args['order_direction'],
            'offset' => $args['offset'],
            'numberposts' => $args['limit'],
            'tax_query' => [],
        ];

        if (Str::startsWith($query['orderby'], 'field:')) {
            $query['meta_key'] = substr($query['orderby'], 6);
            $query['orderby'] = 'meta_value';
        }

        if (!empty($args['terms'])) {

            $taxonomies = [];

            foreach ($args['terms'] as $id) {
                if ($term = get_term($id)) {
                    $taxonomies[$term->taxonomy][] = $id;
                }
            }

            foreach ($taxonomies as $taxonomy => $terms) {
                $query['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'terms' => $terms,
                    'include_children' => false,
                    'field' => 'term_id',
                ];
            }
        }

        return get_posts($query);
    }

    public function getMetadata()
    {
        $label_lower = strtolower($this->type->label);

        $fields = ($taxonomies = get_object_taxonomies($this->type->name)) ? [
            
            'terms' => [
                'label' => 'Limit by Terms',
                'description' => "Select the terms from which {$label_lower} should be loaded. {$this->type->label} from child terms are not included. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> to select multiple terms.",
                'type' => 'select-term',
                'taxonomies' => $taxonomies,
                'default' => [],
                'attrs' => [
                    'multiple' => true,
                    'class' => 'uk-height-medium uk-resize-vertical',
                ],
            ],

        ] : [];

        $orderOptions = [];
        foreach (AcfHelper::groups('post', $this->type->name) as $group) {
            $orderOptions[$group['title']] = array_map(function ($name) { return "field:{$name}"; }, array_column(acf_get_fields($group), 'name', 'label'));
        }

        return [
            'label' => "Custom {$this->type->label}",
            'group' => 'Custom',
            'fields' => $fields + [
                '_offset' => [
                    'description' => "Set the starting point and limit the number of {$label_lower}.",
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
                            'default' => 'none',
                            'options' => [
                                'Default' => 'none',
                                'Date' => 'date',
                                'Modified' => 'modified',
                                'Alphabetical' => 'title',
                                'Author' => 'author',
                                'Random' => 'rand',
                            ] + $orderOptions,
                        ],
                        'order_direction' => [
                            'label' => 'Direction',
                            'type' => 'select',
                            'default' => 'DESC',
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
