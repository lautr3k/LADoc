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
     * Warnings collection.
     *
     * @protected
     * @property warnings
     * @type     array
    */
    protected $warnings = [];

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
        'link'        => ['url', '?text'],
        'method'      => ['text'],
        'namespace'   => ['text'],
        'param'       => ['type', 'name', '?text'],
        'private'     => null,
        'property'    => ['text'],
        'protected'   => null,
        'public'      => null,
        'return'      => ['type', '?text'],
        'source'      => ['url', '?text'],
        'throw'       => ['type', '?text'],
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
     * @param string $test Test 1
     * @param string $test Test 2
     * @param string $test
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
     * Add an new warning message.
     *
     * @protected
     * @method warning
     * @param string $message
     * @param array  [$args]
     */
    protected function warning($message, $args = array())
    {
        $this->warnings[] = empty($args) ? $message : vsprintf($message, $args);
    }

    /**
     * Parse a DocBlock tag string.
     *
     * @protected
     * @method parseDocBlockTag
     * @param  string $tag
     * @return string
     */
    protected function parseDocBlockTag($tag, $file, $num)
    {
        $parts = array_filter(explode(' ', $tag));
        $name  = substr(array_shift($parts), 1);

        $params = $parts;

        if (! array_key_exists($name, $this->tags))
        {
            $this->warning('Unsupported tag: @%s [%s:%s]', [$name, $file, $num]);
            continue;
        }
        else if ($this->tags[$name] === null)
        {
            $params = true;
        }
        else
        {
            foreach ($this->tags[$name] as $key => $tagName)
            {
                $optional = strpos($tagName, '?') === 0;

                if ($optional)
                {
                    $tagName = substr($tagName, 1);
                }

                if (! isset($params[$key]))
                {
                    if (! $optional)
                    {
                        $args = [$name, implode('', $params), $tagName, $file, $num];
                        $this->warning('Missed argument: @%s %s -> %s <- [%s:%s]', $args);
                        continue;
                    }
                }
                else
                {
                    $params[$tagName] = $params[$key];
                    unset($params[$key]);
                }
            }
        }

        return ['name' => $name, 'params' => $params];
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
        $file       = str_replace($this->config['input'] . '/', '', $path);
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
                    'type' => '',
                    'from' => $num + 1,
                    'to'   => $num + 1,
                    'file' => $file,
                    'text' => '',
                    'tags' => [],
                ];
            }
            else if ($inDocBlock and strpos($line, '*/') === 0)
            {
                $inDocBlock = false;

                if ($docBlock['type'] !== '')
                {
                    $docBlock['to']   = $num + 1;
                    $docBlock['text'] = trim($docBlock['text']);
                    $docBlocks[]      = $docBlock;
                }
            }
            else if ($inDocBlock)
            {
                $line = preg_replace('/^\* ?/', '', $line);

                if ($line === '' or $line[0] !== '@')
                {
                    $docBlock['text'] .= "$line\n";
                }
                else
                {
                    $tag = $this->parseDocBlockTag($line, $file, $num);

                    if (in_array($tag['name'], $this->primaryTags))
                    {
                        $docBlock['type'] = $tag['name'];
                    }

                    if (in_array($tag['name'], $this->multipleTags))
                    {
                        $docBlock['tags'][$tag['name']][] = $tag['params'];
                    }
                    else
                    {
                        $docBlock['tags'][$tag['name']] = $tag['params'];
                    }
                }
            }
        }

        /**
         * Haaaaaaaaaaaaaaaaaaaaaaaaaa.
         * Hoooooooooooooooooooooooooo.
         * Hiiiiiiiiiiiiiiiiiiiiiiiiii.
         */

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
