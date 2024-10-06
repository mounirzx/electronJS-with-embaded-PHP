<?php
/**
 * libxml extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: libxml_func_exceptions.php,v 1.1 2008/12/29 17:10:06 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['libxml'])) {
    $function_exceptions['libxml'] = array();
}

$function_exceptions['libxml'] = array_merge($function_exceptions['libxml'], array(
  'libxml_clear_errors' =>
  array (
    'init' => '5.1.0',
    'ext' => 'libxml',
    'pecl' => false,
  ),
  'libxml_get_errors' =>
  array (
    'init' => '5.1.0',
    'ext' => 'libxml',
    'pecl' => false,
  ),
  'libxml_get_last_error' =>
  array (
    'init' => '5.1.0',
    'ext' => 'libxml',
    'pecl' => false,
  ),
  'libxml_set_streams_context' =>
  array (
    'init' => '5.0.0',
    'ext' => 'libxml',
    'pecl' => false,
  ),
  'libxml_use_internal_errors' =>
  array (
    'init' => '5.1.0',
    'ext' => 'libxml',
    'pecl' => false,
  ))
);
?>