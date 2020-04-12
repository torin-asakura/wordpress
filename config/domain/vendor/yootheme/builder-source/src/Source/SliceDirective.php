<?php

namespace YOOtheme\Builder\Source;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\Type;

class SliceDirective extends Directive
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name' => 'slice',
            'locations' => [
                DirectiveLocation::FIELD,
                DirectiveLocation::FRAGMENT_SPREAD,
                DirectiveLocation::INLINE_FRAGMENT,
            ],
            'args' => [
                new FieldArgument([
                    'name' => 'offset',
                    'type' => Type::int(),
                ]),
                new FieldArgument([
                    'name' => 'limit',
                    'type' => Type::int(),
                ]),
            ],
        ]);
    }

    /**
     * Directive callback.
     *
     * @param array $value
     * @param array $args
     *
     * @return array
     */
    public function __invoke($value, array $args)
    {
        extract($args + ['offset' => 0, 'limit' => null]);

        if ($offset || $limit) {
            $value = array_slice($value, (int) $offset, (int) $limit ?: null);
        }

        return $value;
    }
}
