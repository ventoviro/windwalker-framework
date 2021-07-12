<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filesystem;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use UnexpectedValueException;
use Windwalker\Filesystem\Exception\FilesystemException;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Str;

/**
 * A Path handling class
 *
 * @since  2.0
 */
class Path
{
    public const CASE_OS_DEFAULT = 1;

    public const CASE_SENSITIVE = 2;

    public const CASE_INSENSITIVE = 3;

    /**
     * Chmods files and directories recursively to given permissions.
     *
     * @param  string  $path        Root path to begin changing mode [without trailing slash].
     * @param  string  $filemode    Octal representation of the value to change file mode to [null = no change].
     * @param  string  $foldermode  Octal representation of the value to change folder mode to [null = no change].
     *
     * @return  boolean  True if successful [one fail means the whole operation failed].
     *
     * @since   2.0
     */
    public static function setPermissions(string $path, $filemode = '0644', $foldermode = '0755'): bool
    {
        // Initialise return value
        $ret = true;

        if (is_dir($path)) {
            $dh = opendir($path);

            while ($file = readdir($dh)) {
                if ($file !== '.' && $file !== '..') {
                    $fullpath = $path . '/' . $file;

                    if (is_dir($fullpath)) {
                        if (!self::setPermissions($fullpath, $filemode, $foldermode)) {
                            $ret = false;
                        }
                    } elseif (isset($filemode)) {
                        if (!@ chmod($fullpath, octdec($filemode))) {
                            $ret = false;
                        }
                    }
                }
            }

            closedir($dh);

            if (isset($foldermode) && !@ chmod($path, octdec($foldermode))) {
                $ret = false;
            }
        } elseif (isset($filemode)) {
            $ret = @ chmod($path, octdec($filemode));
        }

        return $ret;
    }

    /**
     * Get the permissions of the file/folder at a give path.
     *
     * @param  string   $path      The path of a file/folder.
     * @param  boolean  $toString  Convert permission number to string.
     *
     * @return  string  Filesystem permissions.
     *
     * @since   2.0
     */
    public static function getPermissions(string $path, bool $toString = false): string
    {
        $path = self::clean($path);
        $mode = @ decoct(@ fileperms($path) & 0777);

        if (!$toString) {
            return $mode;
        }

        if (strlen($mode) < 3) {
            return '---------';
        }

        $parsedMode = '';

        for ($i = 0; $i < 3; $i++) {
            // Read
            $parsedMode .= ($mode[$i] & 04) ? 'r' : '-';

            // Write
            $parsedMode .= ($mode[$i] & 02) ? 'w' : '-';

            // Execute
            $parsedMode .= ($mode[$i] & 01) ? 'x' : '-';
        }

        return $parsedMode;
    }

    /**
     * Function to strip additional / or \ in a path name.
     *
     * @param  string  $path  The path to clean.
     * @param  string  $ds    Directory separator (optional).
     *
     * @return  string  The cleaned path.
     *
     * @throws  UnexpectedValueException If $path is not a string.
     * @throws  InvalidArgumentException
     * @since   2.0
     */
    public static function clean(string $path, string $ds = DIRECTORY_SEPARATOR): string
    {
        if ($path === '') {
            return $path;
        }

        $prefix = '';

        if (str_contains($path, '://')) {
            $extracted = explode('://', $path, 2);

            if (count($extracted) === 1) {
                return $extracted[0];
            }

            $prefix = $extracted[0] . '://';
            $path = $extracted[1];
        } elseif (preg_match('/(\w+):[\/\\\\](.*)/', $path, $matches)) {
            if ($matches[2] === '') {
                return $path;
            }

            $prefix = $matches[1] . ':' . $ds;
            $path = $matches[2];
        }

        $path = trim($path, ' ');

        if (($ds === '\\') && ($path[0] === '\\') && ($path[1] === '\\')) {
            // Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
            // If dealing with a UNC path don't forget to prepend the path with a backslash.
            $path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
        } else {
            $path = (string) preg_replace('#[/\\\\]+#', $ds, $path);
        }

        return $prefix . $path;
    }

