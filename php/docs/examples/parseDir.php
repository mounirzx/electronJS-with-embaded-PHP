<?php
/**
 * Get the Compatibility info for an entire folder (recursive)
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: parseDir.php,v 1.7 2008/07/22 21:13:14 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

$info = new PHP_CompatInfo();

$folder  = dirname(__FILE__);
$options = array(
    'file_ext' => array('php3', 'php'),
    'ignore_files' => array(__FILE__)
    );

var_dump($options);
$r = $info->parseFolder($folder, $options);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>