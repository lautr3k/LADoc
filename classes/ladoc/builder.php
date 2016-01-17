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

use \LADoc;
use \LADoc\Builder\Files;

/**
 * Documentation builder.
 *
 * @class Builder
 * @use   \LADoc
 * @use   Builder\Files
 */
class Builder
{
    /**
     * Front controller instance.
     *
     * @protected
     * @property frontController
     * @type     \LADoc
    */
    protected $frontController = null;

    /**
     * Files instance.
     *
     * @protected
     * @property files
     * @type     Builder\Files
    */
    protected $files = null;

    /**
     * Output instance.
     *
     * @protected
     * @property output
     * @type     Output
    */
    protected $output = null;

    /**
     * Config instance.
     *
     * @protected
     * @property config
     * @type     Config
    */
    protected $config = null;

    // @protected @property integer startTime
    protected $startTime = null;

    /**
     * Class constructor.
     *
     * @constructor
     * @param \LADoc $frontController
     */
    public function __construct(LADoc $frontController)
    {
        // Set front controller instance.
        $this->frontController = $frontController;

        // Set output instance.
        $this->output = $frontController->getOutput();

        // Set config instance.
        $this->config = $frontController->getConfig();

        // Create files instance.
        $this->files = new Files($this->config);
    }

    /**
     * Get the front controller instance.
     *
     * @method getFrontController
     * @return \LADoc
     */
    public function getFrontController()
    {
        return $this->frontController;
    }

    /**
     * Get the files instance.
     *
     * @method getFiles
     * @return Builder\Files
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Build documentation.
     *
     * @method build
     * @chainable
     */
    public function build()
    {
        // Set start time.
        $this->startTime = time();

        // Write build start message.
        $date = date("Y-m-d", $this->startTime);
        $time = date("H:i:s", $this->startTime);
        $this->output->writeTitle('Build start at %s (%s)', [$time, $date]);

        // Scan input directory.
        $this->files->scan();

        // Get collections
        $includedFiles       = $this->files->getCollection('includedFiles');
        $excludedFiles       = $this->files->getCollection('excludedFiles');
        $excludedDirectories = $this->files->getCollection('excludedDirectories');

        // If no files found.
        if (empty($includedFiles)) {
            // Write and throw an error message.
            $path = $this->config->get('inputPath');
            $this->output->writeAndThrowError('No files found in %s.', [$path]);
        }

        // Write verbose messages.
        $this->output->writeTitle('Included files (%s)', count($includedFiles));
        $this->output->writeVerbose(array_values($includedFiles));
        $this->output->writeSpacer();

        if (! empty($excludedFiles)) {
            $this->output->writeTitle('Excluded files (%s)', count($excludedFiles));
            $this->output->writeVerbose(array_values($excludedFiles));
            $this->output->writeSpacer();
        }

        if (! empty($excludedDirectories)) {
            $this->output->writeTitle('Excluded directories (%s)', count($excludedDirectories));
            $this->output->writeVerbose(array_values($excludedDirectories));
            $this->output->writeSpacer();
        }

        // Set method chainable.
        return $this;
    }
}
