<?php

namespace YOOtheme\Theme;

use Joomla\CMS\Document\Document;
use YOOtheme\Config;
use YOOtheme\Metadata;
use YOOtheme\Path;

class HighlightListener
{
    public static function checkContent(Config $config, Metadata $metadata, $content)
    {
        if ($highlight = $config('~theme.highlight') and strpos($content, '</code>')) {
            $metadata->set('style:highlight', ['href' => Path::get("../assets/styles/{$highlight}.css"), 'defer' => true]);
            $metadata->set('script:highlight', ['src' => Path::get('../assets/highlight.js'), 'defer' => true]);
            $metadata->set('script:highlight-init', 'UIkit.util.ready(function() {hljs.initHighlightingOnLoad()});', ['defer' => true]);
        }

        return $content;
    }

    public static function beforeRender(Config $config, Metadata $metadata, Document $document)
    {
        static::checkContent($config, $metadata, $document->getBuffer('component'));
    }
}
