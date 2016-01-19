<?php
// @namespace LADoc\Builder\DocBlocks
namespace LADoc\Builder\DocBlocks;

/**
 * Line class.
 *
 * @class Parser
 */
class Line
{
    // @protected @property integer num
    protected $num = null;

    // @protected @property string type
    protected $type = null;

    // @protected @property string raw
    protected $raw = null;

    // @protected @property string text
    protected $text = null;

    // @protected @property boolean inDocBlock
    protected $inDocBlock = null;

    /**
     * Class constructor.
     *
     * @constructor
     * @param integer $num
     * @param string  $contents
     * @param boolean $inDocBlock
     */
    public function __construct($num, $contents, $inDocBlock)
    {
        // Set line number.
        $this->num = $num;

        // Set raw contents.
        $this->raw = $contents;

        // Set normalized contents.
        $this->text = trim($contents);

        // Set if in an DocBlock.
        $this->inDocBlock = $inDocBlock;

        // Get/Set the type.
        $this->getType();
    }

    /**
     * Get, set and return the line type.
     *
     * @param  null|string [$type=null]
     * @return string|boolean
     */
    public function getType($type = null)
    {
        // If not already set.
        if ($this->type) {
            // Return the type.
            return $this->type;
        }

        // If not in a DocBlock and start tag found.
        if (! $this->inDocBlock and ($this->text === '/**' or $this->text === '/*')) {
            // Set we are in a DocBlock.
            $this->inDocBlock  = true;
            
            // Set to: docBlockStart.
            return $this->type = 'docBlockStart';
        }

        // If in a DocBlock and end tag found.
        if ($this->inDocBlock and $this->text === '*/') {
            // Set to: singleLineComment.
            return $this->type = 'docBlockEnd';
        }

        // If in DocBlock.
        if ($this->inDocBlock) {
            // Remove possible start comment char in text.
            $this->text = trim(preg_replace('/^\*/', '', $this->text));

            // Set to: singleLineComment.
            return $this->type = 'docBlockData';
        }

        // If not in a DocBlock and single line comment found.
        if (strpos($this->text, '//') === 0) {
            // Remove start comment char in text.
            $this->text = trim(substr($this->text, 2));

            // Set to: singleLineComment.
            return $this->type = 'singleLineComment';
        }

        // If nothing detected, set to: data.
        return $this->type = 'data';
    }

    /**
     * Test if the line is from type.
     *
     * @param  string $type
     * @return boolean
     */
    public function isType($type)
    {
        return $this->type === $type;
    }
}
