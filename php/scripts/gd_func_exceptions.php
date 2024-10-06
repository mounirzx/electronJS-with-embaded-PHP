<?php
/**
 * Image processing (gd) extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: gd_func_exceptions.php,v 1.1 2008/12/28 19:51:26 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['gd'])) {
    $function_exceptions['gd'] = array();
}

$function_exceptions['gd'] = array_merge($function_exceptions['gd'], array(
  'gd_info' =>
  array (
    'init' => '4.3.0',
    'ext' => 'gd',
    'pecl' => false,
  ),
  'getimagesize' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gd',
    'pecl' => false,
  ),
  'iptcembed' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gd',
    'pecl' => false,
  ),
  'iptcparse' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gd',
    'pecl' => false,
  ),
  'jpeg2wbmp' =>
  array (
    'init' => '4.0.5',
    'ext' => 'gd',
    'pecl' => false,
  ),
  'png2wbmp' =>
  array (
    'init' => '4.0.5',
    'ext' => 'gd',
    'pecl' => false,
  ))
);
?>