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
        'author'      => 1,
        'class'       => 1,
        'constructor' => 0,
        'copyright'   => 1,
        'extend'      => 1,
        'license'     => 1,
        'link'        => 1,
        'method'      => 1,
        'namespace'   => 1,
        'param'       => 3,
        'private'     => 0,
        'property'    => 1,
        'protected'   => 0,
        'public'      => 0,
        'return'      => 2,
        'source'      => 1,
        'throw'       => 1,
        'static'      => 0,
        'type'        => 1,
        'use'         => 1,
        'version'     => 1
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
        $tokken        = ['name' => $tag, 'params' => null];

        if (! $firstSpacePos)
        {
            return $tokken;
        }

        $tag    = preg_replace('|[ ]++|', ' ', $tag);
        $name   = substr($tag, 0, $firstSpacePos);
        $value  = substr($tag, $firstSpacePos + 1);
        $limit  = isset($this->tags[$name]) ? $this->tags[$name] : 2;
        $params = array_pad(explode(' ', $value, $limit), $limit, null);

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
     * @param string $path
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
                    'type' => null,
                    'text' => '',
                    'tags' => [],
                    'from' => $num + 1,
                    'to'   => null,
                    'file' => str_replace($this->config['input'] . '/', '', $path)
                ];
            }
            else if ($inDocBlock and $lineLen > 1 and $line[0] == '*' and $line[1] == '/')
            {
                // DocBlock end
                $docBlock['to'] = $num + 1;
                $docBlocks[]    = $docBlock;
                $inDocBlock     = false;
            }
            else if ($inDocBlock)
            {
                $line = $lineLen > 2 ? substr($line, 2) : '';

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
                        $docBlock['tags'][$tag['name']][] = $tag;
                    }
                    else
                    {
                        $docBlock['tags'][$tag['name']] = $tag;
                    }
                }
            }
        }

        var_dump($docBlocks);
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
            $this->parseFile($path);
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

        var_dump($this->filesTree);
    }
}
