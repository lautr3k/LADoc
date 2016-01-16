<?php
// @namespace LADoc\Builder
namespace LADoc\Builder;

use \LADoc\Builder;

/**
 * Files manager.
 *
 * @class Files
 * @use   \LADoc\Builder
 */
class Files
{
    /**
     * Builder instance.
     *
     * @protected
     * @property builder
     * @type     \LADoc\Builder
    */
    protected $builder = null;

    /**
     * Flat files tree.
     *
     * @protected
     * @property tree
     * @type     array
    */
    protected $tree = [];

    /**
     * Class constructor.
     *
     * @constructor
     * @param \LADoc\Builder $builder
     */
    public function __construct(Builder $builder)
    {
        // Set builder instance.
        $this->builder = $builder;
    }

    /**
     * Get the files tree.
     *
     * @method getTree
     * @return array Collection of {File}.
     */
    public function getTree()
    {
        return $this->tree;
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
     * @return array
     */
    public function scan($path = null)
    {
        // Set console and config shortcuts.
        $console = $this->builder->getFrontController()->getConsole();
        $config  = $this->builder->getFrontController()->getConfig();

        // If first call (no path provided).
        if ($path === null)
        {
            // Reset files tree.
            $this->tree = [];

            // Set default path from configuration.
            $path = $config->get('inputPath');
        }

        // Log scan start message.
        $console->info('Scan ----> %s', [$path]);

        // For each file/directory
        foreach(scandir($path) as $file)
        {
            // Skip curent and parent directory.
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            // Create {File} instance.
            $file = new File($path, $file);

            // If matches exclude pattern.
            if ($file->match($config->get('excludeFiles'))
            or  $file->match($config->get('excludePaths')))
            {
                // Log exclude file message.
                $console->info('Exclude -> %s', [$file->getPath()]);

                // Go to next item.
                continue;
            }

            // If directory.
            if ($file->isDirectory())
            {
                // Merge sub-files matching patterns with current tree.
                $this->scan($file->getPath());

                // Go to next item.
                continue;
            }

            // If file and matches one pattern.
            if ($file->match($config->get('includeFiles')))
            {
                // Log file found message.
                $console->info('Found ---> %s', [$file->getPath()]);

                // Add file to tree.
                $this->tree[] = $file;
            }
        }

        // Return files tree.
        return $this->tree;
    }
}
