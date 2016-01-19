<?php
/**
 * LADoc - Front controller.
 *
 *     __Example:__
 *         - Bla bla bla...
 *         - Bla bla bla...
 *         - Bla bla bla...
 *         - Bla bla bla...
 *
 * @bootstrap LADoc
 */

// @const string LADOC_ROOT_PATH Define the absolute root path (force unix style).
define('LADOC_ROOT_PATH', str_replace('\\', '/', __DIR__));

// @const string LADOC_CLASSES_PATH Define the absolute classes path (unix style).
define('LADOC_CLASSES_PATH', LADOC_ROOT_PATH . '/classes');

/**
 * @function loadClass Try to load a class based on his name.
 * @param    string    $className
 * @return   boolean
 */
function loadClass($className)
{
    // On cases sensitive filesystem all classes paths must be lowercased.
    $classPath = str_replace('\\', '/', strtolower($className));
    $classPath = LADOC_CLASSES_PATH . '/' . $className . '.php';
    return is_file($classPath) and require $classPath;
}

// Register {@function loadClass} as autoload callback.
spl_autoload_register('loadClass');

// Try to load and run the {@class LADoc front controller}.
try
{
    // Create instance.
    $ladoc = new LADoc();

    // Setup configuration.
    $ladoc->setup(['inputPath' => '.']);

    // Run build action.
    $ladoc->run('build');
}
catch (\LADoc\Error $e) {}

// Print output.
echo '<pre>' . $ladoc->getOutput() . '</pre>';
