<?php

namespace YOOtheme\Builder\Wordpress\Source\Listener;

class LoadTemplateUrl
{
    public function __construct()
    {
        add_filter('get_archives_link', [$this, 'getArchivesLink'], 10, 4);
    }

    public function handle(array $template): array
    {
        $type = $template['type'] ?? '';

        if (str_starts_with($type, 'single-') && ($posts = $this->getPosts($template))) {
            $template['url'] = get_permalink($posts[0]);
        } elseif (str_starts_with($type, 'taxonomy-') && ($terms = $this->getTerms($template))) {
            $template['url'] = get_term_link($terms[0], substr($type, 9));
        } elseif ($type === 'date-archive' && ($archives = $this->getArchives($template))) {
            $template['url'] = html_entity_decode($archives);
        } elseif ($type === 'author-archive') {
            $template['url'] = get_author_posts_url(get_current_user_id());
        } elseif (str_starts_with($type, 'archive-')) {
            $template['url'] = get_post_type_archive_link(substr($type, 8));
        } elseif ($type === 'search') {
            $template['url'] = get_search_link();
        } elseif ($type === 'error-404') {
            $template['url'] = home_url('index.php?p=-1');
        }

        return $template;
    }

    public function getArchivesLink($link_html, $url, $text, $format): string
    {
        return $format === 'url' ? $url : $link_html;
    }

    protected function getArchives(array $template): string
    {
        $type = $template['query']['archive'] ?? '';
        $types = [
            'day' => 'daily',
            'month' => 'monthly',
            'year' => 'yearly',
        ];

        return $type !== 'time'
            ? wp_get_archives([
                'type' => $types[$type] ?? '',
                'echo' => false,
                'limit' => 1,
                'format' => 'url',
            ])
            : '';
    }

    protected function getTerms(array $template): array
    {
        $args = [
            'number' => 1,
            'fields' => 'ids',
            'taxonomy' => substr($template['type'], 9),
        ];

        $templateTerms = $template['query']['terms'] ?? [];
        $includeChildren = $template['query']['include_children'] ?? false;

        if ($templateTerms && $includeChildren === 'only') {
            $terms = [];
            foreach ($templateTerms as $termId) {
                $args += ['child_of' => $termId];
                $terms = get_terms($args);
                if (!empty($terms)) {
                    break;
                }
            }
        } else {
            $args += ['include' => $templateTerms];
            $terms = get_terms($args);
        }

        return is_array($terms) ? $terms : [];
    }

    protected function getPosts(array $template): array
    {
        $args = [
            'post_type' => substr($template['type'], 7),
            'limit' => 1,
        ];

        if ($posts_page = get_option('page_for_posts')) {
            $args['exclude'] = [$posts_page];
        }

        if ($terms = $template['query']['terms'] ?? []) {
            $args['terms'] = $terms;

            foreach ($template['query'] as $key => $value) {
                if (str_ends_with($key, '_include_children')) {
                    $args[$key] = $value;
                }
            }
        }

        return get_posts($args);
    }
}
