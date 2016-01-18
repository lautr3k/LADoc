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
     * Return if type must be diplayed.
     *
     * @method mustDisplay
     * @param  string $type
     * @return boolean
     */
    public function mustDisplay($type)
    {
        return $this->verbosity and in_array($type, $this->verbosity);
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
            if (! $this->mustDisplay($type)) {
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
     * Return encoded (printable) data.
     *
     * @method encodeData
     * @param  array $data
     */
    static public function encodeData($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        }
        return $data;
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
                $this->write($type, $value);
            }
        }

        foreach ((array) $text as $key => $text) {
            $text = $this->encodeData($text);

            if (is_array($data)) {
                $data = array_map([$this, 'encodeData'], $data);
            }

            if ($max > 0 and is_string($key)) {
                $text = str_pad($key, $max) . ': ' . $text;
            }

            $this->buffer[$type][microtime()] = vsprintf($text, $data ?: []);
        }
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
        $this->prependOnNextWrite[] = str_repeat($char, $length);
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
        $title = wordwrap($title, 76, "\n", true);
        $lines = explode("\n", $title);

        $this->writeSpacer(80);
        foreach ($lines as $title) {
            $title = str_pad($title, 76);
            $this->prependOnNextWrite[] = "| $title |";
        }
        $this->writeSpacer(80);
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
