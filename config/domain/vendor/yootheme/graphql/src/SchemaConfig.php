<?php

namespace YOOtheme\GraphQL;

use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Values;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\SchemaConfig as Config;
use YOOtheme\GraphQL\Type\ObjectType;

class SchemaConfig extends Config
{
    /**
     * @var array
     */
    public $unresolvedTypes = [
        'RootQuery' => [],
        'RootMutation' => [],
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->query = $this->getType('RootQuery');
        $this->mutation = $this->getType('RootMutation');
        $this->typeLoader = [$this, 'getType'];
        $this->assumeValid = true;
    }

    /**
     * @param mixed $name
     *
     * @return Type
     */
    public function getType($name)
    {
        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        if ($type = $this->createType($name)) {
            return $this->types[$name] = $type;
        }
    }

    /**
     * @param Type $type
     */
    public function setType(Type $type)
    {
        $this->types[$type->name] = $type;
    }

    /**
     * @param string $name
     * @param string $class
     * @param mixed  ...$args
     */
    public function addType($name, $class, ...$args)
    {
        $this->unresolvedTypes[$name][] = [$class, $args];
    }

    /**
     * @param string $name
     *
     * @return Type
     */
    public function createType($name)
    {
        $internal = Type::getInternalTypes();

        if (isset($internal[$name])) {
            return $internal[$name];
        }

        if (isset($this->unresolvedTypes[$name])) {
            return new ObjectType($name, [$this, 'resolveType']);
        }
    }

    /**
     * @param Type $type
     *
     * @return Type
     */
    public function resolveType(Type $type)
    {
        $type->config = $config = $this->prepareConfig($type, $this->unresolvedTypes[$type->name]);

        if (isset($config['fields'])) {
            $type->config['fields'] = $this->prepareFields($type, $config['fields']);
        }

        if (isset($config['description'])) {
            $type->description = $config['description'];
        }

        if (isset($config['resolvers'])) {
            $type->resolveFieldFn = [$this, 'resolveField'];
        }

        return $type;
    }

    /**
     * @param mixed       $value
     * @param mixed       $args
     * @param mixed       $context
     * @param ResolveInfo $info
     *
     * @return mixed
     */
    public function resolveField($value, $args, $context, ResolveInfo $info)
    {
        $resolver = [Executor::class, 'defaultFieldResolver'];

        if (isset($info->parentType->config['resolvers'][$info->fieldName])) {
            $resolver = $info->parentType->config['resolvers'][$info->fieldName];
        }

        return $this->resolveDirectives($resolver($value, $args, $context, $info), $context, $info);
    }

    /**
     * @param mixed       $value
     * @param mixed       $context
     * @param ResolveInfo $info
     *
     * @return mixed
     */
    public function resolveDirectives($value, $context, ResolveInfo $info)
    {
        foreach ($info->fieldNodes as $node) {

            if (is_null($node->directives)) {
                continue;
            }

            foreach ($this->directives as $directive) {

                $args = Values::getDirectiveValues($directive, $node);

                if (is_array($args)) {
                    $value = $directive($value, $args, $context, $info);
                }
            }
        }

        return $value;
    }

    /**
     * @param mixed      $object
     * @param null|array $methods
     *
     * @return array
     */
    public function mapResolvers($object, array $methods = null)
    {
        $resolvers = [];

        if (is_null($methods)) {
            $methods = get_class_methods($object);
        }

        foreach ($methods as $name => $method) {

            if (is_int($name)) {
                $name = $method;
            }

            if (strpos($method, '__') !== 0) {
                $resolvers[$name] = [$object, $method];
            }
        }

        return $resolvers;
    }

    /**
     * @param Type  $type
     * @param array $loaders
     *
     * @return array
     */
    protected function prepareConfig($type, array $loaders)
    {
        $config = [];

        foreach ($loaders as $params) {

            list($class, $args) = $params;

            $loader = new $class(...$args);

            if (!is_callable($loader)) {
                continue;
            }

            if (is_array($values = $loader($this, $type))) {
                $config = array_replace_recursive($config, $values);
            }
        }

        return $config;
    }

    /**
     * @param Type  $type
     * @param mixed $name
     * @param array $config
     *
     * @return array
     */
    protected function prepareField(Type $type, $name, array $config)
    {
        $config += [
            'type' => null,
            'name' => lcfirst($name),
        ];

        if (is_string($config['type'])) {
            $config['type'] = $this->getType($config['type']);
        }

        if (is_array($config['type'])) {
            $config['type'] = $this->prepareModifiers($config['type']);
        }

        if (empty($config['type'])) {
            throw new InvariantViolation("Field '{$name}' on '{$type->name}' does not have a Type.");
        }

        if (!empty($config['args'])) {
            $config['args'] = $this->prepareFields($type, $config['args']);
        }

        return $config;
    }

    /**
     * @param Type  $type
     * @param mixed $fields
     *
     * @return array
     */
    protected function prepareFields(Type $type, $fields)
    {
        $result = [];

        if (empty($fields) || !is_array($fields)) {
            return $result;
        }

        foreach ($fields as $name => $config) {
            $result[$name] = $this->prepareField($type, $name, $config);
        }

        return array_filter($result);
    }

    /**
     * @param array $type
     *
     * @return Type|array
     */
    protected function prepareModifiers(array $type)
    {
        if (isset($type['nonNull'])) {

            if (is_string($type['nonNull'])) {
                $nonNull = $this->getType($type['nonNull']);
            } elseif (is_array($type['nonNull'])) {
                $nonNull = $this->prepareModifiers($type['nonNull']);
            }

            $type = Type::nonNull(isset($nonNull) ? $nonNull : Type::string());

        } elseif (isset($type['listOf'])) {

            if (is_string($type['listOf'])) {
                $listOf = $this->getType($type['listOf']);
            } elseif (is_array($type['listOf'])) {
                $listOf = $this->prepareModifiers($type['listOf']);
            }

            $type = Type::listOf(isset($listOf) ? $listOf : Type::string());
        }

        return $type;
    }
}
