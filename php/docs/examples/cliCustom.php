<?php
/**
 * How to cutomize CLI output version. Requires at least version 1.3.1
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: cliCustom.php,v 1.5 2008/07/22 21:09:37 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 * @deprecated since version 1.8.0b2
 */
require_once 'PHP/CompatInfo/Cli.php';

// split filename to 30 char. max and continuation char. is +
$cli = new PHP_CompatInfo_Cli(30, '+');
$cli->run();
?>