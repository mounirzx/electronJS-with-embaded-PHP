<?php
/**
 * hash extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: hash_func_exceptions.php,v 1.1 2008/12/29 09:43:12 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['hash'])) {
    $function_exceptions['hash'] = array();
}

$function_exceptions['hash'] = array_merge($function_exceptions['hash'], array(
  'md5' =>
  array (
    'init' => '4.0.0',
    'ext' => 'hash',
    'pecl' => false,
  ),
  'md5_file' =>
  array (
    'init' => '4.2.0',
    'ext' => 'hash',
    'pecl' => false,
  ),
  'sha1' =>
  array (
    'init' => '4.3.0',
    'ext' => 'hash',
    'pecl' => false,
  ),
  'sha1_file' =>
  array (
    'init' => '4.3.0',
    'ext' => 'hash',
    'pecl' => false,
  ))
);
?>