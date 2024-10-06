<?php
/**
 * date extension Function Exceptions dictionary for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: date_func_exceptions.php,v 1.1 2008/12/28 19:48:59 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['date'])) {
    $function_exceptions['date'] = array();
}

$function_exceptions['date'] = array_merge($function_exceptions['date'], array(
  'checkdate' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'getdate' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'gmdate' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'gmmktime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'gmstrftime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'idate' =>
  array (
    'init' => '5.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'localtime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'mktime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'strftime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'strtotime' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'time' =>
  array (
    'init' => '4.0.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_abbreviations_list' =>
  array (
    'init' => '5.1.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_identifiers_list' =>
  array (
    'init' => '5.1.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_name_from_abbr' =>
  array (
    'init' => '5.1.3',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_name_get' =>
  array (
    'init' => '5.1.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_offset_get' =>
  array (
    'init' => '5.1.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_open' =>
  array (
    'init' => '5.1.0',
    'ext' => 'date',
    'pecl' => false,
  ),
  'timezone_transitions_get' =>
  array (
    'init' => '5.2.0',
    'ext' => 'date',
    'pecl' => false,
  ))
);
?>