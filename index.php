<?php
/**
 * Initialize and start build process.
 *
 * @bootstrap LADoc
 */

// @const string ROOT_PATH Define absolute root path (force unix style).
define('ROOT_PATH', str_replace('\\', '/', __DIR__));

// @const string CLASSES_PATH Define absolute classes path.
define('CLASSES_PATH', ROOT_PATH . '/classes');

/**
 * Try to load a class based on his name.
 *
 * @func  loadClass
 * @event spl_autoload Called on class requested.
 * @param string $className
 */
function loadClass($className)
{
    // Set partial class path based on his name.
    $classPath = str_replace('\\', '/', strtolower($className));

    // Concact and try to load class file.
    require CLASSES_PATH . '/' . $classPath . '.php';
}

// Register autoload event callback.
spl_autoload_register('loadClass');

// Try to build documentation.
try
{
    // Create builder instance.
    $builder = new \LADoc\Builder();

    // Build output.
    $builder->build();
}

// If an error occurred.
catch (\LADoc\Error $e)
{
    // Get message text.
    $message = $e->getMessage();

    // Create HTML message.
    $output  = "<html lang=\"en\"><head><meta charset=\"utf-8\">";
    $output .= "<title>Error !</title></head><body>";
    $output .= "<h1>Oups !</h1><hr /><pre>$message</pre>";
    $output .= "</body></html>";

    // Print message.
    echo $output;
}
