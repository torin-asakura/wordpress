<?php

namespace YOOtheme\Builder\Wordpress;

use YOOtheme\Builder;
use YOOtheme\Config;
use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use YOOtheme\Path;
use YOOtheme\View;

class ContentListener
{
    const PATTERN = '/<!--\s?(\{(?:.*?)\})\s?-->/';

    public static function onTemplateInclude(Builder $builder, Config $config, View $view, $template)
    {
        if (!self::isPage()) {
            return $template;
        }

        global $post;

        if (post_password_required($post)) {
            return $template;
        }

        $content = isset($post->post_content) ? self::matchContent($post->post_content) : false;

        if ($config('app.isCustomizer')) {

            if ($page = get_theme_mod('page')) {
                if ($post->ID === $page['id']) {
                    $content = json_encode($page['content']);
                } else {
                    unset($page);
                }
            }

            $modified = !empty($page);

            $config->add('customizer.page', [
                'id' => $post->ID,
                'title' => $post->post_title,
                'content' => $content ? $builder->load($content) : $content,
                'modified' => $modified,
                'modifiedDate' => $modified ? $page['modifiedDate'] : self::getModifiedDate($post),
                'contentHash' => $modified ? $page['contentHash'] : self::getContentHash($post),
                'collision' => $modified ? self::getCollision($page['contentHash'], $post) : false,
            ]);

        }

        // Render builder output
        if ($content) {
            $view['sections']->set('builder', $builder->render($content, ['prefix' => 'page', 'post' => $post]));
        }

        return $template;
    }

    public static function onLateTemplateInclude(Config $config, View $view, $template)
    {
        if ($view['sections']->exists('builder')) {
            $config->set('app.isBuilder', true);
            return Path::get('~theme/page.php');
        }

        return $template;
    }

    public static function onPrePostContent($content)
    {
        // Prevent content filters from corrupting builder JSON in post_content on save.
        if (self::matchContent($content)) {
            if (is_callable('kses_remove_filters')) {
                kses_remove_filters();
            }

            if (is_callable('wp_remove_targeted_link_rel_filters')) {
                wp_remove_targeted_link_rel_filters();
            }
        }

        return $content;
    }

    public static function savePage(Request $request, Response $response, Builder $builder)
    {
        $request->abortIf(!$page = $request('page'), 400)
                ->abortIf(!$page = base64_decode($page), 400)
                ->abortIf(!$page = json_decode($page), 400)
                ->abortIf(!current_user_can('edit_post', $page->id), 403, 'Insufficient User Rights.');

        if (!$request('overwrite') and $collision = self::getCollision($page->contentHash, get_post($page->id))) {
            return $response->withJSON(compact('collision'));
        }

        $isEmpty = empty((array) $page->content);
        $content = json_encode($page->content);
        $updated = wp_update_post([
            'ID' => $page->id,
            'post_content' => !$isEmpty ? wp_slash("{$builder->withParams(['context' => 'content'])->render($content)}\n<!-- {$content} -->") : '',
        ], true) and update_post_meta($page->id, '_edit_last', get_current_user_id());

        $request->abortIf(is_wp_error($updated), 500);

        $post = get_post($page->id);

        return $response->withJSON([
            'id' => $page->id,
            'contentHash' => self::getContentHash($post),
            'modifiedDate' => self::getModifiedDate($post),
        ]);
    }

    protected static function getCollision($hash, $post)
    {
        if ($hash !== ($contentHash = self::getContentHash($post))) {

            $author = self::getModifiedAuthor($post);
            $modifiedBy = $author->data->display_name;
            $modifiedDate = self::getModifiedDate($post);

            return compact('modifiedBy', 'modifiedDate', 'contentHash');
        }

        return false;
    }

    protected static function getContentHash($post)
    {
        return md5($post->post_content);
    }

    protected static function getModifiedDate($post)
    {
        // checking if modified date is present to avoid regression (not sure if necessary)
        return @$post->post_modified ? strtotime($post->post_modified) : time();
    }

    protected static function getModifiedAuthor($post)
    {
        $userId = get_post_meta($post->ID, '_edit_last', true) or
        $revs = wp_get_post_revisions($post->ID) and $lastRev = end($revs) and $userId = $lastRev->post_author;

        return get_userdata($userId);
    }

    protected static function matchContent($content)
    {
        return $content && strpos($content, '<!--') !== false && preg_match(self::PATTERN, $content, $matches) ? $matches[1] : null;
    }

    protected static function isPage()
    {
        return is_page() || is_single() && get_post_type() === 'post';
    }
}
