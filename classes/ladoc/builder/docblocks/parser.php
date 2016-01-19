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
     * Class constructor.
     *
     * @constructor
     * @param \LADoc\Builder\Files\Tree $filesTree
     */
    public function __construct(Tree $filesTree)
    {
        // Set {@class \LADoc\Builder\Files\Tree files tree} instance.
        $this->filesTree = $filesTree;
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
                var_dump($this->docBlock);

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
