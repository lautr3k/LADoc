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
        'input'    => '.',
        'output'   => './docs',
        'includes' => '*.php, *.md',
        'excludes' => 'docs, .git'
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

        // Normalize includes and excludes pattern as array
        $this->config['includes'] = explode(',', $this->config['includes']);
        $this->config['includes'] = array_map('trim', $this->config['includes']);
        $this->config['excludes'] = explode(',', $this->config['excludes']);
        $this->config['excludes'] = array_map('trim', $this->config['excludes']);
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
            $this->config['includes'],
            $this->config['excludes']
        );
    }

    /**
     * Extract all DocBlocks from file contents.
     *
     * @protected
     * @method extractDocBlocks
     * @param  string $contents
     * @return array
     */
    protected function extractDocBlocks($contents)
    {
        $result     = [];
        $lines      = explode("\n", $contents);
        $inDocBlock = false;

        foreach ($lines as $num => $line)
        {
            $line    = trim($line);
            $lineLen = strlen($line);

            if (! $inDocBlock and $lineLen > 1 and $line[0] == '/' and $line[1] == '*')
            {
                $docBlock   = [];
                $inDocBlock = true;
            }
            else if ($inDocBlock and $lineLen > 1 and $line[0] == '*' and $line[1] == '/')
            {
                $result[]   = $docBlock;
                $inDocBlock = false;
            }
            else if ($inDocBlock)
            {
                $docBlock[$num] = $lineLen > 2 ? substr($line, 2) : '';
            }
        }

        return $result;
    }

    /**
     * Parse the files tree.
     *
     * @protected
     * @method parseFilesTree
     */
    protected function parseFilesTree()
    {
        foreach ($this->filesTree as $path)
        {
            $contents  = Helper::getFileContents($path);
            $docBlocks = $this->extractDocBlocks($contents);

            var_dump([$path, $docBlocks]);
        }
    }

    /**
     * Build the documentation.
     *
     * @method build
     */
    public function build()
    {
        $this->getFilesTree();
        $this->parseFilesTree();
    }
}
