<?php
/**
 * Initialize and start the build process.
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
    // Set the partial class path based on his name.
    $classPath = str_replace('\\', '/', strtolower($className));

    // Concact and try to load the class file.
    require CLASSES_PATH . '/' . $classPath . '.php';
}

// Register autoload event callback.
spl_autoload_register('loadClass');

// Try to build the doc.
try
{
    // Create builder instance.
    $builder = new \LADoc\Builder();

    // Build the output.
    $builder->build();
}

// Catch builder error.
catch (\Error $e)
{
    // Get error message, file and line number.
    $message = $e->getMessage();
    $file    = $e->getFile();
    $line    = $e->getLine();

    // Create HTML error message.
    $output  = "<html lang=\"en\"><head><meta charset=\"utf-8\">";
    $output .= "<title>Error !</title></head><body>";
    $output .= "<h1>Error !</h1><hr /><pre>";
    $output .= "<b>Message :</b> $message\n";
    $output .= "</pre></body></html>";

    // Print HTML error message.
    echo $output;
}
