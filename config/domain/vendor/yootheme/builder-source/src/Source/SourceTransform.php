<?php

namespace YOOtheme\Builder\Source;

use function YOOtheme\app;
use YOOtheme\Arr;
use YOOtheme\Builder\Source;
use YOOtheme\Str;

class SourceTransform
{
    /**
     * @var array
     */
    public $filters;

    /**
     * Constructor.
     *
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = array_merge([
            'date' => [$this, 'applyDate'],
            'limit' => [$this, 'applyLimit'],
            'search' => [$this, 'applySearch'],
            'before' => [$this, 'applyBefore'],
            'after' => [$this, 'applyAfter'],
        ], $filters);
    }

    /**
     * Adds a filter.
     *
     * @param string   $name
     * @param callable $filter
     * @param int      $offset
     */
    public function addFilter($name, callable $filter, $offset = PHP_INT_MAX)
    {
        Arr::splice($this->filters, $offset, 0, [$name => $filter]);
    }

    /**
     * Transform callback.
     *
     * @param object $node
     * @param array  $params
     *
     * @return bool|void
     */
    public function __invoke($node, array $params)
    {
        if (empty($node->source->query) || empty($node->source->props)) {
            return;
        }

        // query source data
        $data = app(Source::class)->querySource($node->source, $params);

        // map source properties
        if ($data && empty($data[0])) {
            $this->mapSource($node, $params + compact('data'));
        } elseif ($data) {
            $this->repeatSource($node, $params + compact('data'));
        } elseif (!is_null($data)) {
            return false;
        }
    }

    /**
     * Map source properties.
     *
     * @param object $node
     * @param array  $params
     *
     * @return object
     */
    public function mapSource($node, array $params)
    {
        foreach ((array) @$node->source->props as $name => $prop) {

            $value = Arr::get($params, "data.{$prop->name}");
            $filters = isset($prop->filters) ? (array) $prop->filters : [];

            // apply value filters
            foreach (array_intersect_key($this->filters, $filters) as $key => $filter) {
                $value = $filter($value, $filters[$key], $filters, $params);
            }

            // set filtered value
            $node->props[$name] = (string) $value;
        }

        return $node;
    }

    /**
     * Repeat node for each source item.
     *
     * @param object $node
     * @param array  $params
     *
     * @return bool
     */
    public function repeatSource($node, array $params)
    {
        $parent = $params['parent'];

        // clone node for each item
        foreach ($params['data'] as $index => $item) {

            if (!$index) {

                $nodes[$index] = $node;

            } else {

                $clone = $nodes[$index] = clone $node;
                $clone->transient = true;
                $clone->source = (object) [
                    'props' => $node->source->props,
                ];
            }

            $this->mapSource($nodes[$index], ['data' => $item] + $params);
        }

        array_splice($parent->children, array_search($node, $parent->children, true), 1, $nodes);
    }

    /**
     * Apply "before" filter.
     *
     * @param mixed $value
     * @param mixed $before
     *
     * @return string
     */
    public function applyBefore($value, $before)
    {
        return $before . $value;
    }

    /**
     * Apply "after" filter.
     *
     * @param mixed $value
     * @param mixed $after
     *
     * @return string
     */
    public function applyAfter($value, $after)
    {
        return $value . $after;
    }

    /**
     * Apply "limit" filter.
     *
     * @param mixed $value
     * @param mixed $limit
     *
     * @return string
     */
    public function applyLimit($value, $limit)
    {
        return $limit ? Str::limit(strip_tags($value), intval($limit)) : $value;
    }

    /**
     * Apply "date" filter.
     *
     * @param mixed $value
     * @param mixed $format
     *
     * @return false|string
     */
    public function applyDate($value, $format)
    {
        if (is_string($value)) {
            $value = strtotime($value);
        }

        return date($format ?: 'd/m/Y', intval($value) ?: time());
    }

    /**
     * Apply "search" filter.
     *
     * @param mixed $value
     * @param mixed $search
     * @param array $filters
     *
     * @return false|string
     */
    public function applySearch($value, $search, array $filters)
    {
        $replace = isset($filters['replace']) ? $filters['replace'] : '';

        if ($search && $search[0] === '/') {
            return @preg_replace($search, $replace, $value);
        }

        return str_replace($search, $replace, $value);
    }
}
