<?php

namespace YOOtheme\Wordpress;

use YOOtheme\Url;

class Router
{
    /**
     * {@inheritdoc}
     */
    public static function generate($pattern = '', array $parameters = [], $secure = null)
    {
        if ($pattern !== '') {

            $search = [];

            foreach ($parameters as $key => $value) {
                $search[] = '#:' . preg_quote($key, '#') . '(?!\w)#';
            }

            $pattern = preg_replace($search, $parameters, $pattern);
            $pattern = preg_replace('#\(/?:.+\)|\(|\)|\\\\#', '', $pattern);

            $parameters = array_merge(['p' => $pattern], $parameters);
        }

        return Url::to(admin_url() . 'admin-ajax.php?action=kernel', $parameters, $secure);
    }
}
