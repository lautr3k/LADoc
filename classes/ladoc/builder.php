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
     * Class constructor.
     *
     * @constructor
     * @param \LADoc $frontController
     */
    public function __construct(LADoc $frontController)
    {
        // Set front controller instance.
        $this->frontController = $frontController;

        // Create files instance.
        $this->files = new Files($this);
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
        // Scan input directory.
        $this->files->scan();

        var_dump($this->files->getTree());

        // Set method chainable.
        return $this;
    }
}
