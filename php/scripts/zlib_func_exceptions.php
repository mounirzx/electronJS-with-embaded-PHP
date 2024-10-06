<?php
/**
 * zlib extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: zlib_func_exceptions.php,v 1.1 2008/12/29 14:11:55 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['zlib'])) {
    $function_exceptions['zlib'] = array();
}

$function_exceptions['zlib'] = array_merge($function_exceptions['zlib'], array(
  'ob_gzhandler' =>
  array (
    'init' => '4.0.4',
    'ext' => 'zlib',
    'pecl' => false,
  ),
  'readgzfile' =>
  array (
    'init' => '4.0.0',
    'ext' => 'zlib',
    'pecl' => false,
  ))
);
?>