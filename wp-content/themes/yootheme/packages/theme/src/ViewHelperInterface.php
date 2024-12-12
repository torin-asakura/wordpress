<?php

namespace YOOtheme\Theme;

interface ViewHelperInterface
{
    public function social($link);

    /**
     * @param string $link
     * @param array $params
     * @param bool $defaults
     *
     * @return false|string
     */
    public function iframeVideo($link, $params = [], $defaults = true);

    public function isYouTubeShorts($link);

    public function uid();

    public function isVideo($link);

    /**
     * @param string|array $url
     * @param array $attrs
     *
     * @return string
     */
    public function image($url, array $attrs = []);

    /**
     * @param string $url
     * @param array $params
     *
     * @return array
     */
    public function bgImage($url, array $params = []);

    public function isImage($link);

    public function isAbsolute($url): bool;

    public function parallaxOptions(
        $params,
        $prefix = '',
        $props = ['x', 'y', 'scale', 'rotate', 'opacity', 'blur', 'background']
    );

    public function striptags(
        $str,
        $allowable_tags = '<div><h1><h2><h3><h4><h5><h6><p><ul><ol><li><img><svg><br><hr><span><strong><em><i><b><s><mark><sup><del>'
    ): string;

    /**
     * @param string $margin
     */
    public function margin($margin): ?string;
}
