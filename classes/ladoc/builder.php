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

        // Write start message.
        $this->console->info('Builder setup 1');
        $this->console->warning('Oups!');
        $this->console->info('Builder setup 2');

        // Set method chainable.
        return $this;
    }

    /**
     * Build documentation.
     *
     * @method build
     */
    public function build()
    {
        // Write start message.
        $this->console->info('LADoc - Language Agnostic Documentor.');

        // If builder was not setup.
        if ($this->config === null)
        {
            // Log and throw an error message.
            $this->console->error('Please call Builder::setup() before calling Builder::build().');
        }

        // Set method chainable.
        return $this;
    }
}
