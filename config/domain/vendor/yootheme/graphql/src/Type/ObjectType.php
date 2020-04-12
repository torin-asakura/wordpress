<?php

namespace YOOtheme\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType as BaseType;

class ObjectType extends BaseType
{
    /**
     * @var callable
     */
    protected $loader;

    /**
     * Constructor.
     *
     * @param string   $name
     * @param callable $loader
     */
    public function __construct($name, callable $loader)
    {
        $this->name = $name;
        $this->loader = $loader;
    }

    /**
     * @return FieldDefinition[]
     * @throws InvariantViolation
     */
    public function getFields()
    {
        if (is_null($this->config)) {
            call_user_func($this->loader, $this);
        }

        return parent::getFields();
    }
}
