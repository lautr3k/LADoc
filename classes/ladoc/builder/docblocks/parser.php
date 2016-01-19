<?php
// @namespace LADoc\Builder\DocBlocks
namespace LADoc\Builder\DocBlocks;

use \LADoc\Builder\Files\Tree;
use \LADoc\Builder\Files\File;

/**
 * DocBlocks parser.
 *
 * @class Parser
 * @use   \LADoc\Builder\Files\Tree
 * @use   \LADoc\Builder\Files\File
 * @use   Line
 */
class Parser
{
    /**
     * Files tree instance.
     *
     * @protected
     * @property filesTree
     * @type     \LADoc\Builder\Files\Tree
    */
    protected $filesTree = null;

    /**
     * DocBlocks collection.
     *
     * @protected
     * @property docBlocks
     * @type     array
    */
    protected $docBlocks = null;

    /**
     * Patterns collection.
     *
     * @static
     * @protected
     * @property patterns
     * @type     array
    */
    static protected $patterns =
    [
        'text'   => '.+',
        'string' => '[^ ]+',
        'space'  => ' ',
        'spaces' => ' +',
    ];

    /**
     * Tags definitions.
     *
     * @static
     * @protected
     * @property tags
     * @type     array
    */
    static protected $tags =
    [
        'author' => [
            'pattern' => 'name:text ?spaces ?link:string'
        ],
        'bootstrap' => [
            'pattern' => 'text:text'
        ],
        'class' => [
            'pattern' => 'name:string'
        ],
        'constructor' => [
            'pattern' => null
        ],
        'copyright' => [
            'pattern' => 'text:text'
        ],
        'extends' => [
            'pattern' => 'name:string'
        ],
        'license' => [
            'pattern' => 'name:string ?text:text'
        ],
        'main' => [
            'pattern' => null
        ],
        'method' => [
            'pattern' => 'name:string'
        ],
        'namespace' => [
            'pattern' => 'name:string'
        ],
        'param' => [
            'pattern' => 'type:string spaces name:string ?text:text'
        ],
        'property' => [
            'pattern' => 'name:string'
        ],
        'private' => [
            'pattern' => null
        ],
        'protected' => [
            'pattern' => null
        ],
        'public' => [
            'pattern' => null
        ],
        'return' => [
            'pattern' => 'type:string ?text:text'
        ],
        'source' => [
            'pattern' => 'url:string ?text:text'
        ],
        'static' => [
            'pattern' => null
        ],
        'throw' => [
            'pattern' => 'type:string ?text:text'
        ],
        'type' => [
            'pattern' => 'name:string'
        ],
        'version' => [
            'pattern' => 'number:string ?text:text'
        ]
    ];

    /**
     * Compiled tags patterns indexed by tag name.
     *
     * @protected
     * @property compiledTagsPatterns
     * @type     array
    */
    protected $compiledTagsPatterns = [];

    /**
     * Class constructor.
     *
     * @constructor
     * @param \LADoc\Builder\Files\Tree $filesTree
     */
    public function __construct(Tree $filesTree)
    {
        // Set {@class \LADoc\Builder\Files\Tree files tree} instance.
        $this->filesTree = $filesTree;

        // Compile tags regexp patterns.
        array_walk(self::$tags, [$this, 'compileTagsPatterns'], $this->compiledTagsPatterns);

        var_dump($this->compiledTagsPatterns);
    }

    /**
     * Compile tags regexp patterns.
     *
     * @protected
     * @method compileTagsPatterns
     * @param  string $tag
     * @return string
     */
    protected function compileTagsPatterns($tag, $name)
    {
        // If no pattern for this tag.
        if ($tag['pattern'] === null) {
            // Return null.
            return null;
        }

        // Split pattern on spaces
        $params = array_filter(explode(' ', $tag['pattern']));

        // For each parameter
        $params = array_map(function($param)
        {
            // Test if it is an optional parameter
            $optional = $param[0] === '?';

            // If it is an optional parameter
            if ($optional)
            {
                // Remove optional parameter mark
                $param = substr($param, 1);
            }

            // Split parameter on colons
            $param = explode(':', $param);

            // If not an named parameter
            if (! isset($param[1]))
            {
                // Get the parameter value
                $value = $param[0];

                // Return the regexp in patterns list from the parameter value
                return self::$patterns[$value] . ($optional ? '?' : '');
            }

            // Get the parameter name
            $name = $param[0];

            // Get the parameter value
            $value = $param[1];

            // Get the regexp in patterns list from the parameter value
            $pattern = self::$patterns[$value];

            // Compile the named part regexp
            return "(?P<$name>$pattern)" . ($optional ? '?' : '');

        }, $params);

        // Concact all compiled parts
        $pattern = implode('', $params);

        // Make and return the final regexp
        return $this->compiledTagsPatterns[$name] = "/$pattern(?P<_>.*)?/";
    }

    /**
     * Parse a file.
     *
     * - Extract DocBlocks.
     * - Tokkenize DocBlocks.
     *
     * @protected
     * @method parseFile
     * @param  \LADoc\Builder\Files\File $file
     */
    protected function parseFile(File $file)
    {
        // Reset parser properties.
        $this->inDocBlock = false;
        $this->line       = null;
        $this->docBlock   = null;
        $this->docBlocks  = [];

        // For each line.
        foreach ($file->getLines() as $num => $contents)
        {
            // Create/Set current line instance.
            $this->line = new Line($num, $contents, $this->inDocBlock);

            // If the line start a DocBlock.
            if ($this->line->isType('docBlockStart'))
            {
                // Set we are in a DocBlock.
                $this->inDocBlock = true;

                // Create new DocBlock instance.
                $this->docBlock = new DocBlock($file);

                // Set the line start number.
                $this->docBlock->setLineNumber('from', $num);

                // Go to next line.
                continue;
            }

            // If the line end a DocBlock.
            if ($this->line->isType('docBlockEnd'))
            {
                // Validate the DocBlock.

                // Set the line end number.
                $this->docBlock->setLineNumber('to', $num);

                // Register the DocBlock.
                $this->docBlocks[] = $this->docBlock;

                // Set we are not in a DocBlock.
                $this->inDocBlock = false;

                // Go to next line.
                continue;
            }

            // If the line is in a DocBlock.
            if ($this->line->isType('docBlockData'))
            {
                // Parse DocBlock line.

                // Go to next line.
                continue;
            }

            // If the line is a single line comment.
            if ($this->line->isType('singleLineComment'))
            {
                // Parse comment line.

                // Add the comment line to last DocBlock.

                // Go to next line.
                continue;
            }
        }
    }

    /**
     * Extract all DocBlocks.
     *
     * @protected
     * @method extractDocBlocks
     */
    protected function extractDocBlocks()
    {
        // For each file in tree, extract DocBlocks
        array_map([$this, 'parseFile'], $this->filesTree->getFiles('includedFiles'));
    }

    /**
     * Parse the files tree.
     *
     * @method parse
     */
    public function parse()
    {
        // Extract all DocBlocks.
        $this->extractDocBlocks();
    }
}
