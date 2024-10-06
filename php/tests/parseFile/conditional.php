<?php
// PHP 4.0.0 : __FILE__
// PHP 4.0.6 : DIRECTORY_SEPARATOR
// PHP 4.0.7 : version compare
// PHP 4.3.0 : ob_get_clean
// PHP 4.3.0 : debug_backtrace
// PHP 4.3.10 and 5.0.2 : PHP_EOL
// PHP 5.0.0 : simplexml_load_file

if (!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR',
        strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/'
    );
}

if (function_exists('debug_backtrace')) {
    $backtrace = debug_backtrace();
} else {
    $backtrace = false;
}

if (function_exists('simplexml_load_file')) {
    $xml = simplexml_load_file('C:\php\pear\PHP_CompatInfo\scripts\version.xml');
}

if (version_compare(phpversion(), '5.0.0', '<')) {
    include_once 'PHP/Compat.php';
    PHP_Compat::loadFunction('ob_get_clean');
    PHP_Compat::loadConstant('PHP_EOL');
}

echo "Hello World" . PHP_EOL;

$ds  = DIRECTORY_SEPARATOR;
$fn  = dirname(__FILE__) . $ds . basename(__FILE__);
echo "You have run file : $fn at " . date(DATE_W3C) . PHP_EOL;

?>