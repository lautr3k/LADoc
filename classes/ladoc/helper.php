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
        // If the path exist
        if (file_exists($path))
        {
            // Get the absolute path
            $path = realpath($path);
        }

        // If the path does not exist and strict mode
        else
        {
            if ($strict)
            {
                Error::raise('Path "%s" does not exist.', [$path]);
            }
        }

        // Return a normalized path
        return self::normalizePath($path);
    }

    /**
     * Return a flat files three (recursive).
     *
     * @static
     * @method scanPath
     * @param  string $path
     * @param  string $pattern
     * @return array
     */
    public static function scanPath($path)
    {
        // Files list
        $paths = [];



        return $paths;
    }
}
