<?php
// @namespace LADoc
namespace LADoc;

/**
 * Configuration class.
 *
 * @class Config
 */
class Config
{
    /**
     * Defaults configuration.
     *
     * @static
     * @protected
     * @property config
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
     * Main configuration.
     *
     * @protected
     * @property config
     * @type     array
    */
    protected $config = [];

    /**
     * Create and initialize main configuration.
     *
     * @constructor
     * @param array|null [$config=null]
     */
    public function __construct($config = null)
    {
        $config = [
            'bob' => 'hahaha',
            'inputPath' => './'
        ];

        $config and $this->merge(array_merge(self::$defaults, $config));
    }

    /**
     * Get config item.
     *
     * @method get
     * @param  string $key
     * @param  mixed  [$defaultValue=null]
     */
    public function get($key, $defaultValue)
    {
        // If item found.
        if (array_key_exists($key, $this->config))
        {
            // Return item value;
            return $this->config[$key];
        }

        // Else return default value.
        return $defaultValue;
    }

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
        if (is_array($path))
        {
            return array_map('self::normalizePath', $path);
        }

        return trim(preg_replace('|[\\\\/]+|', '/', $path), '/');
    }

    /**
     * Set new path.
     *
     * @method setPath
     * @param  string $key
     * @param  string  $path
     * @param  boolean [$strict=true]
     * @return string
     */
    public function setPath($key, $path, $strict = true)
    {
        // If strict mode and path not found.
        if ($strict and ! file_exists($path))
        {
            // Throw an error.
            Error::raise('Path not found: "%s".', [$path]);
        }

        // Else set and return normalized absolute path.
        return $this->config[$key] = self::normalizePath(realpath($path));
    }

    /**
     * Set config item.
     *
     * @method set
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    public function set($key, $value)
    {
        // Handle specials cases.
        switch ($key)
        {
            // Paths must exist.
            case 'inputPath':
            case 'outputPath':
                return $this->setPath($key, $value);
            case 'excludePaths':
                $value = array_filter(array_map('realpath', $value));
                $value = self::normalizePath($value);
        }

        // Set and return value.
        return $this->config[$key] = $value;
    }

    /**
     * Merge config items.
     *
     * @method merge
     * @param  array $config
     */
    public function merge($config)
    {
        foreach($config as $key => $value)
        {
            $this->set($key, $value);
        }
    }
}
