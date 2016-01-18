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
use \LADoc\Builder\Files\Tree;

/**
 * Documentation builder.
 *
 * @class Builder
 * @use   \LADoc
 * @use   Builder\Files\Tree
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
     * @property filesTree
     * @type     Builder\Files\Tree
    */
    protected $filesTree = null;

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
        // Set {@class \LADoc front controller} instance.
        $this->frontController = $frontController;

        // Set {@class \LADoc\Console console} instance.
        $this->output = $frontController->getOutput();

        // Set {@class \LADoc\Config config} instance.
        $this->config = $frontController->getConfig();

        // Create {@class \LADoc\Files\Tree file tree} instance.
        $this->filesTree = new Tree($this->config);
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
     * @return Builder\Files\Tree
     */
    public function getFiles()
    {
        return $this->filesTree;
    }

    /**
     * Scan the files tree.
     *
     * @protected
     * @method scanFilesTree
     * @throw  Error
     */
    protected function scanFilesTree()
    {
        // Write scan start message.
        $path = $this->config->get('inputPath');
        $this->output->writeInfo('Scan: %s', [$path]);

        // Scan input directory.
        $this->filesTree->scan();

        // Get files found.
        $includedFiles = $this->filesTree->getFiles('includedFiles');

        // If no files found.
        if (empty($includedFiles)) {
            // Write and throw an error message.
            $this->output->writeAndThrowError('Done: No files found.');
        }

        // Write scan done message.
        $this->output->writeInfo('Done!');
        $this->output->writeSpacer();

        // Write verbose messages.
        $this->output->writeTitle('Included files (%s)', count($includedFiles));
        $this->output->writeVerbose(array_values($includedFiles));
        $this->output->writeSpacer();

        $excludedFiles = $this->filesTree->getFiles('excludedFiles');

        if (! empty($excludedFiles)) {
            $this->output->writeTitle('Excluded files (%s)', count($excludedFiles));
            $this->output->writeVerbose(array_values($excludedFiles));
            $this->output->writeSpacer();
        }

        $excludedDirectories = $this->filesTree->getFiles('excludedDirectories');

        if (! empty($excludedDirectories)) {
            $this->output->writeTitle('Excluded directories (%s)', count($excludedDirectories));
            $this->output->writeVerbose(array_values($excludedDirectories));
            $this->output->writeSpacer();
        }
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
        $this->output->writeTitle('Start build at %s (%s)', [$time, $date]);

        // Scan the files tree.
        $this->scanFilesTree();

        // Set method chainable.
        return $this;
    }
}
