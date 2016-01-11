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
  * Exception wrapper with message formatting option.
  *
  * @class   Error
  * @extends \ErrorException
  */
class Error extends \ErrorException
{
    /**
     * Exception wrapper with message formatting option.
     *
     *  **Usage:**
     *
     *      // Without arguments.
     *      throw new Error('Unknown error: Empty string.');
     *
     *      // With arguments.
     *      throw new Error('%s error: %s', ['Unknown', 'Empty string.']);
     *
     * @constructor
     * @param string $message
     * @param array  [$args]
     */
    public function __construct($message, $args = array())
    {
        // If arguments provided
        if (! empty($args))
        {
            // Format the message
            $message = vsprintf($message, $args);
        }

        // Set message
        $this->message = $message;
    }

    /**
     * Convenient static method to throw an error exception.
     *
     *  **Usage:**
     *
     *      // Without arguments.
     *      Error::raise('Unknown error: Empty string.');
     *
     *      // With arguments.
     *      Error::raise('%s error: %s', ['Unknown', 'Empty string.']);
     *
     * @static
     * @method raise
     * @param  string $message
     * @param  array  [$args]
     * @throws Error
     */
    public static function raise($message, $args = array())
    {
        throw new self($message, $args);
    }
}
