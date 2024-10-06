<?php
/**
 * gettext extension Function Exceptions dictionary
 * for PHP_CompatInfo 1.9.0b2 or better
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: gettext_func_exceptions.php,v 1.1 2008/12/29 17:47:08 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.9.0 (2009-01-19)
 */

if (!isset($function_exceptions['gettext'])) {
    $function_exceptions['gettext'] = array();
}

$function_exceptions['gettext'] = array_merge($function_exceptions['gettext'], array(
  '_' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'bind_textdomain_codeset' =>
  array (
    'init' => '4.2.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'bindtextdomain' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'dcgettext' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'dcngettext' =>
  array (
    'init' => '4.2.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'dgettext' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'dngettext' =>
  array (
    'init' => '4.2.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'gettext' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'ngettext' =>
  array (
    'init' => '4.2.0',
    'ext' => 'gettext',
    'pecl' => false,
  ),
  'textdomain' =>
  array (
    'init' => '4.0.0',
    'ext' => 'gettext',
    'pecl' => false,
  ))
);
?>