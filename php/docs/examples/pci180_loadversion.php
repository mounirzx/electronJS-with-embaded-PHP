<?php
/**
 * Get PHP functions and constants history from a range of version,
 * group by version number
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_loadversion.php,v 1.3 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0RC2 (2008-07-18)
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

$compatInfo = new PHP_CompatInfo();

$r = $compatInfo->loadVersion('4.3.2', '4.4.0', true, true);
var_export($r);
?>