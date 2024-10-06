<?php
/**
 * Standard PHP Library (SPL) extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: spl_func_exceptions.php,v 1.2 2008/12/29 13:14:12 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['SPL'])) {
    $function_exceptions['SPL'] = array();
}

$function_exceptions['SPL'] = array_merge($function_exceptions['SPL'], array(
  'class_implements' =>
  array (
    'init' => '5.0.0',
    'ext' => 'SPL',
    'pecl' => false,
  ),
  'class_parents' =>
  array (
    'init' => '5.0.0',
    'ext' => 'SPL',
    'pecl' => false,
  ),
  'iterator_apply' =>
  array (
    'init' => '5.2.0',
    'ext' => 'SPL',
    'pecl' => false,
  ),
  'iterator_count' =>
  array (
    'init' => '5.1.3',
    'ext' => 'SPL',
    'pecl' => false,
  ),
  'iterator_to_array' =>
  array (
    'init' => '5.1.3',
    'ext' => 'SPL',
    'pecl' => false,
  ))
);
?>