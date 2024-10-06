<?php
/**
 * date extension Class Exceptions dictionary for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: date_class_exceptions.php,v 1.1 2008/12/28 19:48:59 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 */

$class_exceptions['date'] = array (
  'DateTime' =>
  array (
    'init' => '5.2.0',
    'name' => 'DateTime',
    'ext' => 'date',
    'pecl' => false,
  ),
  'DateTimeZone' =>
  array (
    'init' => '5.2.0',
    'name' => 'DateTimeZone',
    'ext' => 'date',
    'pecl' => false,
  ),
);
?>