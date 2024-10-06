<?php
/**
 * Get the Compatibility info for an array
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: parseArray.php,v 1.7 2008/07/22 21:13:14 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

require_once 'PHP/CompatInfo.php';
require_once 'PEAR.php';

$info = new PHP_CompatInfo();

$files   = get_included_files();
$options = array(
    'debug' => false,
    'ignore_files' => array($files[0]),
    'ignore_functions' => array('debug_backtrace')
    );

$r = $info->parseArray($files, $options);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>