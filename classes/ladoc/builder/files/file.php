<?php
// @namespace LADoc\Builder\Files
namespace LADoc\Builder\Files;

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
     * @property name
     * @type     string
    */
    protected $name = null;

    /**
     * @protected
     * @property rootPath
     * @type     string
    */
    protected $rootPath = null;

    /**
     * @protected
     * @property parentPath
     * @type     string
    */
    protected $parentPath = null;

    /**
     * @protected
     * @property absolutePath
     * @type     string
    */
    protected $absolutePath = null;

    /**
     * @protected
     * @property relativePath
     * @type     string
    */
    protected $relativePath = null;

    /**
     * Class constructor.
     *
     * @constructor
     * @param string $rootPath
     * @param string $parentPath
     * @param string $filename
     */
    public function __construct($rootPath, $parentPath, $filename)
    {
        // Set file name.
        $this->name = $filename;

        // Set file paths.
        $this->rootPath     = $rootPath;
        $this->parentPath   = $parentPath;
        $this->absolutePath = $parentPath . '/' . $filename;
        $this->relativePath = str_replace("$rootPath/", '', $this->absolutePath);
    }

    /**
     * Return the absolute file path when treated like a string.
     *
     * @method getRootPath
     * @return string
     */
    public function __toString()
    {
        return $this->absolutePath;
    }

    /**
     * Get root path.
     *
     * @method getRootPath
     * @return string
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * Get parent path.
     *
     * @method getParentPath
     * @return string
     */
    public function getParentPath()
    {
        return $this->parentPath;
    }

    /**
     * Get absolute path.
     *
     * @method getAbsolutePath
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * Get relative path (from root path).
     *
     * @method getRelativePath
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
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
        return is_dir($this->absolutePath);
    }

    /**
     * Test if property matches almost one pattern.
     *
     * @protected
     * @method match
     * @param  string|array $patterns
     * @param  string|array $patterns
     * @return boolean
     */
    protected function match($property, $patterns)
    {
        // For each pattern.
        foreach ((array) $patterns as $pattern)
        {
            // If pattern matches.
            if (fnmatch($pattern, $this->$property))
            {
                // Return TRUE.
                return true;
            }
            // Else test next pattern.
        }

        // If nothing matches, return FALSE.
        return false;
    }

    /**
     * Test if file name matches almost one pattern.
     *
     * @method nameMatch
     * @param  string|array $patterns
     * @return boolean
     */
    public function nameMatch($patterns)
    {
        return $this->match('name', $patterns);
    }

    /**
     * Test if file path matches almost one pattern.
     *
     * @method pathMatch
     * @param  string|array $patterns
     * @return boolean
     */
    public function pathMatch($patterns)
    {
        return $this->match('absolutePath', $patterns);
    }
}
