<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class TermType
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

                $this->taxonomy->rest_base => [
                    'type' => [
                        'listOf' => Str::camelCase($this->taxonomy->name, true),
                    ],
                    'metadata' => [
                        'label' => $this->taxonomy->label,
                    ],
                ],

            ],

            'resolvers' => [

                $this->taxonomy->rest_base => function ($post) {
                    return wp_get_post_terms($post->ID, $this->taxonomy->name);
                },

            ],

        ];
    }
}
