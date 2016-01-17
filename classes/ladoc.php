<?php
/**
 * LADoc - Language Agnostic Documentor.
 *
 * @license   GPL
 * @version   1.0.0
 * @source    https://github.com/lautr3k/LADoc
 * @copyright 2016 © Onl'Fait (http://www.onlfait.ch)
 * @author    Sébastien Mischler (skarab) <sebastien@onlfait.ch>
 * @class     LADoc         21
 * @use       LADoc\Output  16
 * @use       LADoc\Config  17
 * @use       LADoc\Builder 18
 * @use       LADoc\Error   19
 */
use LADoc\Output;
use LADoc\Config;
use LADoc\Builder;
use LADoc\Error;

class LADoc
{
    // @static @protected @property string name
    static protected $name = 'LADoc';

    // @static @protected @property string version
    static protected $version = '1.0.0';

    // @static @protected @property string description
    static protected $description = 'Language Agnostic Documentor';

    // @protected @property LADoc\Output output
    protected $output = null;

    // @protected @property LADoc\Config config
    protected $config = null;

    // @protected @property LADoc\Builder builder
    protected $builder = null;

    /**
     * Initialize the front controller.
     *
     * @constructor
     */
    public function __construct()
    {
        // Create {@class LADoc\Output output} instance.
        $this->output = new Output();

        // Write an header message in the output.
        $this->output->writeTitle('%s - %s', [self::$name, self::$description]);
        $this->output->writeInfo('version: %s.', [self::$version]);
        $this->output->writeSpacer();
    }

    /**
     * Get the output instance.
     *
     * @method getOutput
     * @return LADoc\Output
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get the config instance.
     *
     * @method getConfig
     * @return LADoc\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the builder instance.
     *
     * @method getBuilder
     * @return LADoc\Builder
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
     * @throw Builder\Error
     * @chainable
     */
    public function setup($config = null)
    {
        // Write setup message.
        $this->output->writeTitle('Configuration');

        // Initialize configuration.
        try {
            $this->config = new Config($config);
        }
        // If an error is reaised.
        catch (Error $e) {
            // Log and re-throw the error.
            $this->output->writeAndThrowError($e->getMessage());
        }

        // Set output messages verbosity.
        $this->output->setVerbosity($this->config->get('verbosity'));

        // Write some setup informations.
        $this->output->writeVerbose($this->config->getAll());
        $this->output->writeSpacer();

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
        // Write run message.
        $this->output->writeTitle(str_repeat('haaa ', 100));
        $this->output->writeTitle('hshshshs '. str_repeat('1', 80) . ' dfsdf dfs fsdfsd fasdfsdfsdf');
        $this->output->writeVerbose('hohohoho');
        $this->output->writeSpacer();

        // Set method chainable.
        return $this;
    }
}
