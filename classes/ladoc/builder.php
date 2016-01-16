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
        $this->console->title('%s - %s', [self::$name, self::$description]);
        $this->console->info('version: %s.', [self::$version]);
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
        // Write setup message.
        $this->console->title('Setup configuration');

        // Initialize configuration.
        try {
            $this->config = new Config($config);
        }
        catch (Error $e) {
            $this->console->error($e->getMessage());
        }

        // Write some setup informations.
        $this->console->info('inputPath : %s.', [$this->config->get('inputPath')]);
        $this->console->info('outputPath: %s.', [$this->config->get('outputPath')]);

        // Set method chainable.
        return $this;
    }

    /**
     * Build documentation.
     *
     * @method build
     * @param array|null [$config=null]
     * @chainable
     */
    public function build($config = null)
    {
        // If $config provided.
        if ($config !== null) {
            // Setup configuration.
            $this->setup($config);
        }

        // Else if builder was not setup.
        else if ($this->config === null) {
            // Log and throw an error message.
            $this->console->error('Call Builder::setup() before calling Builder::build().');
        }

        // Build...

        // Set method chainable.
        return $this;
    }
}
