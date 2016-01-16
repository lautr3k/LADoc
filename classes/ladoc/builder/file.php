<?php
// @namespace LADoc\Builder
namespace LADoc\Builder;

use \LADoc\Builder;

/**
 * File manager.
 *
 * @class File
 * @use   \LADoc\Builder
 */
class File
{
    /**
     * @protected
     * @property path
     * @type     string
    */
    protected $path = null;

    /**
     * @protected
     * @property parent
     * @type     File
    */
    protected $parent = null;

    /**
     * @protected
     * @property name
     * @type     string
    */
    protected $name = null;

    /**
     * Class constructor.
     *
     * @constructor
     * @param string $parent
     * @param string $name
     */
    public function __construct($path, $name)
    {
        // Set file path.
        $this->path = $path . '/' . $name;

        // Set parent path.
        $this->parent = $path;

        // Set file name.
        $this->name = $name;
    }

    /**
     * Get file path.
     *
     * @method getPath
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get parent path.
     *
     * @method getParent
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get file name.
     *
     * @method getName
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Test if the file is a directory.
     *
     * @method isDirectory
     * @return boolean
     */
    public function isDirectory()
    {
        return is_dir($this->path);
    }

    /**
     * Test if file path matches almost one pattern.
     *
     * @method match
     * @param  string|array $patterns
     * @return boolean
     */
    public function match($patterns)
    {
        // For each pattern.
        foreach ((array) $patterns as $pattern)
        {
            // If pattern matches.
            if (fnmatch($pattern, $this->name))
            {
                // Return TRUE.
                return true;
            }
            // Else test next pattern.
        }

        // If nothing matches, return FALSE.
        return false;
    }
}
