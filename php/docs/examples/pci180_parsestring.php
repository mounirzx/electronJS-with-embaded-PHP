<?php
/**
 * Get the Compatibility info for a chunk of code
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsestring.php,v 1.3 2008/07/22 20:26:45 farell Exp $
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

   Here we choose to display result as XML stream, beautified if PEAR::XML_Beautifier
   package is available (installed).
 */
$driverType = 'xml';

// use some options of XML_Beautifier to change default render
$driverOptions = array('beautifier' => array('indent' => ' ', 'linebreak' => PHP_EOL));

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);

$source = '<?php
$nl = "\n";
echo "$nl Atom    = " . DATE_ATOM;
echo "$nl Cookie  = " . DATE_COOKIE;
echo "$nl Iso8601 = " . DATE_ISO8601;
echo "$nl Rfc822  = " . DATE_RFC822;
echo "$nl Rfc850  = " . DATE_RFC850;
echo "$nl Rfc1036 = " . DATE_RFC1036;
echo "$nl Rfc1123 = " . DATE_RFC1123;
echo "$nl Rfc2822 = " . DATE_RFC2822;
echo "$nl RSS     = " . DATE_RSS;
echo "$nl W3C     = " . DATE_W3C;
?>';

$r = $compatInfo->parseString($source);
// You may also use the new unified method parseData(), parseString() became an alias
//$r = $compatInfo->parseData($source);

/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
var_export($r);
?>