<?php

namespace YOOtheme;

class UrlResolver
{
    public static function resolve(Config $config, $path, $parameters, $secure, callable $next)
    {
        $root = $config('app.rootDir');
        $file = Path::resolveAlias($path);

        if (Str::startsWith($file, $root)) {
            $path = ltrim(substr($file, strlen($root)), '/');
        }

        return $next($path, $parameters, $secure);
    }
}
