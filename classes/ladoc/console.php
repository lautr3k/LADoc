<?php
// @namespace LADoc
namespace LADoc;

/**
 * Documentation builder.
 *
 * @class Console
 */
class Console
{
    /**
     * Collection of data indexed by type.
     *
     * @protected
     * @property data
     * @type     array
    */
    protected $data = [];

    /**
     * Message add before next write.
     *
     * @protected
     * @property prependOnNextWrite
     * @type     string|null
    */
    protected $prependOnNextWrite = null;

    /**
     * Message add after next write.
     *
     * @protected
     * @property prependOnNextWrite
     * @type     string|null
    */
    protected $appendOnNextWrite = null;

    /**
     * Return a collection of all data prefixed by type or a group of data.
     *
     * @method getdata
     * @param  string [$type=null]
     * @return array
     */
    public function getData($type = null)
    {
        if ($type !== null) {
            return $this->data[$type];
        }

        return $type ? $this->data[$type] : $this->data;
    }

    /**
     * Return a collection of all data prefixed by type and indexed by microtime.
     *
     * @method getFlatData
     * @param  string [$type=null]
     * @return array
     */
    public function getFlatData($type = null)
    {
        $data = [];
        $max  = max(array_map('strlen', array_keys($this->data)));

        foreach($this->data as $type => $group) {
            $type = str_pad($type, $max);
            foreach($group as $microtime => $text) {
                $data[$microtime] = ucfirst($type) . ' >>> ' . $text;
            }
        }

        return $data;
    }

    /**
     * Return the string representation of (flat) data collection.
     *
     * @method __toString
     * @return string
     */
    public function __toString()
    {
        return implode("\n", $this->getFlatData());
    }

    /**
     * Write a (formated) text.
     *
     * @method log
     * @param  string  $type
     * @param  string  $text
     * @param  array   [$data=null]
     */
    public function write($type, $text, $data = null)
    {
        if ($this->prependOnNextWrite !== null) {
            $this->data[$type][microtime()] = $this->prependOnNextWrite;
            $this->prependOnNextWrite = null;
        }

        $this->data[$type][microtime()] = vsprintf($text, $data ?: []);

        if ($this->appendOnNextWrite !== null) {
            $this->data[$type][microtime()] = $this->appendOnNextWrite;
            $this->appendOnNextWrite = null;
        }
    }

    /**
     * Prepend a spacer before next write.
     *
     * @method spacer
     * @param  string  [$char='-']
     * @param  integer [$length=80]
     */
    public function spacer($char = '-', $length = 3)
    {
        $this->prependOnNextWrite = str_repeat('-', $length);
    }

    /**
     * Prepend a title before next write.
     *
     * @method title
     * @param  string $title
     */
    public function title($title, $data = null)
    {
        $title  = vsprintf($title, $data ?: []);
        $length = strlen($title) + 4;
        $line   = str_repeat('-', $length);
        $this->prependOnNextWrite = $line;
        $this->appendOnNextWrite  = $line;
        $this->write('', "| $title |");
    }

    /**
     * Log info message.
     *
     * @method info
     * @param  string $text
     * @param  array  [$data=null]
     */
    public function info($text, $data = null)
    {
        $this->write('', $text, $data);
    }

    /**
     * Log warning message.
     *
     * @method warning
     * @param  string $text
     * @param  array  [$data=null]
     */
    public function warning($text, $data = null)
    {
        $this->write('warning', $text, $data);
    }

    /**
     * Log and throw error message.
     *
     * @method error
     * @param  string $text
     * @param  array  [$data=null]
     * @throw  Error
     */
    public function error($text, $data = null)
    {
        $this->write('error', $text, $data);
        Error::raise($text, $data);
    }
}
