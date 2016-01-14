<?php
// @namespace LADoc
namespace LADoc;

 /**
 * Collection of static convenience methods.
 *
 * @class Helper
 */
class Helper
{
    /**
     * Return a normalized path.
     *
     * - Replace path separators by url separator.
     * - Remove starting and trailing url separator.
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
     * Return a absolute normalized path.
     *
     * @static
     * @method absolutePath
     * @param  string  $path
     * @param  boolean [$strict=true]
     * @throw  Error
     * @return string
     */
    public static function absolutePath($path, $strict = true)
    {
        // If path found.
        if (file_exists($path))
        {
            // Get the real path.
            $path = realpath($path);
        }
        // If path not found and strict mode.
        else if ($strict)
        {
            // Raise an error.
            Error::raise('Path "%s" does not exist.', [$path]);
        }

        // Return normalized path.
        return self::normalizePath($path);
    }
}
