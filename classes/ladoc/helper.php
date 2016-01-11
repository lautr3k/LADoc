<?php
/**
 * LADoc - Language Agnostic Documentator.
 *
 * @license   GPL
 * @version   1.0.0
 * @source    https://github.com/lautr3k/LitDoc
 * @copyright 2016 © Onl'Fait (http://www.onlfait.ch)
 * @author    Sébastien Mischler (skarab) <sebastien@onlfait.ch>
 * @namespace LADoc
 */
namespace LADoc;

/**
 * Collection of static convenience methods.
 *
 * @class Helper
 */
class Helper
{
    /**
     * Return a path where all path separators was replaced by an url separator,
     * and the starting and trailing url separator removed.
     *
     * @static
     * @method normalizePath
     * @param  string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        return trim(preg_replace('|[\\\\/]+|', '/', $path), '/');
    }

    /**
     * Return a normalized and absolute path if path exist
     * or the normalized provided path if `$strict = false`
     * otherwise an error is raised.
     *
     * @static
     * @method absolutePath
     * @param  string  $path
     * @param  boolean [$strict=true] Throw an exception if does not exist.
     * @throws Error   If `$path` does not exist and `$strict = true`.
     * @return string
     */
    public static function absolutePath($path, $strict = true)
    {
        if (file_exists($path))
        {
            $path = realpath($path);
        }
        else
        {
            if ($strict)
            {
                Error::raise('Path "%s" does not exist.', [$path]);
            }
        }

        return self::normalizePath($path);
    }

    /**
     * Match filename against almost one pattern.
     *
     * @static
     * @method pathMatch
     * @param  string $path
     * @param  string|array $patterns Shell pattern or array of shell patterns.
     * @return boolean
     */
    public static function pathMatch($path, $patterns)
    {
        foreach ((array) $patterns as $pattern)
        {
            if (fnmatch($pattern, $path))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a flat files three (recursive) matching shell patterns.
     *
     * @static
     * @method scanPath
     * @param  string $path
     * @param  string|array [$includes='*']  Shell pattern or array of shell patterns to include in returned array.
     * @param  string|array [$excludes=null] Shell pattern or array of shell patterns to exclude from returned array.
     * @return array
     */
    public static function scanPath($path, $includes = '*', $excludes = null)
    {
        $result = [];

        foreach(scandir($path, SCANDIR_SORT_NONE) as $file)
        {
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            if (self::pathMatch($file, $excludes))
            {
                continue;
            }

            $absolutePath = $path . '/' . $file;

            if (is_dir($absolutePath))
            {
                $files  = self::scanPath($absolutePath, $includes, $excludes);
                $result = array_merge($result, $files);
            }
            else if (self::pathMatch($file, $includes))
            {
                 $result[] = $absolutePath;
            }

        }

        return $result;
    }

    /**
     * Normalize file content.
     *
     * - CRLF normalization.
     * - Trim witespaces.
     * - Tabs to spaces.
     *
     * @static
     * @method normalizeFileContents
     * @param  string $contents
     * @return string
     */
    public static function normalizeFileContents($contents)
    {
        // Normalize CRLF to UNIX style
        $contents = str_replace("\r\n", "\n", $contents);

        // Normalize TABS with four spaces
        $contents = str_replace("\t", "    ", $contents);

        // Trim witespaces
        $contents = trim($contents);

        // Return the file content
        return $contents;
    }

    /**
     * Get the file content normalized.
     *
     * - CRLF normalization.
     * - Trim witespaces.
     * - Tabs to spaces.
     *
     * @static
     * @method getFileContents
     * @param  string $path
     * @return string
     */
    public static function getFileContents($path)
    {
        // Get the file content
        $contents = file_get_contents($path);

        // Return the normalized file content
        return self::normalizeFileContents($contents);
    }
}
