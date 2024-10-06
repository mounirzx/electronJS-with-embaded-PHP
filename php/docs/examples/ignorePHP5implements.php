<?php
/**
 * Exclude all PHP5 functions when calculating the version needed.
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: ignorePHP5implements.php,v 1.5 2008/07/22 21:13:14 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

$info = new PHP_CompatInfo();

$dir = 'C:\PEAR\Tools and utilities\PhpDocumentor-1.3.0';
$options = array(
    'debug' => true,
    'ignore_functions' => PHP_CompatInfo::loadVersion('5.0.0'),
    'ignore_constants' => array('clone', 'public')
    );

$r = $info->parseFolder($dir, $options);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>