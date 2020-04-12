<?php

namespace YOOtheme;

/**
 * A static class which provides utilities for working with directory paths.
 */
abstract class Path
{
    /**
     * @var array
     */
    protected static $aliases = [];

    /**
     * Gets a absolute path by resolving aliases and current directory.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * Path::get('~app/dir');
     * // => /app/dir
     */
    public static function get($path)
    {
        $path = static::resolveAlias($path);

        // path starts not with ./ or ../
        if (!preg_match('/^\.\.?(?:\/.*)?$/', $path)) {
            return $path;
        }

        // get caller file
        $file = Reflection::getCaller('file');

        return static::join(dirname($file), $path);
    }

    /**
     * Sets a path alias.
     *
     * @param string $alias
     * @param string $path
     *
     * @example
     * Path::setAlias('~app', '/app');
     *
     * Path::resolveAlias('~app/resource');
     * // => /app/resource
     */
    public static function setAlias($alias, $path)
    {
        if (substr($alias, 0, 1) !== '~') {
            throw new \InvalidArgumentException("The alias '{$alias}' must start with ~");
        }

        $path = rtrim(static::resolveAlias($path), '/');
        $alias = rtrim(strtr($alias, '\\', '/'), '/');

        list($name) = explode('/', $alias, 2);

        static::$aliases[$name]["$alias/"] = "$path/";
    }

    /**
     * Resolve a path with alias.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * Path::setAlias('~app', '/app');
     *
     * Path::resolveAlias('~app/resource');
     * // => /app/resource
     */
    public static function resolveAlias($path)
    {
        $path = strtr($path, '\\', '/');
        $trim = substr($path, -1) !== '/';

        list($name) = explode('/', $path, 2);

        if (substr($name, 0, 1) !== '~') {
            return $path;
        }

        $path = Event::emit("path {$name}|filter", $path, substr($path, strlen($name)));

        if (isset(static::$aliases[$name])) {
            $path = strtr($trim ? "{$path}/" : $path, static::$aliases[$name]);
        }

        return $trim ? rtrim($path, '/') : $path;
    }

    /**
     * Resolves a sequence of paths or path segments into an absolute path. All path segments are processed from right to left.
     *
     * @param array $paths
     *
     * @return string
     *
     * @example
     * Path::resolve('~app/dir/dir', '../resource');
     * // => /app/dir/resource
     */
    public static function resolve(...$paths)
    {
        $parts = [];

        foreach (array_reverse($paths) as $path) {

            $path = static::resolveAlias($path);

            array_unshift($parts, $path);

            if (static::isAbsolute($path)) {
                break;
            }
        }

        return static::join(...$parts);
    }

    /**
     * Returns trailing name component of path.
     *
     * @param string $path
     * @param string $suffix
     *
     * @return string
     *
     * @example
     * Path::basename('~app/dir/file.php');
     * // => file.php
     */
    public static function basename($path, $suffix = null)
    {
        return basename(static::resolveAlias($path), $suffix);
    }

    /**
     * Returns the extension of the path.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * Path::extname('~app/dir/file.php');
     * // => .php
     */
    public static function extname($path)
    {
        $basename = static::basename($path);
        $position = strrpos($basename, '.');

        return $position ? substr($basename, $position) : '';
    }

    /**
     * Returns a parent directory's path.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * Path::dirname('~app/dir/file.php');
     * // => /app/dir
     */
    public static function dirname($path)
    {
        return dirname(static::resolveAlias($path));
    }

