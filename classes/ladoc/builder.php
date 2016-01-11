<?php
/**
 * LADoc - Language Agnostic Documentator.
 *
 * @license   GPL
 * @version   1.0.0
 * @source    https://github.com/lautr3k/LitDoc
 * @copyright 2016 © Onl'Fait (http://www.onlfait.ch)
 * @author    Sébastien Mischler (skarab) <sebastien@onlfait.ch>
 * @namespace LADoc
 */
namespace LADoc;

/**
 * Project builder.
 *
 * @class Builder
 */
class Builder
{
    /**
     * Main configuration.
     *
     * @protected
     * @property  config
     * @type      array
    */
    protected $config =
    [
        'input'  => '.',
        'output' => './docs',
        'files'  => '*.php'
    ];

    /**
     * Class constructor.
     *
     * @constructor
     */
    public function __construct()
    {
        // Merge user configuration
        $this->config = array_merge($this->config, $_GET);

        // Normalize and test if input/output path exists
        $this->config['input']  = Helper::absolutePath($this->config['input']);
        $this->config['output'] = Helper::absolutePath($this->config['output']);
    }

    /**
     * Get the input files tree.
     *
     * @protected
     * @method getFilesTree
     */
    protected function getFilesTree()
    {
        $this->filesTree = Helper::scanPath
        (
            $this->config['input'],
            $this->config['files']
        );
    }

    /**
     * Build the documentation.
     *
     * @method build
     */
    public function build()
    {
        // Get the input files tree
        $this->getFilesTree();

        // Debugage
        var_dump($this->filesTree);
    }
}
