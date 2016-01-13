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
        // Get normalized file contents
        $contents = Helper::getFileContents($path);

        // Split contents on new line
        $lines = explode("\n", $contents);

        // Init parser variables
        $inDocBlock = false;
        $docBlock   = [];
        $docBlocks  = [];

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

                // Save DocBlock info
                $docBlocks[] = $docBlock;

                // Set we are not in a DocBlock
                $inDocBlock = false;

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

                    // Extract arguments
                    $args = isset($args[1]) ? trim($args[1]) : '';

                    // Debugage...
                    var_dump([$name, $args]);

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

            // If not in DocBlock. Collect single line comments
            // todo...
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
        var_dump($this->filesTree);
    }
}
