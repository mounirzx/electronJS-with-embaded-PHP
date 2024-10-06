<?php
/**
 * Get the Compatibility info for a single file
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsefile.php,v 1.3 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0b2 (2008-06-03)
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

   Here we choose to display result still as an array, but with PEAR::Var_Dump
   package if available (installed).
   On CLI we have only ability to use the "Text" Var_Dump renderer (display_mode)
 */
$driverType = 'array';

// use default "HTML4_Table" Var_Dump renderer
/* Be aware that if you run this script in CLI, the Var_Dump renderer used
   is "Text" (no choice) */
$driverOptions
    = array('PEAR::Var_Dump' =>
            array('options' => array('display_mode' => 'HTML4_Table')));

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);

$source = 'C:\php\pear\HTML_Progress2\Progress2.php';
$r = $compatInfo->parseFile($source);
// You may also use the new unified method parseData(), parseFile() became an alias
//$r = $compatInfo->parseData($source);

/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
?>