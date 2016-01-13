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

// Define root path (force unix style)
define('ROOT_PATH', str_replace('\\', '/', __DIR__));

// Define classes path
define('CLASSES_PATH', ROOT_PATH . '/classes');

// Auto load classes
spl_autoload_register(function($class_name)
{
    // Normalize file path
    $filename = str_replace('\\', '/', strtolower($class_name));

    // Try to include de class file
    require CLASSES_PATH . '/' . $filename . '.php';
});

try
{
    // Start buffer
    ob_start();

    // Create builder instance
    $builder = new Builder();

    // Build the output
    $builder->build();
}
catch (\Exception $e)
{
    // Clean buffer
    ob_clean();

    // Error messages
    $message = $e->getMessage();
    $file    = $e->getFile();
    $line    = $e->getLine();

    // Override output
    $output  = "<html lang=\"en\"><head><meta charset=\"utf-8\">";
    $output .= "<title>Error !</title></head><body>";
    $output .= "<h1>Error !</h1><hr /><pre>";
    $output .= "<b>Message :</b> $message\n";
    $output .= "<b>File    :</b> $file\n";
    $output .= "<b>Line    :</b> $line\n";
    $output .= "</pre></body></html>";

    // Print output
    echo $output;
}
