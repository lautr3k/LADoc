<?php
/**
 * LADoc - Language Agnostic Documentor.
 *
 * @license   GPL
 * @version   1.0.0
 * @source    https://github.com/lautr3k/LADoc
 * @copyright 2016 © Onl'Fait (http://www.onlfait.ch)
 * @author    Sébastien Mischler (skarab) <sebastien@onlfait.ch>
 * @namespace LADoc
 * @main
 */
namespace LADoc;

/**
 * Documentation builder.
 *
 * @class Builder
 */
class Builder
{
    /**
     * Default settings.
     *
     * @static
     * @protected
     * @property defaults
     * @type     array
    */
    static protected $defaults =
    [
        'inputPath'    => '.',
        'outputPath'   => './docs',
        'includeFiles' => ['*.php', '*.md'],
        'excludeFiles' => ['.git', '*Copie.php'],
        'excludePaths' => ['./docs', './cache']
    ];

    /**
     * Settings.
     *
     * @protected
     * @property settings
     * @type     array
    */
    protected $settings = [];

    /**
     * Absolute path to input directory.
     *
     * @protected
     * @property inputPath
     * @type     string
    */
    protected $inputPath = null;

    /**
     * Absolute path to output directory.
     *
     * @protected
     * @property outputPath
     * @type     string
    */
    protected $outputPath   = null;

    /**
     * Collection of patterns to match files (and directories) to be included.
     *
     * @protected
     * @property includeFiles
     * @type     array
    */
    protected $includeFiles = null;

    /**
     * Collection of patterns to match files (and directories) to be excluded.
     *
     * @protected
     * @property excludeFiles
     * @type     array
    */
    protected $excludeFiles = null;

    /**
     * Collection of absolute paths to be excluded.
     *
     * @protected
     * @property excludePaths
     * @type     array
    */
    protected $excludePaths = null;

    /**
     * Collection of files to be parsed.
     *
     * @protected
     * @property files
     * @type     array
    */
    protected $files = [];

    /**
     * Collection of messages indexed by type.
     *
     * @protected
     * @property messages
     * @type     array
    */
    protected $messages = [];

    /**
     * Class constructor.
     *
     * @constructor
     * @param array|null [$settings=null]
     */
    public function __construct($settings = null)
    {
        // Merge user settings with defaults settings.
        $this->settings = array_merge(self::$defaults, (array) $settings);

        // Setup properties from settings.
        $this->setupProperties();
    }

    /**
     * Setup properties from settings.
     *
     * @method setupProperties
     */
    protected function setupProperties()
    {
        // Test, set and normalize input path.
        $this->inputPath  = Helper::absolutePath($this->settings['inputPath']);

        // Test, set and normalize output path.
        $this->outputPath = Helper::absolutePath($this->settings['outputPath']);

        // Set patterns to match files/directories to be included.
        $this->includeFiles = $this->settings['includeFiles'];

        // Set patterns to match files/directories to be excluded.
        $this->excludeFiles = $this->settings['excludeFiles'];

        // Test, set and normalize paths to be excluded.
        $this->excludePaths = $this->settings['excludePaths'];

        array_walk($this->excludePaths, function(&$path)
        {
            $path = realpath($path);
            $path = $path ? Helper::normalizePath($path) : null;
        });

        $this->excludePaths = array_filter($this->excludePaths);
    }

    /**
     * Log a message by type.
     *
     * @protected
     * @method message
     * @param  string  $type
     * @param  string  $message
     * @param  array   [$data=null]
     */
    public function message($type, $message, $data = null)
    {
        $this->messages[$type][] = $data ? vsprintf($message, $data) : $message;
    }

    /**
     * Log info message.
     *
     * @protected
     * @method info
     * @param  string $message
     * @param  array  [$data=null]
     */
    public function info($message, $data = [])
    {
        $this->message('info', $message, $data);
    }

    /**
     * Log warning message.
     *
     * @protected
     * @method message
     * @param  string  $type
     * @param  string  $message
     * @param  array  [$data=null]
     */
    public function warning($message, $data = [])
    {
        $this->message('warning', $message, $data);
    }

    /**
     * Log and throw error message.
     *
     * @protected
     * @method error
     * @param  string  $type
     * @param  string  $message
     * @param  array  [$data=null]
     * @throw  Error
     */
    public function error($message, $data = [])
    {
        $this->message('error', $message, $data);
        Error::raise($message, $data);
    }

    /**
     * Matches filename against almost one pattern.
     *
     * @static
     * @method pathMatch
     * @param  string       $path
     * @param  string|array $patterns
     * @return boolean
     */
    public static function pathMatch($path, $patterns)
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
     * @static
     * @method scanPath
     * @param  string $path
     * @return array
     */
    public function scanPath($path)
    {
        // Log scan start message.
        $this->info('Scan: %s', [$path]);

        // Reset files collection.
        $files = [];

        // For each file/directory
        foreach(scandir($path) as $file)
        {
            // Skip curent and parent directory.
            if (in_array($file, array('.', '..')))
            {
                continue;
            }

            // Append filename to path.
            $subPath = $path . '/' . $file;

            // If matches exclude pattern.
            if (self::pathMatch($file, $this->excludeFiles))
            {
                // Log exclude file message.
                $this->info('Exclude: %s', [$subPath]);

                // Go to next item.
                continue;
            }

            // If it is a directory.
            if (is_dir($subPath))
            {
                // Merge sub-files matching patterns with current collection.
                $files = array_merge($files, $this->scanPath($subPath));
            }
            // Else if a file matching the pattern.
            else if (self::pathMatch($file, $this->includeFiles))
            {
                // Log file found message.
                $this->info('Found: %s', [$subPath]);

                // Add path to files collection.
                $files[] = $subPath;
            }
        }

        // Return files collection.
        return $files;
    }

    /**
     * Build documentation.
     *
     * @method build
     */
    public function build()
    {
        // Log build start message.
        $this->info('Start new build');

        // Scan input directory looking for files to be parsed.
        $this->files = $this->scanPath($this->inputPath);

        // @debug Print some internal properties.
        echo('<h1>Messages</h1><hr />');
        var_dump($this->messages);

        echo('<h1>Settings</h1><hr />');
        var_dump($this->settings);

        echo('<h1>Files</h1><hr />');
        var_dump($this->files);

        echo('<h1>Builder</h1><hr />');
        var_dump($this);
    }
}
