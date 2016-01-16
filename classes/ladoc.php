<?php
/**
 * LADoc - Language Agnostic Documentor.
 *
 * @license   GPL
 * @version   1.0.0
 * @source    https://github.com/lautr3k/LADoc
 * @copyright 2016 © Onl'Fait (http://www.onlfait.ch)
 * @author    Sébastien Mischler (skarab) <sebastien@onlfait.ch>
 * @class     LADoc         19
 * @use       LADoc\Console 15
 * @use       LADoc\Config  16
 * @use       LADoc\Builder 17
 */
use LADoc\Console;
use LADoc\Config;
use LADoc\Builder;

class LADoc
{
    /**
     * Name.
     *
     * @static
     * @protected
     * @property name
     * @type     string
    */
    static protected $name = 'LADoc';

    /**
     * Version.
     *
     * @static
     * @protected
     * @property version
     * @type     string
    */
    static protected $version = '1.0.0';

    /**
     * Description.
     *
     * @static
     * @protected
     * @property description
     * @type     string
    */
    static protected $description = 'Language Agnostic Documentor';

    /**
     * Console instance.
     *
     * @protected
     * @property console
     * @type     \LADoc\Console
    */
    protected $console = null;

    /**
     * Config instance.
     *
     * @protected
     * @property config
     * @type     \LADoc\Config
    */
    protected $config = null;

    /**
     * Builder instance.
     *
     * @protected
     * @property builder
     * @type     \LADoc\Builder
    */
    protected $builder = null;

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
     * Get the console instance.
     *
     * @method getConsole
     * @return \LADoc\Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Get the console instance.
     *
     * @method getConfig
     * @return \LADoc\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the builder instance.
     *
     * @method getBuilder
     * @return \LADoc\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
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
        $this->console->title('Setup: configuration');

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

        // Initialize builder.
        $this->builder = new Builder($this);

        // Set method chainable.
        return $this;
    }

    /**
     * Run the front controller.
     *
     * @method run
     * @param  string [$action='build']
     * @chainable
     */
    public function run($action = 'build')
    {
        // If configuration was not setup.
        if ($this->config === null) {
            // Log and throw an error message.
            $this->console->error('Call LADoc::setup() before calling LADoc::run().');
        }

        // Write run message.
        $this->console->title('Run: %s', [$action]);

        // Run action:
        switch ($action) {
            // If build:
            case 'build':
                // Run the builder.
                $this->builder->build();
                break;
            // If unknown:
            default:
                // Log and throw an error message.
                $this->console->error('Unknown action: %s', [$action]);
        }

        // Set method chainable.
        return $this;
    }
}
