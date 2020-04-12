<?php

namespace YOOtheme\Builder\Wordpress\Source;

use YOOtheme\Builder\Source;
use YOOtheme\Builder\Source\Type\SiteType;
use YOOtheme\Config;
use YOOtheme\Http\Request;
use YOOtheme\Str;

class SourceListener
{
    public static function initSource(Source $source)
    {
        $types = [
            ['Site', SiteType::class],
            ['User', Type\UserType::class],
            ['UserFields', Type\FieldsType::class, 'user'],
            ['RootQuery', Type\SiteQueryType::class],
        ];

        foreach ($types as $args) {
            $source->addType(...$args);
        }

        $arguments = [
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
        ];

        foreach (get_post_types($arguments, 'objects') as $name => $type) {

            if (!$type->rest_base || $name === $type->rest_base) {
                continue;
            }

            $baseQuery = Str::camelCase([$type->rest_base, 'Query'], true);

            $source->addType(Str::camelCase($name, true), Type\PostType::class, $type);
            $source->addType(Str::camelCase([$name, 'Fields'], true), Type\FieldsType::class, 'post', $name);
            $source->addType('RootQuery', Type\PostQueryType::class, $type);
            $source->addType($baseQuery, Type\SinglePostQueryType::class, $type);
            $source->addType($baseQuery, Type\CustomPostQueryType::class, $type);
            $source->addType($baseQuery, Type\CustomPostsQueryType::class, $type);

            if ($name === 'post' || $type->has_archive) {
                $source->addType($baseQuery, Type\PostArchiveQueryType::class, $type);
            }
        }

        foreach (get_taxonomies($arguments, 'objects') as $name => $taxonomy) {

            if (!$taxonomy->rest_base) {
                continue;
            }

            $baseQuery = Str::camelCase([$taxonomy->rest_base, 'Query'], true);

            $source->addType(Str::camelCase($name, true), Type\TaxonomyType::class, $taxonomy);
            $source->addType(Str::camelCase([$name, 'Fields'], true), Type\FieldsType::class, 'term', $name);
            $source->addType('RootQuery', Type\TaxonomyQueryType::class, $taxonomy);
            $source->addType($baseQuery, Type\TaxonomyArchiveQueryType::class, $taxonomy);

            if ($taxonomy->hierarchical) {
                $source->addType($baseQuery, Type\CustomTaxonomyQueryType::class, $taxonomy);
            }

            foreach ($taxonomy->object_type as $type) {
                $source->addType(Str::camelCase($type, true), Type\TermType::class, $taxonomy);
            }
        }
    }

    public static function initCustomizer(Config $config)
    {
        $args = [
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
        ];

        foreach (get_post_types($args, 'objects') as $name => $type) {

            if (!$type->rest_base || $name === $type->rest_base) {
                continue;
            }

            $templates["single-{$name}"] = [
                'label' => "Single {$type->labels->singular_name}",
            ];

            if ($taxes = get_object_taxonomies($name)) {

                $label_lower = strtolower($type->labels->name);

                $templates["single-{$name}"] += [

                    'fieldset' => [
                        'default' => [
                            'fields' => [
                                'terms' => [
                                    'label' => 'Limit by Terms',
                                    'description' => "Select the terms where the template is applied to the {$label_lower}. {$type->labels->name} from child terms are not included. Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple terms.",
                                    'type' => 'select-term',
                                    'taxonomies' => $taxes,
                                    'default' => [],
                                    'attrs' => [
                                        'multiple' => true,
                                        'class' => 'uk-height-medium uk-resize-vertical',
                                    ],
                                ],
                            ],
                        ],
                    ],

                ];

            }

            if ($name === 'post' || $type->has_archive) {

                $templates["archive-{$name}"] = [
                    'label' => "{$type->label} Archive",
                ];

            }

        }

        foreach (get_taxonomies($args, 'objects') as $name => $taxonomy) {

            $terms = [];

            foreach (static::getTerms($taxonomy) as $label => $id) {
                $terms[] = [$id, $label];
            }

            $taxonomies[$name] = [
                'label' => $taxonomy->label,
                'terms' => $terms,
            ];

            $label_lower = strtolower($taxonomy->labels->name);
            $has_archive = $taxonomy->hierarchical ? "Child {$label_lower} are not included." : '';

            $templates["taxonomy-{$name}"] = [

                'label' => "{$taxonomy->labels->singular_name} Archive",
                'fieldset' => [
                    'default' => [
                        'fields' => [
                            'terms' => [
                                'label' => $taxonomy->label,
                                'description' => "Select the {$label_lower} to which the template is applied. {$has_archive} Use the <kbd>shift</kbd> or <kbd>ctrl/cmd</kbd> key to select multiple {$label_lower}.",
                                'type' => 'select-term',
                                'taxonomy' => $name,
                                'default' => [],
                                'attrs' => [
                                    'multiple' => true,
                                        'class' => 'uk-height-small uk-resize-vertical',
                                ],
                            ],
                        ],
                    ],
                ],

            ];

        }

        $config->add('customizer.templates', $templates);
        $config->add('customizer.taxonomies', $taxonomies);
    }

    public static function addPostTypeFilter(Request $request, $query)
    {
        if ($post_type = $request->getParam('post_type')) {
            return ['post_type' => [$post_type]] + $query;
        }

        return $query;
    }

    protected static function getTerms($taxonomy)
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
        ]);

        if ($taxonomy->hierarchical) {
            $terms = _get_term_children(0, $terms, $taxonomy->name);
        }

        $result = [];

        foreach ($terms as $term) {
            $result[str_repeat('- ', count(get_ancestors($term->term_id, $term->taxonomy))) . $term->name] = $term->term_id;
        }

        return $result;
    }
}
