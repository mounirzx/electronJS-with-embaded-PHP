<?php
/**
 * Get the Compatibility info for a chunk of code
 *
 * This example show the new options|features available with API 1.8.0
 * Especially the xml renderer
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsedata_toxml.php,v 1.2 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0RC2 (2008-07-18)
 * @ignore
 */
header('Content-type: text/xml');

require_once 'PHP/CompatInfo.php';

/*
   With API 1.8.0 you may choose a custom render,
   between all default renderers (all customizable).

   Here we choose to display result as XML stream,
   Even if PEAR::XML_Beautifier package is available (installed) we won't use it
 */
$driverType = 'xml';

/*
   Won't use PEAR::XML_Beautifier
   (reason bug #5450 http://pear.php.net/bugs/bug.php?id=5450, that strip XML declaration)
 */
$driverOptions = array('use-beautifier' => 'no');

$source  = array('C:\wamp\tmp\Log-1.10.0\Log', 'C:\wamp\tmp\File_Find-1.3.0\Find.php');
$options = array('debug' => true);

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);
$compatInfo->parseData($source, $options);
?>