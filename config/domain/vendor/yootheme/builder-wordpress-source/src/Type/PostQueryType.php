<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Str;

class PostQueryType
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
     * @return array
     */
    public function __invoke()
    {
        $name = Str::camelCase([$this->type->rest_base, 'Query'], true);
        $field = Str::camelCase($this->type->rest_base);

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
