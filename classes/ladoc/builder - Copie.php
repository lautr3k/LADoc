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
     * @property config
     * @type     array
    */
    protected $config =
    [
        'input'    => '.',
        'output'   => './docs',
        'includes' => '*.php, *.md',
        'excludes' => 'docs, .git, *Copie.php'
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
     * Tags list.
     *
     * - Indexed by tag name.
     * - The value is the maximum number of parameters allowed.
     *
     * @protected
     * @property tags
     * @type     array
    */
    protected $tags =
    [
        'author'      => [1, null],
        'class'       => [1, null],
        'constructor' => [0, null],
        'copyright'   => [1, null],
        'extend'      => [1, null],
        'license'     => [1, null],
        'link'        => [2, ['url', 'text']],
        'method'      => [1, null],
        'namespace'   => [1, null],
        'param'       => [3, ['type', 'name', 'text']],
        'private'     => [0, null],
        'property'    => [1, null],
        'protected'   => [0, null],
        'public'      => [0, null],
        'return'      => [2, ['type', 'text']],
        'source'      => [2, ['url', 'text']],
        'throw'       => [2, ['type', 'text']],
        'static'      => [0, null],
        'type'        => [1, null],
        'use'         => [1, null],
        'version'     => [1, null]
    ];

    /**
     * Primary tags list.
     *
     * - One and only one of this tags must be present in a doc block.
     *
     * @protected
     * @property primaryTags
     * @type     array
    */
    protected $primaryTags =
    [
        'class',
        'constructor',
        'method',
        'namespace',
        'property'
    ];

    /**
     * Multiple tags list.
     *
     * - Tags allowed to be used multiple time in the same doc block.
     *
     * @protected
     * @property multipleTags
     * @type     array
    */
    protected $multipleTags =
    [
        'copyright',
        'extend',
        'link',
        'param',
        'return',
        'throw',
        'use'
    ];

    /**
     * Classes collection indexed by namespace.
     *
     * @protected
     * @property classes
     * @type     array
    */
    protected $classes = [];

    /**
     * Class constructor.
     *
     * @constructor
     * @param string test
     * @param string test
     * @param string test
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
        $tokken        = ['name' => $tag, 'params' => true];

        if (! $firstSpacePos)
        {
            return $tokken;
        }

        $tag   = preg_replace('|[ ]++|', ' ', $tag);
        $name  = substr($tag, 0, $firstSpacePos);
        $value = substr($tag, $firstSpacePos + 1);
        $limit = isset($this->tags[$name]) ? $this->tags[$name][0] : 2;

        if ($limit == 1)
        {
            $params = $value;
        }
        else
        {
            $params = array_pad(explode(' ', $value, $limit), $limit, null);

            // Named parameters
            if ($this->tags[$name][1])
            {
                $namedParams = [];

                foreach ($this->tags[$name][1] as $key => $name)
                {
                    $namedParams[$name] = $params[$key];
                }

                $params = $namedParams;
            }
        }

        $tokken['name']   = $name;
        $tokken['params'] = $params;

        return $tokken;
    }

    /**
     * Parse a file.
     *
     * - Extract DocBlocks.
     * - Tokkenize DocBlocks.
     *
     * @protected
     * @method parseFile
     * @param  string $path
     * @return array
     */
    protected function parseFile($path)
    {
        $contents   = Helper::getFileContents($path);
        $lines      = explode("\n", $contents);
        $inDocBlock = false;
        $docBlock   = [];
        $docBlocks  = [];

        foreach ($lines as $num => $line)
        {
            $line    = trim($line);
            $lineLen = strlen($line);

            if (! $inDocBlock and $lineLen > 1 and $line[0] == '/' and $line[1] == '*')
            {
                // DocBlock start
                $inDocBlock = true;
                $docBlock   =
                [
                    'type' => '',
                    'text' => '',
                    'tags' => [],
                    'from' => $num + 1,
                    'to'   => $num + 1,
                    'file' => str_replace($this->config['input'] . '/', '', $path)
                ];
            }
            else if ($inDocBlock and $lineLen > 1 and $line[0] == '*' and $line[1] == '/')
            {
                // DocBlock end, cleaning...
                $docBlock['to']   = $num + 1;
                $docBlock['text'] = trim($docBlock['text']);

                // Add new block and rest
                $docBlocks[] = $docBlock;
                $inDocBlock  = false;
            }
            else if ($inDocBlock)
            {
                // Remove first comment chars (* )
                $line = $lineLen > 2 ? substr($line, 2) : '';

                // Tag or text ?
                if ($line === '' or $line[0] !== '@')
                {
                    // Collect text block
                    $docBlock['text'] .= "$line\n";
                }
                else
                {
                    // Tokkenize tag string
                    $tag = $this->parseDocBlockTag($line);

                    // Get the primary tag
                    if (in_array($tag['name'], $this->primaryTags))
                    {
                        $docBlock['type'] = $tag['name'];
                    }

                    // Register tag by type
                    if (in_array($tag['name'], $this->multipleTags))
                    {
                        $index  = $tag['name'] === 'param' ? 1 : 0;
                        $params = $tag['params'];
                        $name   = $params[$index];

                        if (is_array($params))
                        {
                            unset($params[$index]);
                        }

                        $docBlock['tags'][$tag['name']][$name] = $params;
                    }
                    else
                    {
                        $docBlock['tags'][$tag['name']] = $tag['params'];
                    }
                }
            }
        }

        return $docBlocks;
    }

    /**
     * Parse all dock bocks for a file.
     *
     * @protected
     * @method parseDocBlocks
     * @param  string $path
     * @param  array  $docBlocks
     */
    protected function parseDocBlocks($path, $docBlocks)
    {
        $namespace = null;
        $file      = null;
        $line      = null;
        $class     = null;
        $method    = null;

        foreach ($docBlocks as $docBlock)
        {
            if ($docBlock['type'] === 'namespace')
            {
                $namespace = $docBlock['tags']['namespace'];
            }
            else if ($docBlock['type'] === 'class')
            {
                $file  = $docBlock['file'];
                $line  = $docBlock['to'] + 1;
                $class = $docBlock['tags']['class'];

                $this->classes[$namespace][$class]['file'] = $file;
                $this->classes[$namespace][$class]['line'] = $line;
            }
            else if ($docBlock['type'] === 'method')
            {
                $tags   = $docBlock['tags'];
                $method = $tags['method'];

                unset($tags['method']);

                $this->classes[$namespace][$class]['methods'][$method] = $tags;
            }
            else if ($docBlock['type'] === 'property')
            {
                $tags     = $docBlock['tags'];
                $property = $tags['property'];

                unset($tags['property']);

                $this->classes[$namespace][$class]['properties'][$property] = $tags;
            }
        }
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
            $docBlocks = $this->parseFile($path);

            $this->parseDocBlocks($path, $docBlocks);
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

        var_dump($this->classes);
    }
}
