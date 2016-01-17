<?php
// @namespace LADoc
namespace LADoc;

/**
 * Output wrapper.
 *
 * @class Output
 */
class Output
{
    // @protected @property array buffer
    protected $buffer = [];

    /**
     * Output verbosity.
     *
     * @protected
     * @property verbosity
     * @type     null|array
    */
    protected $verbosity = null;

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
     * Set output verbosity.
     *
     * @method setVerbosity
     * @param  null|string|array [$verbosity=null]
     */
    public function setVerbosity($verbosity = null)
    {
        if (is_string($verbosity)) {
            $verbosity = array_map('trim', explode('|', $verbosity));
        }
        $this->verbosity = $verbosity;
    }

    /**
     * Get buffer data.
     *
     * @method getBuffer
     * @param  string [$type=null]
     * @return array
     */
    public function getBuffer($type = null)
    {
        // If no type provided.
        if ($type === null) {
            // Return the buffer array.
            return $this->buffer;
        }

        // If the group is defined.
        if (isset($this->buffer[$type])) {
            // Return the group array.
            return $this->buffer[$type];
        }

        // Else return an empty array.
        return [];
    }

    /**
     * Return rendered buffer.
     *
     * @method render
     * @return string
     */
    public function render()
    {
        $output = [];
        $buffer = $this->buffer;

        foreach ($buffer as $type => $lines) {
            if ($this->verbosity and ! in_array($type, $this->verbosity)) {
                continue;
            }
            foreach ($lines as $microtime => $line) {
                $output[$microtime] = $line;
            }
        }

        ksort($output);
        return implode("\n", $output);
    }

    /**
     * Print the buffer.
     *
     * @method display
     */
    public function display()
    {
        echo($this->render());
    }

    /**
     * Return the string representation of (flat) data collection.
     *
     * @method __toString
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Write (formated) message(s).
     *
     * @method write
     * @param  string       $type
     * @param  string|array $text
     * @param  array        [$data=null]
     */
    public function write($type, $text, $data = null)
    {
        $max = 0;

        if (is_array($text)) {
            $max = max(array_map('strlen', array_keys($text)));
        }

        if ($this->prependOnNextWrite !== null) {
            $texts = $this->prependOnNextWrite;
            $this->prependOnNextWrite = null;
            foreach ($texts as $value) {
                $this->write($value[0], $value[1]);
            }
        }

        foreach ((array) $text as $key => $text) {
            if (is_array($text)) {
                $text = json_encode($text, JSON_UNESCAPED_SLASHES);
            }

            if (is_array($data)) {
                $data = array_map(function($value) {
                    return json_encode($value, JSON_UNESCAPED_SLASHES);
                }, $data);
            }

            if ($max > 0 and is_string($key)) {
                $text = str_pad($key, $max) . ': ' . $text;
            }

            $this->buffer[$type][microtime()] = vsprintf($text, $data ?: []);
        }

        if ($this->appendOnNextWrite !== null) {
            $texts = $this->appendOnNextWrite;
            $this->appendOnNextWrite = null;
            foreach ($texts as $value) {
                $this->write($value[0], $value[1]);
            }
        }
    }

    /**
     * Write a formatted title.
     *
     * @method writeTitle
     * @param  string $title
     * @param  array  [$data=null]
     */
    public function writeTitle($title, $data = null)
    {
        $title = vsprintf($title, $data ?: []);
        $line  = str_repeat('-', 80);

        $this->prependOnNextWrite[] = ['title', $line];
        foreach (explode("\n", wordwrap($title, 76, "\n", true)) as $title) {
            $title = str_pad($title, 76);
            $this->prependOnNextWrite[] = ['title', "| $title |"];
        }
        $this->prependOnNextWrite[] = ['title', $line];
    }

    /**
     * Write a spacer.
     *
     * @method writeSpacer
     * @param  integer [$length=0]
     * @param  string  [$char='-']
     */
    public function writeSpacer($length = 0, $char = '-')
    {
        $this->prependOnNextWrite[] = ['spacer', str_repeat($char, $length)];
    }

    /**
     * Write info message(s).
     *
     * @method writeInfo
     * @param  string|array $text
     * @param  array        [$data=null]
     */
    public function writeInfo($text, $data = null)
    {
        $this->write('info', $text, $data);
    }

    /**
     * Write verbose message(s).
     *
     * @method writeInfo
     * @param  string|array $text
     * @param  array        [$data=null]
     */
    public function writeVerbose($text, $data = null)
    {
        $this->write('verbose', $text, $data);
    }

    /**
     * Write warning message(s).
     *
     * @method writeWarning
     * @param  string|array $text
     * @param  array        [$data=null]
     */
    public function writeWarning($text, $data = null)
    {
        $this->write('warning', $text, $data);
    }

    /**
     * Write error message(s).
     *
     * @method writeError
     * @param  string|array $text
     * @param  array        [$data=null]
     */
    public function writeError($text, $data = null)
    {
        $this->write('error', $text, $data);
    }

    /**
     * Write and throw error message(s).
     *
     * @method writeAndThrowError
     * @param  string|array $text
     * @param  array        [$data=null]
     * @throw  Error
     */
    public function writeAndThrowError($text, $data = null)
    {
        $this->writeError($text, $data);
        Error::raise($text, $data);
    }
}
