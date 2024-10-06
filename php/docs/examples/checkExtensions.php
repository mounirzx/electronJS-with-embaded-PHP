<?php
/**
 * Test Extensions that appeared both as standard or PECL
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: checkExtensions.php,v 1.3 2008/07/22 21:13:14 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

xdebug_start_trace();

require_once 'PHP/CompatInfo.php';

/**
 * @ignore
 */
function test_extensions()
{
    $image = imagecreate(320, 240);
    imageantialias($image, true);
    return $image;
}

/*
  Cannot be parsed on CLI
  print_r(apache_get_modules());
*/

if (!extension_loaded('sqlite')) {
    $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
    dl($prefix . 'sqlite.' . PHP_SHLIB_SUFFIX);
}

xdebug_stop_trace();

$info = new PHP_CompatInfo();

$file    = __FILE__;
$options = array('debug' => true);

$r = $info->parseFile($file, $options);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>