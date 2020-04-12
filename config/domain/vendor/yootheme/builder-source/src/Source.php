<?php

namespace YOOtheme\Builder;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use YOOtheme\Arr;
use YOOtheme\Builder\Source\SliceDirective;
use YOOtheme\GraphQL\SchemaConfig;
use YOOtheme\GraphQL\Type\Introspection;
use YOOtheme\GraphQL\Type\ObjectScalarType;

class Source extends SchemaConfig
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setType(new ObjectScalarType());
        $this->setDirectives(['slice' => new SliceDirective()]);
    }

    /**
     * Gets the schema.
     *
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema ?: ($this->schema = new Schema($this));
    }

    /**
     * Executes a query on schema.
     *
     * @param mixed  $source
     * @param mixed  $value
     * @param object $context
     * @param array  $variables
     *
     * @return ExecutionResult
     */
    public function query($source, $value = null, $context = null, $variables = null)
    {
        if (is_array($source)) {
            $source = AST::fromArray($source);
        }

        return GraphQL::executeQuery($this->getSchema(), $source, $value, $context, $variables);
    }

    /**
     * Executes an introspection on schema.
     *
     * @param array $options
     *
     * @return ExecutionResult
     */
    public function queryIntrospection(array $options = [])
    {
        $metadata = [
            'type' => $this->getType('Object'),
            'resolve' => function ($type, ...$args) {

                if (empty($type->config['metadata'])) {
                    return null;
                }

                if (is_callable($type->config['metadata'])) {
                    return $type->config['metadata']($type, ...$args);
                }

                return $type->config['metadata'];
            },
        ];

        $options += [
            '__Type' => compact('metadata'),
            '__Field' => compact('metadata'),
            '__InputValue' => compact('metadata'),
        ];

        return GraphQL::executeQuery($this->getSchema(), Introspection::getIntrospectionQuery($options));
    }

    /**
     * Query source definition.
     *
     * @param object $source
     * @param mixed  $value
     * @param object $context
     * @param array  $variables
     *
     * @return ExecutionResult
     */
    public function querySource($source, $value = null, $context = null, $variables = null)
    {
        $root = $query = $source->query;
        $name = "data.{$query->name}";

        // add field selection
        if (isset($source->query->field)) {

            $query->selections = [$source->query->field];
            $query = $source->query->field;
            $name .= ".{$query->name}";

        }

        // add source properties
        if (isset($source->props)) {
            $query->selections = (array) $source->props;
        }

        // get query result
        $result = $this->query([
            'kind' => 'Document',
            'definitions' => [
                $this->queryField($root, [
                    'kind' => 'OperationDefinition',
                    'operation' => 'query',
                ]),
            ],
        ], $value, $context, $variables);

        return Arr::get($result->toArray(), $name);
    }

    /**
     * Create nested fields AST.
     *
     * @param object $field
     * @param array  $parent
     *
     * @return array
     */
    public function queryField($field, array $parent)
    {
        $selections = null;

        foreach (array_reverse(explode('.', $field->name)) as $name) {

            $result = [
                'kind' => 'Field',
                'name' => [
                    'kind' => 'Name',
                    'value' => $name,
                ],
            ];

            if (!$selections) {

                $selection = isset($field->selections) ? (array) $field->selections : [];

                if (isset($field->arguments)) {
                    $result['arguments'] = $this->createArguments($field->arguments);
                }

                if (isset($field->directives)) {
                    $result['directives'] = $this->createDirectives($field->directives);
                }

                foreach ($selection as $select) {
                    $result = $this->queryField($select, $result);
                }

            } else {

                $result['selectionSet']['kind'] = 'SelectionSet';
                $result['selectionSet']['selections'][] = $selections;
            }

            $selections = $result;
        }

        $parent['selectionSet']['kind'] = 'SelectionSet';
        $parent['selectionSet']['selections'][] = $selections;

        return $parent;
    }

    /**
     * Create field AST.
     *
     * @param mixed $directives
     *
     * @return array
     */
    public function createDirectives($directives)
    {
        $result = [];

        foreach ($directives as $directive) {

            $result[] = [
                'kind' => 'Directive',
                'name' => [
                    'kind' => 'Name',
                    'value' => $directive->name,
                ],
                'arguments' => isset($directive->arguments) ? $this->createArguments($directive->arguments) : null,
            ];

        }

        return $result;
    }

    /**
     * Create field AST.
     *
     * @param mixed $arguments
     *
     * @return array
     */
    public function createArguments($arguments)
    {
        $result = [];

        foreach ((array) $arguments as $name => $value) {

            $result[] = [
                'kind' => 'Argument',
                'name' => [
                    'kind' => 'Name',
                    'value' => $name,
                ],
                'value' => $this->createValue($value),
            ];

        }

        return $result;
    }

    /**
     * Create field AST.
     *
     * @param mixed $value
     *
     * @return array
     */
    public function createValue($value)
    {
        if (!is_array($value)) {

            $type = ucfirst(strtr(gettype($value), [
                'integer' => 'int',
            ]));

            return [
                'kind' => "{$type}Value",
                'value' => (string) $value,
            ];
        }

        return [
            'kind' => 'ListValue',
            'values' => array_map([$this, 'createValue'], $value),
        ];
    }
}
