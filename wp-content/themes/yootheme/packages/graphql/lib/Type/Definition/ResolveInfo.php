<?php declare(strict_types=1);

namespace YOOtheme\GraphQL\Type\Definition;

use YOOtheme\GraphQL\Error\Error;
use YOOtheme\GraphQL\Error\InvariantViolation;
use YOOtheme\GraphQL\Language\AST\FieldNode;
use YOOtheme\GraphQL\Language\AST\FragmentDefinitionNode;
use YOOtheme\GraphQL\Language\AST\FragmentSpreadNode;
use YOOtheme\GraphQL\Language\AST\InlineFragmentNode;
use YOOtheme\GraphQL\Language\AST\OperationDefinitionNode;
use YOOtheme\GraphQL\Language\AST\SelectionSetNode;
use YOOtheme\GraphQL\Type\Schema;

/**
 * Structure containing information useful for field resolution process.
 *
 * Passed as 4th argument to every field resolver. See [docs on field resolving (data fetching)](data-fetching.md).
 *
 * @phpstan-import-type QueryPlanOptions from QueryPlan
 *
 * @phpstan-type Path list<string|int>
 */
class ResolveInfo
{
    /**
     * The definition of the field being resolved.
     *
     * @api
     */
    public FieldDefinition $fieldDefinition;

    /**
     * The name of the field being resolved.
     *
     * @api
     */
    public string $fieldName;

    /**
     * Expected return type of the field being resolved.
     *
     * @api
     */
    public Type $returnType;

    /**
     * AST of all nodes referencing this field in the query.
     *
     * @api
     *
     * @var \ArrayObject<int, FieldNode>
     */
    public \ArrayObject $fieldNodes;

    /**
     * Parent type of the field being resolved.
     *
     * @api
     */
    public ObjectType $parentType;

    /**
     * Path to this field from the very root value. When fields are aliased, the path includes aliases.
     *
     * @api
     *
     * @var list<string|int>
     *
     * @phpstan-var Path
     */
    public array $path;

    /**
     * Path to this field from the very root value. This will never include aliases.
     *
     * @api
     *
     * @var list<string|int>
     *
     * @phpstan-var Path
     */
    public array $unaliasedPath;

    /**
     * Instance of a schema used for execution.
     *
     * @api
     */
    public Schema $schema;

    /**
     * AST of all fragments defined in query.
     *
     * @api
     *
     * @var array<string, FragmentDefinitionNode>
     */
    public array $fragments = [];

    /**
     * Root value passed to query execution.
     *
     * @api
     *
     * @var mixed
     */
    public $rootValue;

    /**
     * AST of operation definition node (query, mutation).
     *
     * @api
     */
    public OperationDefinitionNode $operation;

    /**
     * Array of variables passed to query execution.
     *
     * @api
     *
     * @var array<string, mixed>
     */
    public array $variableValues = [];

    /**
     * @param \ArrayObject<int, FieldNode> $fieldNodes
     * @param list<string|int> $path
     * @param list<string|int> $unaliasedPath
     *
     * @phpstan-param Path $path
     * @phpstan-param Path $unaliasedPath
     *
     * @param array<string, FragmentDefinitionNode> $fragments
     * @param mixed|null $rootValue
     * @param array<string, mixed> $variableValues
     */
    public function __construct(
        FieldDefinition $fieldDefinition,
        \ArrayObject $fieldNodes,
        ObjectType $parentType,
        array $path,
        Schema $schema,
        array $fragments,
        $rootValue,
        OperationDefinitionNode $operation,
        array $variableValues,
        array $unaliasedPath = []
    ) {
        $this->fieldDefinition = $fieldDefinition;
        $this->fieldName = $fieldDefinition->name;
        $this->returnType = $fieldDefinition->getType();
        $this->fieldNodes = $fieldNodes;
        $this->parentType = $parentType;
        $this->path = $path;
        $this->unaliasedPath = $unaliasedPath;
        $this->schema = $schema;
        $this->fragments = $fragments;
        $this->rootValue = $rootValue;
        $this->operation = $operation;
        $this->variableValues = $variableValues;
    }

    /**
     * Helper method that returns names of all fields selected in query for
     * $this->fieldName up to $depth levels.
     *
     * Example:
     * query MyQuery{
     * {
     *   root {
     *     id,
     *     nested {
     *      nested1
     *      nested2 {
     *        nested3
     *      }
     *     }
     *   }
     * }
     *
     * Given this ResolveInfo instance is a part of "root" field resolution, and $depth === 1,
     * method will return:
     * [
     *     'id' => true,
     *     'nested' => [
     *         nested1 => true,
     *         nested2 => true
     *     ]
     * ]
     *
     * Warning: this method it is a naive implementation which does not take into account
     * conditional typed fragments. So use it with care for fields of interface and union types.
     *
     * @param int $depth How many levels to include in output
     *
     * @return array<string, mixed>
     *
     * @api
     */
    public function getFieldSelection(int $depth = 0): array
    {
        $fields = [];

        foreach ($this->fieldNodes as $fieldNode) {
            if (isset($fieldNode->selectionSet)) {
                $fields = \array_merge_recursive(
                    $fields,
                    $this->foldSelectionSet($fieldNode->selectionSet, $depth)
                );
            }
        }

        return $fields;
    }

    /**
     * @param QueryPlanOptions $options
     *
     * @throws \Exception
     * @throws Error
     * @throws InvariantViolation
     */
    public function lookAhead(array $options = []): QueryPlan
    {
        return new QueryPlan(
            $this->parentType,
            $this->schema,
            $this->fieldNodes,
            $this->variableValues,
            $this->fragments,
            $options
        );
    }

    /** @return array<string, bool> */
    private function foldSelectionSet(SelectionSetNode $selectionSet, int $descend): array
    {
        /** @var array<string, bool> $fields */
        $fields = [];

        foreach ($selectionSet->selections as $selectionNode) {
            if ($selectionNode instanceof FieldNode) {
                $fields[$selectionNode->name->value] = $descend > 0 && $selectionNode->selectionSet !== null
                    ? \array_merge_recursive(
                        $fields[$selectionNode->name->value] ?? [],
                        $this->foldSelectionSet($selectionNode->selectionSet, $descend - 1)
                    )
                    : true;
            } elseif ($selectionNode instanceof FragmentSpreadNode) {
                $spreadName = $selectionNode->name->value;
                if (isset($this->fragments[$spreadName])) {
                    $fragment = $this->fragments[$spreadName];
                    $fields = \array_merge_recursive(
                        $this->foldSelectionSet($fragment->selectionSet, $descend),
                        $fields
                    );
                }
            } elseif ($selectionNode instanceof InlineFragmentNode) {
                $fields = \array_merge_recursive(
                    $this->foldSelectionSet($selectionNode->selectionSet, $descend),
                    $fields
                );
            }
        }

        return $fields;
    }
}
