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
        'author'      => ['text'],
        'class'       => ['text'],
        'constructor' => null,
        'copyright'   => ['text'],
        'extend'      => ['text'],
        'license'     => ['text'],
        'link'        => ['url', 'text?'],
        'method'      => ['text'],
        'namespace'   => ['text'],
        'param'       => ['type', 'name', 'text?'],
        'private'     => null,
        'property'    => ['text'],
        'protected'   => null,
        'public'      => null,
        'return'      => ['type', 'text?'],
        'source'      => ['url', 'text?'],
        'throw'       => ['type', 'text?'],
        'static'      => null,
        'type'        => ['text'],
        'use'         => ['text'],
        'version'     => ['text']
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
     * Parse a DocBlock tag string.
     *
     * @protected
     * @method parseDocBlockTag
     * @param  string $tag
     * @return array
     */
    protected function parseDocBlockTag($tag)
    {
        $pattern = "^@(?P<name>[a-z]+)";

        preg_match("/$pattern/", $tag, $matches);

        $matches = array_unique($matches);

        unset($matches[0]);

        var_dump($matches);

        $name = $matches['name'];

        $tokken = ['name' => $name, 'params' => $matches];

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
            $line = trim($line);

            if (! $inDocBlock and strpos($line, '/*') === 0)
            {
                $inDocBlock = true;
                $docBlock   =
                [
                    'from' => $num + 1,
                    'to'   => $num + 1,
                    'file' => str_replace($this->config['input'] . '/', '', $path),
                    'type' => '',
                    'text' => '',
                    'tags' => [],
                ];
            }
            else if ($inDocBlock and strpos($line, '*/') === 0)
            {
                $docBlock['to']   = $num + 1;
                $docBlock['text'] = trim($docBlock['text']);

                $docBlocks[] = $docBlock;
                $inDocBlock  = false;
            }
            else if ($inDocBlock)
            {
                $line = strlen($line) > 2 ? substr($line, 2) : '';

                if ($line === '' or $line[0] !== '@')
                {
                    $docBlock['text'] .= "$line\n";
                }

            }
        }

        return $docBlocks;
    }

    /**
     * Build the documentation.
     *
     * @method build
     */
    public function build()
    {
        $this->filesTree = Helper::scanPath
        (
            $this->config['input'],
            $this->config['includes'],
            $this->config['excludes']
        );

        $docBlocks = array_map(array($this, 'parseFile'), $this->filesTree);

        var_dump($docBlocks);
    }
}
