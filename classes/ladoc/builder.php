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
 * Documentation builder.
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
        'includes' => ['*.php', '*.md'],
        'excludes' => ['docs', '.git', '*Copie.php']
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
     * Tags regexp collection, indexed by tag name.
     *
     * @protected
     * @property tags
     * @type     array
    */
    protected $tags =
    [
        'author'      => '',
        'class'       => '',
        'constructor' => '',
        'copyright'   => '',
        'extends'     => '',
        'license'     => '',
        'method'      => '',
        'namespace'   => '',
        'param'       => '',
        'property'    => '',
        'protected'   => '',
        'return'      => '',
        'source'      => '',
        'static'      => '',
        'throw'       => '',
        'type'        => '',
        'version'     => ''
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
     * Warnings messages collection.
     *
     * @protected
     * @property warnings
     * @type     array
    */
    protected $warnings = [];

    /**
     * Class constructor.
     *
     * @constructor
     */
    public function __construct()
    {
        // Merge user configuration with defaults values
        $this->config = array_merge($this->config, $_GET);

        // Normalize and test if input and output path exists
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
            $this->config['includes'],
            $this->config['excludes']
        );
    }

    /**
     * Log a warning message.
     *
     * @protected
     * @method warning
     * @param  string  $file
     * @param  integer $line
     * @param  string  $message
     * @param  array   [$data=[]]
     */
    protected function warning($file, $line, $message, $data = [])
    {
        $data = array_merge($data, [$file, $line]);
        $this->warnings[] = vsprintf($message . ' in %s:%s', $data);
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
     * @unknown unknownTag Unknown tag.
     * @class Bob
     */
    protected function parseFile($path)
    {
        // Get normalized file contents
        $contents = Helper::getFileContents($path);

        // Split contents on new line
        $lines = explode("\n", $contents);

        // Init parser variables
        $inDocBlock  = false;
        $docBlock    = [];
        $docBlocks   = [];
        $docBlockKey = 0;

        // For each line
        foreach ($lines as $num => $rawLine)
        {
            // Trim whitespace
            $line = trim($rawLine);

            // If not in a DocBlock and start tag found
            if (! $inDocBlock and ($line === '/**' or $line === '/*'))
            {
                // Set we are in a DocBlock
                $inDocBlock = true;

                // Set the relative file path
                $file = str_replace($this->config['input'] . '/', '', $path);

                // Set the start line num
                $line = $num + 1;

                // Reset current info collection
                $docBlock =
                [
                    'type' => '',
                    'text' => '',
                    'list' => [],
                    'tags' => [],
                    'from' => $line,
                    'to'   => $line,
                    'file' => $file
                ];

                // Go to next line
                continue;
            }

            // If in a DocBlock and end tag found
            if ($inDocBlock and $line === '*/')
            {
                // Increment end line number
                $docBlock['to']++;

                // Trim collected text
                $docBlock['text'] = trim($docBlock['text']);

                // If no primary tag found
                if ($docBlock['type'] === '')
                {
                    // Split text on new line
                    $list = array_filter(explode("\n", $docBlock['text']));

                    // Decrement block number
                    $docBlockKey--;

                    // Collect verbose comment in last block found
                    $docBlocks[$docBlockKey]['list'] = array_merge($docBlocks[$docBlockKey]['list'], $list);
                }

                // If valid DocBlock
                else
                {
                    // Save DocBlock info
                    $docBlocks[] = $docBlock;
                }

                // Set we are not in a DocBlock
                $inDocBlock = false;

                // Increment block number
                $docBlockKey++;

                // Go to next line
                continue;
            }

            // If in DocBlock
            if ($inDocBlock)
            {
                // Increment end line number
                $docBlock['to']++;

                // Remove possible start comment char
                $line = trim(preg_replace('/^\*/', '', $line));

                // If start tag found
                if (isset($line[0]) and $line[0] === '@')
                {
                    // Split line on first space
                    $args = explode(' ', $line, 2);

                    // Extract tag name
                    $name = substr($args[0], 1);

                    // If is an unknown tag
                    if (! isset($this->tags[$name]))
                    {
                        // Log warning message
                        $this->warning($file, $num, 'Unknown tag [@%s]', [$name]);

                        // Go to next line
                        continue;
                    }

                    // If primary tag found
                    if (in_array($name, $this->primaryTags))
                    {
                        // If already set
                        if ($docBlock['type'] !== '')
                        {
                            // Log warning message
                            $message = 'Primary tag already defined as [@%s]. ';
                            $message.= 'You can not redefine it to [@%s]';
                            $data    =  [$docBlock['type'], $name];
                            $this->warning($file, $num, $message, $data);

                            // Go to next line
                            continue;
                        }

                        // Set DocBlock type (tag name)
                        $docBlock['type'] = $name;
                    }

                    // Extract arguments
                    $args = isset($args[1]) ? trim($args[1]) : '';

                    // Parse arguments
                    //var_dump([$name, $args]);

                    // Go to next line
                    continue;
                }

                // Else collect text
                $docBlock['text'] .= "$line\n";

                // Go to next line
                continue;
            }

            /**
             * Dumy comment...
             *
             * No tags...
             */

            // If single line comment found
            if (strpos($line, '//') === 0)
            {
                // Remove start comment chars
                $line = trim(substr($line, 2));

                // Collect verbose comment in last block found
                $docBlocks[$docBlockKey-1]['list'][] = $line;
            }
        }

        // Return docBlocks collection
        return $docBlocks;
    }

    /**
     * Parse the files tree.
     *
     * @protected
     * @method parseFilesTree
     */
    protected function parseFilesTree()
    {
        // For each file in tree
        foreach ($this->filesTree as $path)
        {
            // Extract all docBlocks
            $docBlocks = $this->parseFile($path);

            // Debugage...
            var_dump($docBlocks);
        }
    }

    /**
     * Build the documentation.
     *
     * @method build
     */
    public function build()
    {
        // Get the file tree
        $this->getFilesTree();

        // Parse the file tree
        $this->parseFilesTree();

        // Debugage...
        var_dump($this->warnings);
        var_dump($this->filesTree);
    }
}
