<?php
// @namespace LADoc
namespace LADoc;

 /**
  * Exception wrapper with message formatting option.
  *
  * @class  Error
  * @extend \ErrorException
  */
class Error extends \ErrorException
{
    /**
     * Class constructor.
     *
     * __Usage :__
     *
     *     // Without text formatting.
     *     throw new Error('Unknown error: Empty string.');
     *
     *     // With text formatting.
     *     throw new Error('%s error: %s', ['Unknown', 'Empty string.']);
     *
     * @constructor
     * @param string     $message
     * @param array|null [$data=null]
     */
    public function __construct($message, $data = null)
    {
        $this->message = $data ? vsprintf($message, $data) : $message;
    }

    /**
     * Convenient static method to throw an error exception.
     *
     *  __Usage :__
     *
     *      // Without text formatting.
     *      Error::raise('Unknown error: Empty string.');
     *
     *      // With text formatting.
     *      Error::raise('%s error: %s', ['Unknown', 'Empty string.']);
     *
     * @static
     * @method raise
     * @param  string     $message
     * @param  array|null [$data=null]
     * @throw  Error
     */
    public static function raise($message, $data = null)
    {
        throw new self($message, $data);
    }
}
