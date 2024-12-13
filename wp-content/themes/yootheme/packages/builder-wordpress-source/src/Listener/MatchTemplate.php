<?php

namespace YOOtheme\Builder\Wordpress\Source\Listener;

use YOOtheme\Arr;
use YOOtheme\Config;

class MatchTemplate
{
    public Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function handle($tpl)
    {
        global $post;

        $locale = $this->getLocale($this->config->get('locale.code'));

        // Will match in following order
        // category, tag, taxonomy, posts page, single post, page, or author
        // from query variable
        $object = get_queried_object();
        $pages = get_query_var('paged') && get_query_var('paged') !== 1 ? 'except_first' : 'first';

        if ($tpl === get_index_template()) {
            return [
                'type' => 'archive-post',
                'query' => compact('pages', 'locale'),
            ];
        }

        if (is_tax() || is_category() || is_tag()) {
            return [
                'type' => "taxonomy-{$object->taxonomy}",
                'query' => [
                    'terms' => static::getTaxonomyTermsFn($object),
                    'terms_filter' => static::getTaxonomyTermsFilterFn(),
                    'pages' => $pages,
                    'locale' => $locale,
                ],
            ];
        }

        if (is_post_type_archive()) {
            return [
                'type' => "archive-{$object->name}",
                'query' => compact('pages', 'locale'),
            ];
        }

        if (is_author()) {
            return [
                'type' => 'author-archive',
                'query' => compact('pages', 'locale'),
            ];
        }

        if (is_date()) {
            return [
                'type' => 'date-archive',
                'query' => [
                    'archive' => is_time()
                        ? 'time'
                        : (is_day()
                            ? 'day'
                            : (is_month()
                                ? 'month'
                                : 'year')),
                    'pages' => $pages,
                    'locale' => $locale,
                ],
            ];
        }

        if (is_search()) {
            return [
                'type' => 'search',
                'query' => compact('pages', 'locale'),
            ];
        }

        if (is_404()) {
            return [
                'type' => 'error-404',
                'query' => compact('locale'),
            ];
        }

        // For WooCommerce's shop page `is_page() => true` if assigned as homepage
        if (is_page() || is_single()) {
            return [
                'type' => "single-{$post->post_type}",
                'query' => !post_password_required($post)
                    ? [
                        'terms' => $this->getSingleTermsFn($post),
                        'locale' => $locale,
                    ]
                    : fn() => false,
            ];
        }
    }

    protected function getLocale($locale)
    {
        if (str_contains($locale, '_')) {
            // Fallback to language code, if e.g. WPML changed the locale
            $locale = [$locale, substr($locale, 0, strpos($locale, '_'))];
        }

        return $locale;
    }

    protected function getSingleTermsFn($post): \Closure
    {
        $postTaxonomies = get_object_taxonomies($post);
        $postTermIds = array_column(wp_get_object_terms($post->ID, $postTaxonomies), 'term_id');

        return function ($tmplTermIds, $query) use ($postTermIds, $postTaxonomies): bool {
            $includeChildren = Arr::some(
                $postTaxonomies,
                fn($taxonomy) => $query["{$taxonomy}_include_children"] ?? false,
            );

            // Bail early if no taxonomy is set to include children
            if (!$includeChildren && !array_intersect($postTermIds, $tmplTermIds)) {
                return false;
            }

            $terms = get_terms(['include' => $tmplTermIds, 'hide_empty' => false]);
            $taxonomies = array_unique(array_column($terms, 'taxonomy'));
            foreach ($terms as $term) {
                $match = in_array($term->term_id, $postTermIds);
                $includeChildren = $query["{$term->taxonomy}_include_children"] ?? false;

                if ($match && $includeChildren === 'only') {
                    continue;
                }

                if (
                    $match ||
                    ($includeChildren &&
                        Arr::some(
                            $postTermIds,
                            fn($id) => term_is_ancestor_of($term->term_id, $id, $term->taxonomy),
                        ))
                ) {
                    unset($taxonomies[array_search($term->taxonomy, $taxonomies)]);

                    if (empty($taxonomies)) {
                        return true;
                    }
                }
            }
            return empty($taxonomies);
        };
    }

    protected function getTaxonomyTermsFn($term): \Closure
    {
        return function ($termIds, $query) use ($term): bool {
            $includeChildren = $query['include_children'] ?? false;

            $match = in_array($term->term_id, $termIds);

            if (!$includeChildren || ($match && $includeChildren === 'include')) {
                return $match;
            }

            if ($match && $includeChildren === 'only') {
                return false;
            }

            foreach ($termIds as $id) {
                if (term_is_ancestor_of($id, $term->term_id, $term->taxonomy)) {
                    return true;
                }
            }
            return false;
        };
    }

    protected function getTaxonomyTermsFilterFn(): \Closure
    {
        return function ($terms): bool {
            global $wp_query;

            if (empty($wp_query->tax_query->queried_terms)) {
                return false;
            }

            foreach ($terms as $id) {
                $term = get_term($id);

                if (!$term) {
                    continue;
                }

                $queried = $wp_query->tax_query->queried_terms[$term->taxonomy] ?? null;
                foreach ($queried['terms'] ?? [] as $t) {
                    $t = get_term_by($queried['field'], $t, $term->taxonomy);

                    if ($t && $t->term_id === $term->term_id) {
                        return true;
                    }
                }
            }
            return false;
        };
    }
}
