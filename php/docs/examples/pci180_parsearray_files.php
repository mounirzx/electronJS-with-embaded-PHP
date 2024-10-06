<?php
/**
 * Get the Compatibility info for a list of different source (directory, file)
 * which may have no link between them.
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsearray_files.php,v 1.2 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0b3 (2008-06-07)
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

/*
   With all default options, its same as version 1.8.0b1 or less
   No need to specify driver type ('array') and options, in class constructor
   Results display made with PHP::var_export
*/
//$compatInfo = new PHP_CompatInfo();

/*
   With API 1.8.0 you may choose a custom render,
   between all default renderers (all customizable).
 */
$driverType = 'array';

/*
   Display wait messages or a progress bar (PEAR::Console_ProgressBar)
   if available while parsing data source
   Default behavior is:  silent = true (no wait system)
   Use (progress => text) if you don't want a progress bar but only text messages
 */
$driverOptions = array('silent' => false, 'progress' => 'bar');

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);

$source  = array('C:\wamp\tmp\Log-1.10.0\Log', 'C:\wamp\tmp\File_Find-1.3.0\Find.php');
$options = array();
$r = $compatInfo->parseArray($source, $options);
// You may also use the new unified method parseData(), parseArray() became an alias
//$r = $compatInfo->parseData($source, $options);

/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>