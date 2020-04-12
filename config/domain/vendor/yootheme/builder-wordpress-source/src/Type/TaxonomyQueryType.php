<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Str;

class TaxonomyQueryType
{
    /**
     * @var \WP_Post_Type
     */
    protected $type;

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
     * @return array
     */
    public function __invoke()
    {
        $name = Str::camelCase([$this->taxonomy->rest_base, 'Query'], true);
        $field = Str::camelCase($this->taxonomy->rest_base);

        return [

            'fields' => [
                $field => ['type' => $name],
            ],

            'resolvers' => [
                $field => [$this, 'resolve'],
            ],

        ];
    }

    public function resolve($root)
    {
        return $root;
    }
}
