<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Str;

class CustomPostQueryType
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
        $field = Str::camelCase(['custom', $this->type->name]);

        return [

            'fields' => [

                $field => [
                    'type' => $name,
                    'args' => [
                        'id' => [
                            'type' => 'Int',
                        ],
                    ],
                    'metadata' => [
                        'label' => "Custom {$this->type->labels->singular_name}",
                        'group' => 'Custom',
                        'fields' => [
                            'id' => [
                                'label' => $this->type->labels->singular_name,
                                'type' => 'select-item',
                                'post_type' => $this->type->name,
                                'labels' => [
                                    'type' => $this->type->labels->singular_name,
                                ],
                            ],
                        ],
                    ],
                ],

            ],

            'resolvers' => $source->mapResolvers($this, [$field => 'customPost']),

        ];
    }

    public function customPost($root, array $args)
    {
        $args += ['id' => 0];

        return $args['id'] ? get_post($args['id']) : null;
    }
}
