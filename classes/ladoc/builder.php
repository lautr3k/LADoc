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
     * Regexp collection.
     *
     * @protected
     * @property patterns
     * @type     array
    */
    protected $patterns =
    [
        'text'   => '.+',
        'string' => '[^ ]+',
        'space'  => ' ',
        'spaces' => ' +',
    ];

    /**
     * Tags patterns indexed by tag name.
     *
     * @protected
     * @property tags
     * @type     array
    */
    protected $tags =
    [
        'author'      => 'name:text ?spaces ?link:string',
        'bootstrap'   => 'text:text',
        'class'       => 'name:string',
        'constructor' => null,
        'copyright'   => 'text:text',
        'extends'     => 'name:string',
        'license'     => 'name:string ?text:text',
        'method'      => 'name:string',
        'namespace'   => 'name:string',
        'param'       => 'type:string spaces name:string ?text:text',
        'property'    => 'name:string',
        'private'     => null,
        'protected'   => null,
        'public'      => null,
        'return'      => 'type:string ?text:text',
        'source'      => 'url:string ?text:text',
        'static'      => null,
        'throw'       => 'type:string ?text:text',
        'type'        => 'name:string',
        'version'     => 'number:string ?text:text'
    ];

    /**
     * Compiled tags patterns indexed by tag name.
     *
     * @protected
     * @property tags
     * @type     array
    */
    protected $tagsPatterns = [];

    /**
     * List of tags that can not be present more than once in the same block.
     *
     * @protected
     * @property primaryTags
     * @type     array
    */
    protected $primaryTags =
    [
        'bootstrap',
        'class',
        'constructor',
        'method',
        'namespace',
        'property'
    ];

    /**
     * List of tags that have no parameters.
     *
     * @protected
     * @property singleTags
     * @type     array
    */
    protected $singleTags =
    [
        'constructor',
        'private',
        'protected',
        'public',
        'static'
    ];

    /**
     * List of tags that can be present more than once in the same block.
     *
     * @protected
     * @property multipleTags
     * @type     array
    */
    protected $multipleTags =
    [
        'author',
        'copyright',
        'extends',
        'param',
        'return',
        'throw',
        'type'
    ];

    /**
     * Warnings messages collection.
     *
     * @protected
     * @property warnings
     * @type     array
    */
    protected $warnings = [];

    /**
     * DocBlocks collection.
     *
     * @protected
     * @property docBlocks
     * @type     array
    */
    protected $docBlocks = [];

    /**
     * Classes collection indexed by namespace.
     *
     * @protected
     * @property classes
     * @type     array
    */
    protected $classes = [];

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

        // Compile tags collection to regexp patterns collection
        $compiler           = [$this, 'compilePattern'];
        $this->tagsPatterns = array_filter(array_map($compiler, $this->tags));
    }

    /**
     * Compile tags collection to regexp patterns collection.
     *
     * @protected
     * @method compilePattern
     * @param  string $pattern
     * @return string
     */
    protected function compilePattern($pattern)
    {
        // If tag name only
        if ($pattern === null)
        {
            // Nothing to compile
            // Return null
            return null;
        }

        // Split pattern on spaces
        $params = array_filter(explode(' ', $pattern));

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
                return $this->patterns[$value] . ($optional ? '?' : '');
            }

            // Get the parameter name
            $name = $param[0];

            // Get the parameter value
            $value = $param[1];

            // Get the regexp in patterns list from the parameter value
            $pattern = $this->patterns[$value];

            // Compile the named part regexp
            return "(?P<$name>$pattern)" . ($optional ? '?' : '');

        }, $params);

        // Concact all compiled parts
        $pattern = implode('', $params);

        // Make and return the final regexp
        return "/$pattern(?P<_>.*)?/";
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
     * Log a warning message.
     *
     * @protected
     * @method warning
     * @param  string  $file
     * @param  integer $line
     * @param  string  $message
     * @param  array   [$data=[]]
     */
    protected function warning($file, $line, $message, $data = [])
    {
        $data = array_merge($data, [$file, $line]);
        $this->warnings[] = vsprintf($message . ' in %s:%s', $data);
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
     * @unknown unknownTag Unknown tag.
     * @class Bob
     */
    protected function parseFile($path)
    {
        // Get normalized file contents
        $contents = Helper::getFileContents($path);

        // Split contents on new line
        $lines = explode("\n", $contents);

        // Init parser variables
        $inDocBlock  = false;
        $docBlock    = [];
        $docBlocks   = [];
        $docBlockKey = 0;

        // For each line
        foreach ($lines as $num => $rawLine)
        {
            // Trim whitespace
            $line = trim($rawLine);

            // Get line number
            $lineNum = $num + 1;

            // If not in a DocBlock and start tag found
            if (! $inDocBlock and ($line === '/**' or $line === '/*'))
            {
                // Set we are in a DocBlock
                $inDocBlock = true;

                // Set the relative file path
                $file = str_replace($this->config['input'] . '/', '', $path);

                // Reset current block info
                $docBlock =
                [
                    'type'     => '',
                    'text'     => '',
                    'comments' => [],
                    'tags'     => [],
                    'from'     => $lineNum,
                    'to'       => $lineNum,
                    'file'     => $file
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

                // If no primary tag found
                if ($docBlock['type'] === '')
                {
                    // Decrement block number
                    $docBlockKey--;

                    // If not the first block in file
                    if (! empty($docBlocks))
                    {
                        // Split text found on new line
                        $comments = array_filter(explode("\n", $docBlock['text']));

                        // Merge comments in last block found
                        foreach ($comments as $num => $comment)
                        {
                            $num = $docBlock['from'] + $num + 1;
                            $docBlocks[$docBlockKey]['comments'][$num] = $comment;
                        }
                    }
                }

                // If primary tag found
                else
                {
                    // Save DocBlock info
                    $docBlocks[] = $docBlock;
                }

                // Set we are not in a DocBlock
                $inDocBlock = false;

                // Increment block number
                $docBlockKey++;

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

                    // If is an unknown tag
                    if (! array_key_exists($name, $this->tags))
                    {
                        // Log warning message
                        $this->warning($file, $lineNum, 'Unknown tag [@%s]', [$name]);

                        // Go to next line
                        continue;
                    }

                    // If primary tag found
                    if (in_array($name, $this->primaryTags))
                    {
                        // If already set
                        if ($docBlock['type'] !== '')
                        {
                            // Log warning message
                            $message = 'Try to redefine [@%s] to [@%s]';
                            $data    =  [$docBlock['type'], $name];
                            $this->warning($file, $lineNum, $message, $data);

                            // Go to next line
                            continue;
                        }

                        // Set DocBlock type (tag name)
                        $docBlock['type'] = $name;
                    }

                    // If tag without parameters
                    if (in_array($name, $this->singleTags))
                    {
                        // Add single tag to collection
                        $docBlock['tags'][$name] = true;

                        // Go to next line
                        continue;
                    }

                    // Extract arguments
                    $args = isset($args[1]) ? trim($args[1]) : '';

                    // Parse arguments
                    preg_match($this->tagsPatterns[$name], $args, $params);

                    // If no parameter found
                    if (empty($params))
                    {
                        // Log warning message
                        $message = 'Malformed parameters for [@%s] expected [%s]';
                        $data    =  [$name, $this->tags[$name]];
                        $this->warning($file, $lineNum, $message, $data);

                        // Go to next line
                        continue;
                    }

                    // Clean parameters found
                    array_shift($params);
                    $params = array_unique($params);
                    $params = array_map('trim', $params);

                    // If too many parameters
                    if (! empty($params['_']))
                    {
                        // Log warning message
                        $message = 'Too many parameters for [@%s] expected [%s]';
                        $data    =  [$name, $this->tags[$name]];
                        $this->warning($file, $lineNum, $message, $data);
                    }

                    // If multiple tag type
                    if (in_array($name, $this->multipleTags))
                    {
                        // Append tag
                        $docBlock['tags'][$name][] = $params;
                    }

                    // If single tag
                    else
                    {
                        // Set tag
                        $docBlock['tags'][$name] = $params;
                    }

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

            // If single line comment found and not the first block
            if (! empty($docBlocks) and strpos($line, '//') === 0)
            {
                // Remove start comment chars
                $line = trim(substr($line, 2));

                // Add comment in last block found
                $docBlocks[$docBlockKey-1]['comments'][$lineNum + 1] = $line;
            }
        }

        // Return docBlocks collection
        return $docBlocks;
    }

    /**
     * Parse the files tree.
     *
     * @protected
     * @author
     * @method parseFilesTree
     */
    protected function parseFilesTree()
    {
        // For each file in tree, extract all DocBlocks
        $this->docBlocks = array_map([$this, 'parseFile'], $this->filesTree);

        // Reduce the DocBlocks collection to one dimension collection
        $this->docBlocks = array_reduce($this->docBlocks, 'array_merge', []);

        // Init parser variables
        $currentNs    = null;
        $currentClass = null;

        // For each DocBlock in collection
        foreach ($this->docBlocks as $docBlock)
        {
            // If namespace type
            if ($docBlock['type'] === 'namespace')
            {
                // Set as current namespace
                $namespace = $docBlock['tags']['namespace']['name'];
                $currentNs = &$this->classes[$namespace];

                // Go to next block
                continue;
            }

            // If class type
            if ($docBlock['type'] === 'class')
            {
                // Set as current class
                $className    = $docBlock['tags']['class']['name'];
                $currentClass = &$currentNs[$className];

                // Set file and line number
                $currentClass['file'] = $docBlock['file'];
                $currentClass['line'] = $docBlock['to'] + 1;

                // Go to next block
                continue;
            }

            // If method type
            if ($docBlock['type'] == 'method')
            {
                // Get the method name
                $methodName = $docBlock['tags']['method']['name'];

                // Remove method tag from tags list
                unset($docBlock['tags']['method']);

                // Add method tags list to current class
                $currentClass['methods'][$methodName] = $docBlock['tags'];

                // Go to next block
                continue;
            }

            // If property type
            if ($docBlock['type'] == 'property')
            {
                // Get property name
                $propertyName = $docBlock['tags']['property']['name'];

                // Remove property tag from tags list
                unset($docBlock['tags']['property']);

                // Add property tags list to current class
                $currentClass['properties'][$propertyName] = $docBlock['tags'];

                // Go to next block
                continue;
            }
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
        var_dump($this->warnings);
        var_dump($this->filesTree);
        var_dump($this->classes);
        var_dump($this->docBlocks);
    }
}
