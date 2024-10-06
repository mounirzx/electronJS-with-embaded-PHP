<?php
/**
 * xmlwriter extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: xmlwriter_func_exceptions.php,v 1.1 2008/12/29 13:48:44 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['xmlwriter'])) {
    $function_exceptions['xmlwriter'] = array();
}

$function_exceptions['xmlwriter'] = array_merge($function_exceptions['xmlwriter'], array(
  'xmlwriter_end_dtd_entity' =>
  array (
    'init' => '5.2.1',
    'ext' => 'xmlwriter',
    'pecl' => false,
  ),
  'xmlwriter_start_dtd_entity' =>
  array (
    'init' => '5.2.1',
    'ext' => 'xmlwriter',
    'pecl' => false,
  ),
  'xmlwriter_write_dtd_entity' =>
  array (
    'init' => '5.2.1',
    'ext' => 'xmlwriter',
    'pecl' => false,
  ))
);
?>