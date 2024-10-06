<?php
/**
 * Show a progress bar when parsing a directory from CLI
 * Do not display any progress bar from other SAPI
 *
 * To run on Windows platform, do:
 * (path to PHP cli)\php.exe -f (this script)
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: parseDir_withCustomProgressBar.php,v 1.4 2008/07/24 21:18:08 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */
require_once 'PHP/CompatInfo.php';

if (php_sapi_name() == 'cli') {
    /*
      Display a progress bar like this one:

     [)))))                                          ]    8% -  45/563 files

     */
    $pbar = array('formatString' => '[%bar%] %percent% - %fraction% files',
                  'barfill' => ')',
                  'prefill' => ' ',
                  'options' => array('percent_precision' => 0));

    $driverType    = 'text';
    $driverOptions = array('silent' => false, 'progress' => 'bar',
                                              'progressbar' => $pbar);

} else {
    $driverType    = 'array';
    $driverOptions = array();
}

$info = new PHP_CompatInfo($driverType, $driverOptions);
$dir  = 'C:\php\pear\HTML_Progress2';
$r    = $info->parseDir($dir);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>