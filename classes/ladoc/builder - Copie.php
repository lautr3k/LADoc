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
     * Tags list.
     *
     * @protected
     * @property tags
     * @type     array
    */
    protected $tags =
    [
        'license'     => 1,
        'version'     => 1,
        'source'      => 1,
        'link'        => 1,
        'copyright'   => 1,
        'author'      => 1,
        'namespace'   => 1,
        'class'       => 1,
        'public'      => 0,
        'protected'   => 0,
        'private'     => 0,
        'static'      => 0,
        'constructor' => 0,
        'property'    => 1,
        'type'        => 1,
        'method'      => 1,
        'param'       => 3,
        'return'      => 2,
        'use'         => 1,
        'extend'      => 1,
        'throw'       => 1
    ];

    /**
     * Main configuration.
     *
     * @protected
     * @property config
     * @type     array
    */
    protected $config =
    [
        'input'    => '.',
        'output'   => './docs',
        'includes' => '*.php, *.md',
        'excludes' => 'docs, .git',
        'tags'     => []
    ];

    /**
     * Flat files tree.
     *
     * @protected
     * @property filesTree
     * @type     array
    */
    protected $filesTree = [];

    /**
     * DocBlocks collection indexed by file path.
     *
     * @protected
     * @property docBlocks
     * @type     array
    */
    protected $docBlocks = [];

    /**
     * Classes collection indexed by namespace.
     *
     * @protected
     * @property classes
     * @type     array
    */
    protected $classes = [];

    /**
     * Files path collection indexed by relative path.
     *
     * @protected
     * @property files
     * @type     array
    */
    protected $files = [];

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
     * Parse a DocBlock tag string.
     *
     * @protected
     * @method parseDocBlockTag
     * @param  string $tag
     * @return array
     */
    protected function parseDocBlockTag($tag)
    {
        $result        = [];
        $tag           = substr($tag, 1);
        $firstSpacePos = strpos($tag, ' ');

        if (! $firstSpacePos)
        {
            return ['type' => 'tag', 'name' => $tag];
        }

        $tag    = preg_replace('|[ ]++|', ' ', $tag);
        $name   = substr($tag, 0, $firstSpacePos);
        $value  = substr($tag, $firstSpacePos + 1);
        $limit  = isset($this->tags[$name]) ? $this->tags[$name] : 2;
        $params = array_pad(explode(' ', $value, $limit), $limit, null);

        return ['type' => 'tag', 'name' => $name, 'params' => $params];
    }

    /**
     * Parse a DocBlock.
     *
     * @protected
     * @method parseDocBlock
     * @param  array $docBlock
     * @return array
     */
    protected function parseDocBlock($docBlock)
    {
        $result = [];
        $text   = '';

        foreach ($docBlock as $line)
        {
            if ($line === '' or $line[0] !== '@')
            {
                $text .= "$line\n";
            }
            else
            {
                if (strlen($text))
                {
                    $result[] = ['type' => 'text', 'data' => $text];
                    $text = '';
                }

                $result[] = $this->parseDocBlockTag($line);
            }
        }

        return $result;
    }

    /**
     * Parse extracted DocBlocks.
     *
     * @protected
     * @method parseDocBlocks
     * @param  array $docBlocks
     * @return array
     */
    protected function parseDocBlocks($docBlocks)
    {
        $result = [];

        foreach ($docBlocks as $docBlock)
        {
            $result = array_merge($result, $this->parseDocBlock($docBlock));
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
        $this->docBlocks = [];

        foreach ($this->filesTree as $path)
        {
            $contents               = Helper::getFileContents($path);
            $docBlocks              = $this->extractDocBlocks($contents);
            $this->docBlocks[$path] = $this->parseDocBlocks($docBlocks);
        }
    }

    /**
     * Collect all item found.
     *
     * @protected
     * @method processDocBlock
     */
    protected function processDocBlock()
    {
        if ($this->currentDocBlock['type'] == 'tag')
        {
            if ($this->currentDocBlock['name'] == 'namespace')
            {
                $this->currentNamespace = $this->currentDocBlock['params'][0];
            }
            else if ($this->currentDocBlock['name'] == 'class')
            {
                $this->currentClass = $this->currentDocBlock['params'][0];

                $this->classes[$this->currentNamespace][] = $this->currentClass;

                $key = str_replace($this->config['input'] . '/', '', $this->currentPath);
                
                $this->files[$key] = $this->currentPath;
            }
        }
    }

    /**
     * Collect all item found.
     *
     * @protected
     * @method processDocBlocks
     */
    protected function processDocBlocks()
    {
        $this->classes = [];
        $this->files   = [];

        foreach ($this->docBlocks as $path => $docBlocks)
        {
            $this->currentNamespace = '';
            $this->currentPath      = $path;

            foreach ($docBlocks as $docBlock)
            {
                $this->currentDocBlock = $docBlock;

                $this->processDocBlock();
            }
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
        $this->processDocBlocks();

        //var_dump($this->namespaces);
        var_dump($this->classes);
        var_dump($this->files);
    }
}
