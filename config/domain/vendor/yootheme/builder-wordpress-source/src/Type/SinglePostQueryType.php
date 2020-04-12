<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class SinglePostQueryType
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
        $field = Str::camelCase(['single', $this->type->name]);

        return [

            'fields' => [

                $field => [
                    'type' => $name,
                    'metadata' => [
                        'label' => $this->type->labels->singular_name,
                        'view' => ["single-{$this->type->name}"],
                        'group' => 'Page',
                    ],
                ],

            ],

            'resolvers' => $source->mapResolvers($this, [$field => 'singlePost']),

        ];
    }

    public function singlePost()
    {
        global $post;

        return $post;
    }
}
