<?php
// @namespace LADoc\Builder
namespace LADoc\Builder;

use \LADoc\Config;

/**
 * Files manager.
 *
 * @class Files
 * @use   \LADoc\Config
 */
class Files
{
    /**
     * Config instance.
     *
     * @protected
     * @property config
     * @type     \LADoc\Config
    */
    protected $config = null;

    /**
     * Absolute path.
     *
     * @protected
     * @property path
     * @type     string
    */
    protected $path = null;

    /**
     * Files collection.
     *
     * @protected
     * @property collection
     * @type     array
    */
    protected $collection = [];

    /**
     * Class constructor.
     *
     * @constructor
     * @param \LADoc\Config $this->config
     */
    public function __construct(Config $config)
    {
        // Set config instance.
        $this->config = $config;

        // Set root path from configuration.
        $this->path = $config->get('inputPath');
    }

    /**
     * Get the root path.
     *
     * @method getPath
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get files collection.
     *
     * @method getCollection
     * @param  string $name
     * @return array  Collection of {File}.
     */
    public function getCollection($name = null)
    {
        if ($name)
        {
            //ksort($this->collection[$name]);
            return $this->collection[$name];
        }

        /*array_walk($this->collection, function(&$files) {
            ksort($files);
        });*/

        return $this->collection;
    }

    /**
     * Matches filename against almost one pattern.
     *
     * @static
     * @method match
     * @param  string       $path
     * @param  string|array $patterns
     * @return boolean
     */
    static public function match($path, $patterns)
    {
        // For each pattern.
        foreach ((array) $patterns as $pattern)
        {
            // If pattern matches.
            if (fnmatch($pattern, $path))
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
     * Scan directory looking for files matching patterns.
     *
     * @method scan
     * @param  string [$path=null]
     */
    public function scan($path = null)
    {
        // If first call (no path provided).
        if ($path === null)
        {
            // Reset files tree.
            $this->collection = [];

            // Set root path as first path to scan.
            $path = $this->path;
        }

        $includedFiles       = [];
        $excludedFiles       = [];
        $excludedDirectories = [];

        $files = [];

        // For each file/directory
        foreach(scandir($path, SCANDIR_SORT_NONE) as $file)
        {
            // Skip curent and parent directory.
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            // Create {File} instance.
            $file = new File($this->path, $path, $file);

            // If matches exclude files pattern.
            if ($file->nameMatch($this->config->get('excludeFiles'))
            or  $file->pathMatch($this->config->get('excludePaths')))
            {
                // Add file to excluded collection.
                if ($file->isDirectory()) {
                    $excludedDirectories[$file->getRelativePath()] = $file;
                }
                else {
                    $excludedFiles[$file->getRelativePath()] = $file;
                }

                // Go to next item.
                continue;
            }

            // If directory.
            if ($file->isDirectory())
            {
                // Merge sub-files matching patterns with current tree.
                $files += $this->scan($file->getAbsolutePath());

                // Go to next item.
                continue;
            }

            // If file type and matches one pattern.
            if ($file->nameMatch($this->config->get('includeFiles')))
            {
                // Add file to found collection.
                $includedFiles[$file->getRelativePath()] = $file;
            }
        }

        ksort($excludedFiles      , SORT_NATURAL | SORT_FLAG_CASE);
        ksort($excludedDirectories, SORT_NATURAL | SORT_FLAG_CASE);
        ksort($includedFiles      , SORT_NATURAL | SORT_FLAG_CASE);

        return $this->collection = array_merge_recursive(compact(
            'excludedFiles', 'excludedDirectories', 'includedFiles'
        ), $files);
    }
}
