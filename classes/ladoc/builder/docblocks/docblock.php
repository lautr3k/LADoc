<?php
// @namespace LADoc\Builder\DocBlocks
namespace LADoc\Builder\DocBlocks;

use \LADoc\Builder\Files\File;

/**
 * DocBlock class.
 *
 * @class Parser
 * @use   \LADoc\Builder\Files\File
 */
class DocBlock
{
    // @protected @property string type
    protected $type = null;

    // @protected @property string title
    protected $title = null;

    // @protected @property array description
    protected $description = null;

    // @protected @property array comments
    protected $comments = null;

    // @protected @property array tags
    protected $tags = null;

    // @protected @property integer fromLine
    protected $fromLine = null;

    // @protected @property integer toLine
    protected $toLine = null;

    // @protected @property LADoc\Builder\Files\File file
    protected $file = null;

    /**
     * Class constructor.
     *
     * @constructor
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Set the line number 'from' or 'to'.
     *
     * @method setLineNumber
     * @param  string  $type
     * @param  integer $num
     */
    public function setLineNumber($type, $num)
    {
        $property = $type . 'Line';
        $this->$property = $num;
    }
}