    /**
     * Normalize a path. This method will do clean() first to replace slashes and remove '..' to create a
     * Clean path. Unlike realpath(), if this path not exists, normalise() will still return this path.
     *
     * @param  string  $path  The path to normalize.
     * @param  string  $ds    Directory separator (optional).
     *
     * @return  string  The normalized path.
     *
     * @since   2.0.4
     */
    public static function normalize(string $path, string $ds = DIRECTORY_SEPARATOR): string
    {
        $parts = [];
        $path = static::clean($path, $ds);
        $segments = explode($ds, $path);

        foreach ($segments as $segment) {
            if ($segment !== '.') {
                $test = array_pop($parts);

                if (null === $test) {
                    $parts[] = $segment;
                } elseif ($segment === '..') {
                    if ($test === '..') {
                        $parts[] = $test;
                    }

                    if ($test === '..' || $test === '') {
                        $parts[] = $segment;
                    }
                } else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }

        return rtrim(implode($ds, $parts), '.' . DIRECTORY_SEPARATOR);
    }

    /**
     * Check file exists and also the filename cases.
     *
     * @param  string  $path       The file path to check.
     * @param  int     $sensitive  Sensitive file name case.
     *
     * @return  bool
     * @throws UnexpectedValueException
     */
    public static function exists(string $path, int $sensitive = self::CASE_OS_DEFAULT): bool
    {
        if ($sensitive === static::CASE_OS_DEFAULT) {
            return file_exists($path);
        }

        $path = static::normalize($path, DIRECTORY_SEPARATOR);
        $it = Filesystem::items(dirname($path));

        if (static::CASE_INSENSITIVE === $sensitive) {
            $lowerfile = strtolower($path);

            foreach ($it as $file) {
                if (strtolower($file->getPathname()) === $lowerfile) {
                    return true;
                }
            }
        } else {
            foreach ($it as $file) {
                if ($file->getPathname() === $path) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Fix a path with correct file name cases.
     *
     * @param  string  $path
     *
     * @return  string
     */
    public static function fixCase(string $path): string
    {
        $path = static::normalize($path, DIRECTORY_SEPARATOR);

        $lowerfile = strtolower($path);

        foreach (glob(dirname($path) . DIRECTORY_SEPARATOR . '*') as $file) {
            if (strtolower($file) === $lowerfile) {
                return $file;
            }
        }

        return $path;
    }

    /**
     * stripTrailingDot
     *
     * @param  string  $path
     * @param  string  $ds
     *
     * @return  string
     */
    public static function stripTrailingDot(string $path, string $ds = DIRECTORY_SEPARATOR): string
    {
        return Str::removeRight($path, $ds . '.');
    }

    /**
     * Strips the last extension off of a file name
     *
     * @param  string  $file  The file name
     *
     * @return  string  The file name without the extension
     *
     * @since   2.0
     */
    public static function stripExtension(string $file): string
    {
        return preg_replace('#\.[^.]*$#', '', $file);
    }

    /**
     * getExtension
     *
     * @param  string  $file  The file path to get extension.
     *
     * @return  string  The ext of file path.
     *
     * @since   2.0
     */
    public static function getExtension(string $file): string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * Get file name from a path.
     *
     * @param  string  $path  The file path to get basename.
     *
     * @return  string  The file name.
     *
     * @since   2.0
     */
    public static function getFilename(string $path): string
    {
        $name = pathinfo($path, PATHINFO_FILENAME);

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ($ext) {
            $name .= '.' . $ext;
        }

        return $name;
    }

    /**
     * Makes the file name safe to use
     *
     * @param  string  $file        The name of the file [not full path]
     * @param  array   $stripChars  Array of regex (by default will remove any leading periods)
     *
     * @return  string  The sanitised string
     *
     * @since   2.0
     */
    public static function makeSafe(string $file, array $stripChars = ['#^\.#']): string
    {
        $regex = array_merge(['#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#'], $stripChars);

        $file = preg_replace($regex, '', $file);

        // Remove any trailing dots, as those aren't ever valid file names.
        return rtrim($file, '.');
    }

    /**
     * Make file name safe with UTF8 name.
     *
     * @param  string  $file  The file name.
     *
     * @return  false|string
     *
     * @since  3.4.5
     */
    public static function makeUtf8Safe(
        string $file
    ): bool|string {
        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);

        return mb_ereg_replace("([\.]{2,})", '', $file);
    }

    /**
     * Returns whether a path is absolute.
     *
     * @param  string  $path  A path string.
     *
     * @return bool Returns true if the path is absolute, false if it is
     *              relative or empty.
     */
    public static function isAbsolute(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        // Strip scheme
        if (false !== ($pos = strpos($path, '://'))) {
            $path = substr($path, $pos + 3);
        }

        // UNIX root "/" or "\" (Windows style)
        if ('/' === $path[0] || '\\' === $path[0]) {
            return true;
        }

        // Windows root
        if (strlen($path) > 1 && ctype_alpha($path[0]) && ':' === $path[1]) {
            // Special case: "C:"
            if (2 === strlen($path)) {
                return true;
            }

            // Normal case: "C:/ or "C:\"
            if ('/' === $path[2] || '\\' === $path[2]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether a path is relative.
     *
     * @param  string  $path  A path string.
     *
     * @return bool Returns true if the path is relative or empty, false if
     *              it is absolute.
     *
     * @since 1.0 Added method.
     * @since 2.0 Method now fails if $path is not a string.
     */
    public static function isRelative(string $path): bool
    {
        return !static::isAbsolute($path);
    }

    /**
     * Find root. if in xUNIX system will return `/`, if in Windows will return disk root name.
     *
     * @param  string  $path
     *
     * @return  string
     */
    public static function findRoot(string $path): string
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return '/';
        }

        if (!static::isAbsolute($path)) {
            throw new FilesystemException("Finding root from a relative path: \"$path\".");
        }

        $paths = explode(DIRECTORY_SEPARATOR, static::normalize($path));

        $root = array_shift($paths);

        return Str::ensureRight($root, DIRECTORY_SEPARATOR);
    }

    /**
     * Method to find the relative path from a given path to another path based on the current working directory.
     * If both the given paths are the same, it would resolve to a zero-length string.
     * This method is a fork from Node.js but without case check.
     *
     * @see https://github.com/nodejs/node-v0.x-archive/blob/master/lib/path.js#L504-L530
     * @see https://github.com/nodejs/node-v0.x-archive/blob/master/lib/path.js#L265-L304
     *
     * Path::relative('/root/path', '/root/path/images/a.jpg') => `images/a.jpg`
     *
     * @param  string  $from
     * @param  string  $to
     *
     * @return  string
     */
    public static function relative(string $from, string $to): string
    {
        $to = static::normalize($to);
        $from = static::normalize($from);

        $fromParts = Arr::explodeAndClear(DIRECTORY_SEPARATOR, $from);
        $toParts = Arr::explodeAndClear(DIRECTORY_SEPARATOR, $to);

        $length = min(count($fromParts), count($toParts));
        $samePartsLength = $length;

        for ($i = 0; $i < $length; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                $samePartsLength = $i;
                break;
            }
        }

        $outputParts = [];
        $fromPartsLength = count($fromParts);

        for ($k = $samePartsLength; $k < $fromPartsLength; $k++) {
            $outputParts[] = '..';
        }

        $outputParts = array_merge($outputParts, array_slice($toParts, $samePartsLength));

        return implode(DIRECTORY_SEPARATOR, $outputParts);
    }
}
