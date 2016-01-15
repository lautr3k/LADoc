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
     * Version.
     *
     * @static
     * @property version
     * @type     string
    */
    static public $version = '1.0.0';

    /**
     * Name.
     *
     * @static
     * @property name
     * @type     string
    */
    static public $name = 'LADoc';

    /**
     * Description.
     *
     * @static
     * @property description
     * @type     string
    */
    static public $description = 'Language Agnostic Documentor';

    /**
     * Console instance.
     *
     * @protected
     * @property console
     * @type     Console
    */
    public $console = null;

    /**
     * Config instance.
     *
     * @protected
     * @property config
     * @type     Config
    */
    public $config = null;

    /**
     * Class constructor.
     *
     * @constructor
     */
    public function __construct()
    {
        // Initialize console.
        $this->console = new Console();

        // Write header message.
        $this->console->info('%s - %s - v%s', [
            self::$name,
            self::$description,
            self::$version
        ]);
    }

    /**
     * Setup the builder.
     *
     * @method setup
     * @param array|null [$config=null]
     * @chainable
     */
    public function setup($config = null)
    {
        // Initialize configuration.
        try
        {
            $this->config = new Config($config);
        }
        catch (Error $e)
        {
            $this->console->error($e->getMessage());
        }

        // Write setup message.
        $this->console->info('---');
        $this->console->info('inputPath  = %s.', [$this->config->get('inputPath')]);
        $this->console->info('outputPath = %s.', [$this->config->get('outputPath')]);

        // Set method chainable.
        return $this;
    }

    /**
     * Build documentation.
     *
     * @method build
     * @chainable
     */
    public function build()
    {
        // If builder was not setup.
        if ($this->config === null)
        {
            // Log and throw an error message.
            $this->console->error('Call Builder::setup() before calling Builder::build().');
        }

        // Set method chainable.
        return $this;
    }
}
