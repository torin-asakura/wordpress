<?php

namespace YOOtheme\Builder\Wordpress\Source\Type;

use YOOtheme\Builder\Source;
use YOOtheme\Builder\Wordpress\Source\AcfHelper;
use YOOtheme\Str;

class FieldsType
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Source
     */
    protected $source;

    /**
     * Constructor.
     *
     * @param string $type
     * @param string $name
     */
    public function __construct($type, $name = '')
    {
        $this->type = $type;
        $this->name = $name;
    }

    public function __invoke(Source $source)
    {
        $this->source = $source;

        $fields = [];
        $resolvers = [];

        foreach (AcfHelper::groups($this->type, $this->name) as $group) {
            foreach (acf_get_fields($group) as $field) {

                $config = [
                    'type' => 'String',
                    'metadata' => [
                        'label' => $field['label'] ?: $field['name'],
                        'group' => $group['title'],
                    ],
                ];

                $fieldType = Str::camelCase($field['type'], true);
                $fieldConfig = is_callable($load = [$this, "loadField{$fieldType}"]) ? $load($field, $config) : $this->loadField($field, $config);

                if (!$fieldConfig) {
                    continue;
                }

                $fields[$field['name']] = $fieldConfig;
                $resolvers[$field['name']] = function ($item) use ($field, $fieldType) {
                    return is_callable($resolve = [$this, "resolveField{$fieldType}"]) ? $resolve($field, $item) : $this->resolveField($field, $item);
                };

            }
        }

        return compact('fields', 'resolvers');
    }

    protected function loadField($field, array $config)
    {
        if (in_array($field['type'], ['message', 'accordion', 'tab', 'clone', 'flexible_content'])) {
            return;
        }

        if (isset($field['choices'])) {
            return $this->loadFieldChoices($field, $config);
        }

        if (in_array($field['type'], ['file', 'link', 'image'])) {
            return $this->loadFieldFileReference($field, $config);
        }

        if (isset($field['sub_fields'])) {
            return $this->loadFieldSubfields($field, $config);
        }

        if ($this->isMultiple($field)) {

            $name = $this->getFieldGroupName($field);

            $this->source->addType($name, FieldType::class, ['fields' => [
                'value' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Value',
                        'group' => $config['metadata']['group'],
                    ],
                ],
            ]]);

            return ['type' => ['listOf' => $name]] + $config;
        }

        return $config;
    }

    protected function loadFieldDatePicker($field, array $config)
    {
        return array_merge_recursive($this->loadField($field, $config), ['metadata' => ['filters' => ['date']]]);
    }

    protected function loadFieldDateTimePicker($field, array $config)
    {
        return $this->loadFieldDatePicker($field, $config);
    }

    protected function loadFieldTimePicker($field, array $config)
    {
        return $this->loadFieldDatePicker($field, $config);
    }

    protected function loadFieldRelationship($field, $config)
    {
        return $this->loadFieldPostObject($field, $config);
    }

    protected function loadFieldPostObject($field, $config)
    {
        $multiple = $this->isMultiple($field);

        if (empty($field['post_type']) || count($field['post_type']) > 1) {
            return;
        }

        $type = $this->getPostType(array_pop($field['post_type']));

        if (!$type) {
            return;
        }

        $type = Str::camelCase($type->name, true);

        return ['type' => $multiple ? ['listOf' => $type] : $type] + $config;
    }

    protected function loadFieldTaxonomy($field, $config)
    {
        $multiple = $this->isMultiple($field);

        if (empty($field['taxonomy'])) {
            return;
        }

        $taxonomy = $this->getTaxonomy($field['taxonomy']);

        if (!$taxonomy) {
            return;
        }

        $taxonomy = Str::camelCase($taxonomy->name, true);

        return ['type' => $multiple ? ['listOf' => $taxonomy] : $taxonomy] + $config;
    }

    protected function loadFieldUser($field, array $config)
    {
        return ['type' => $this->isMultiple($field) ? ['listOf' => 'User'] : 'User'] + $config;
    }

    protected function loadFieldChoices($field, array $config)
    {
        $multiple = $this->isMultiple($field);

        $name = $this->getFieldGroupName($field);

        $this->source->addType($name, FieldType::class, ['fields' => [
            'label' => [
                'type' => 'String',
                'metadata' => [
                    'label' => $multiple ? 'Label' : "{$field['label']} Label",
                    'group' => $config['metadata']['group'],
                ],
            ],
            'value' => [
                'type' => 'String',
                'metadata' => [
                    'label' => $multiple ? 'Value' : "{$field['label']} Value",
                    'group' => $config['metadata']['group'],
                ],
            ],
        ]]);

        return ['type' => $multiple ? ['listOf' => $name] : $name] + $config;
    }

    protected function loadFieldFileReference($field, array $config)
    {
        $name = $this->getFieldGroupName($field);

        $this->source->addType($name, FieldType::class, ['fields' => [
            'title' => [
                'type' => 'String',
                'metadata' => [
                    'label' => "{$field['label']} Title",
                    'group' => $config['metadata']['group'],
                ],
            ],
            'url' => [
                'type' => 'String',
                'metadata' => [
                    'label' => "{$field['label']} Url",
                    'group' => $config['metadata']['group'],
                ],
            ],
        ]]);

        return ['type' => $name] + $config;
    }

    protected function loadFieldGoogleMap($field, array $config)
    {
        $name = $this->getFieldGroupName($field);
        $props = ['address', 'lat', 'lng', 'zoom', 'place_id', 'street_number', 'street_name', 'street_name_short', 'city', 'state', 'state_short', 'post_code', 'country', 'country_short'];
        $fields = [];

        foreach($props as $prop) {
            $fields[$prop] = [
                'type' => 'String',
                'metadata' => [
                    'label' => $field['label'] . ' ' . Str::titleCase(str_replace('_', ' ', $prop)),
                    'group' => $config['metadata']['group'],
                ],
            ];
        }

        $this->source->addType($name, FieldType::class, ['fields' => $fields]);

        return ['type' => $name] + $config;
    }

    protected function loadFieldGallery($field, array $config)
    {
        $name = $this->getFieldGroupName($field);
        $props = ['title', 'name', 'filename', 'url', 'link', 'alt', 'description', 'caption'];
        $fields = [];

        foreach($props as $prop) {
            $fields[$prop] = [
                'type' => 'String',
                'metadata' => [
                    'label' => Str::titleCase(str_replace('_', ' ', $prop)),
                    'group' => $config['metadata']['group'],
                ],
            ];
        }

        $this->source->addType($name, FieldType::class, ['fields' => $fields]);

        return ['type' => ['listOf' => $name]] + $config;
    }

    protected function loadFieldSubfields($field, array $config)
    {
        $name = $this->getFieldGroupName($field);
        $multiple = $this->isMultiple($field);
        $fields = [];

        foreach ($field['sub_fields'] as $sub_field) {
            $fields[$sub_field['name']] = $this->loadField($sub_field, [
                'type' => 'String',
                'metadata' => [
                    'label' => $sub_field['label'] ?: $sub_field['name'],
                    'group' => $field['name'],
                ],
            ]);
        }

        $this->source->addType($name, FieldType::class, ['fields' => $fields]);

        return ['type' => $multiple ? ['listOf' => $name] : $name] + $config;
    }

    protected function resolveField($field, $item)
    {
        $postId = acf_get_valid_post_id($item);

        // Subfields field
        if (array_key_exists('sub_fields', $field)) {

            if (empty($field['sub_fields'])) {
                return;
            }

            if ($this->isMultiple($field)) {

                $value = acf_get_metadata($postId, $field['name']);

                $values = [];
                for ($i = 0; $i < $value; $i++) {
                    foreach ($field['sub_fields'] as $subfield) {
                        $values[$i][$subfield['name']] = $this->resolveField(['name' => "{$field['name']}_{$i}_{$subfield['name']}"] + $subfield, $item);
                    }
                }

                return $values;
            }

            $values = [];
            foreach ($field['sub_fields'] as $subfield) {
                $values[$subfield['name']] = $this->resolveField($subfield, $item);
            }

            return $values;
        }

        switch ($field['type']) {
            case 'post_object':
            case 'relationship':
            case 'taxonomy':
            case 'user':
                $field['return_format'] = 'object';
                break;
            case 'button_group':
            case 'checkbox':
            case 'radio':
            case 'select':
            case 'gallery':
            case 'file':
            case 'image':
            case 'link':
                $field['return_format'] = 'array';
        }

        // get value for field
        $value = acf_get_value($postId, $field);

        if ($value === null) {
            return;
        }

        $value = acf_format_value($value, $postId, $field);

        if (!empty($field['return_format'])) {
            return $value ?: null;
        }

        if ($this->isMultiple($field)) {
            return array_map(function ($value) { return compact('value'); }, $value);
        }

        return $value;
    }

    protected function isMultiple($field)
    {
        return !empty($field['multiple']) && $field['multiple']
            || in_array($field['type'], ['checkbox', 'relationship'])
            || !empty($field['field_type']) && !in_array($field['field_type'], ['select', 'radio'])
            || isset($field['sub_fields'], $field['max']);
    }

    protected function getPostType($post_type)
    {
        global $wp_post_types;

        if (empty($wp_post_types[$post_type]->rest_base) || $wp_post_types[$post_type]->name === $wp_post_types[$post_type]->rest_base) {
            return;
        }

        return $wp_post_types[$post_type];
    }

    protected function getTaxonomy($taxonomy)
    {
        global $wp_taxonomies;

        if (empty($wp_taxonomies[$taxonomy]->rest_base) || $wp_taxonomies[$taxonomy]->name === $wp_taxonomies[$taxonomy]->rest_base) {
            return;
        }

        return $wp_taxonomies[$taxonomy];
    }

    protected function getFieldGroupName($field)
    {
        $parentField = acf_get_field($field['parent']);

        $prefix = $parentField ? (Str::camelCase(['Field', $parentField['name']], true) . '_') : '';

        return Str::camelCase([$prefix, 'Field', $field['name']], true);
    }
}