    /**
     * Gets the relative path to a given base path.
     *
     * @param string $from
     * @param string $to
     *
     * @return string
     *
     * @example
     * Path::relative('/path/dir/test/aaa', '/path/dir/impl/bbb');
     * // => ../../impl/bbb
     */
    public static function relative($from, $to)
    {
        $from = static::resolveAlias($from);
        $to = static::resolveAlias($to);

        $_from = static::parse($from);
        $_to = static::parse($to);

        if ($to === '') {
            return $from;
        }

        if ($from === '') {
            throw new \InvalidArgumentException("The absolute path '{$to}' can\'t be made relative to the path '{$from}'.");
        }

        if ($_from['root'] === '' && $_to['root'] !== '') {
            throw new \InvalidArgumentException("The absolute path '{$to}' can\'t be made relative to the relative path '{$from}'.");
        }

        if ($_from['root'] && $_from['root'] !== $_to['root']) {
            throw new \InvalidArgumentException("The path '{$to}' can\'t be made relative to '{$from}', because the roots are different ('{$_from['root']}' and '{$_to['root']}').");
        }

        $fromParts = explode('/', $_from['pathname']);
        $toParts = explode('/', $_to['pathname']);

        $match = true;
        $prefix = '';

        foreach ($fromParts as $i => $fromPart) {

            if ($match && isset($toParts[$i]) && $fromPart === $toParts[$i]) {
                unset($toParts[$i]);
                continue;
            }

            $match = false;
            $prefix .= '../';
        }

        return rtrim($prefix . join('/', $toParts), '/');
    }

    /**
     * Normalizes a path, resolving '..' and '.' segments.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * Path::normalize('/path1/.././file.txt');
     * // => /file.txt
     */
    public static function normalize($path)
    {
        static $cache;

        if (!$path) {
            return '';
        }

        if (isset($cache[$path])) {
            return $cache[$path];
        }

        $parts = [];
        $parsed = static::parse($path);

        foreach (explode('/', $parsed['pathname']) as $part) {

            if ('.' === $part || '' === $part) {
                continue;
            }

            if ($part === '..' && $parts && $parts[count($parts) - 1] !== '..') {
                array_pop($parts);
                continue;
            }

            if ($part !== '..' || $parsed['root'] === '') {
                $parts[] = $part;
            }
        }

        return $cache[$path] = $parsed['root'] . join('/', $parts);
    }

    /**
     * Joins all given path segments together.
     *
     * @param array $parts
     *
     * @return string
     *
     * @example
     * Path::join('/foo', '/bar', 'baz/asdf', 'quux', '..');
     * // => /foo/bar/baz/asdf
     */
    public static function join(...$parts)
    {
        return static::normalize(join('/', $parts));
    }

    /**
     * Returns information about a path.
     *
     * @param string $path
     *
     * @return array
     *
     * @example
     * Path::parse('/foo/file.txt');
     * // => ['root' => '/', 'pathname' => 'foo/file.txt', 'dirname' => '/foo', 'basename' => 'file.txt', 'filename' => 'file', 'extension' => 'txt']
     */
    public static function parse($path)
    {
        $path = strtr($path, '\\', '/');
        $root = (string) static::root($path);
        $pathname = substr($path, 0, strlen($root)) === $root
            ? (string) substr($path, strlen($root))
            : $path;

        $parts = [
            'root' => $root,
            'pathname' => $pathname,
            'dirname' => '',
            'basename' => '',
            'filename' => '',
            'extension' => '',
        ];

        return array_merge($parts, pathinfo($path));
    }

    /**
     * Checks if path is absolute.
     *
     * @param string $path
     *
     * @return bool
     *
     * @example
     * Path::isAbsolute('/foo/file.txt');
     * // => true
     */
    public static function isAbsolute($path)
    {
        return (bool) static::root($path);
    }

    /**
     * Checks if path is relative.
     *
     * @param string $path
     *
     * @return bool
     *
     * @example
     * Path::isRelative('foo/file.txt');
     * // => true
     */
    public static function isRelative($path)
    {
        return (bool) !static::root($path);
    }

    /**
     * Returns path root.
     *
     * @param string $path
     *
     * @return mixed
     */
    public static function root($path)
    {
        $path = strtr($path, ['\\' => '/']);

        if ($path && $path[0] === '/') {
            return '/';
        }

        if (strpos($path, ':') && \preg_match('/^([a-z]*:)?(\/\/|\/)/i', $path, $matches)) {
            return $matches[0];
        }
    }
}
