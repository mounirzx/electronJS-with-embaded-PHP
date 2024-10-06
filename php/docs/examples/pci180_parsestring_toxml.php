<?php
/**
 * Get the Compatibility info for a chunk of code
 *
 * This example show the new options|features available with API 1.8.0
 * Especially, observer and xml renderer
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsestring_toxml.php,v 1.2 2008/07/22 20:26:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    version 1.8.0b4 (2008-06-18)
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

define('DEST_LOG_FILE', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'notify.log');

/*
   Add an observer to listen all PCI events
 */
function pci180_debug(&$auditEvent)
{
    $notifyName = $auditEvent->getNotificationName();
    $notifyInfo = $auditEvent->getNotificationInfo();
    error_log('notifyName:'. $notifyName . PHP_EOL, 3, DEST_LOG_FILE);
    error_log('notifyInfo:'. PHP_EOL .
               var_export($notifyInfo, true) . PHP_EOL, 3, DEST_LOG_FILE);
}

/*
   With API 1.8.0 you may choose a custom render,
   between all default renderers (all customizable).

   Here we choose to display result as XML stream,
   beautified if PEAR::XML_Beautifier package is available (installed).
 */
$driverType = 'xml';

// use some options of XML_Beautifier to change default render
$driverOptions = array('beautifier' => array('indent' => ' ',
                                             'linebreak' => PHP_EOL),
//                     output all informations except tokens (output-level = 23)
                       'args' => array('output-level' => 23));
$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);
$compatInfo->addListener('pci180_debug');

$str1 = '<?php
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

$str2 = '<?php
class Request6056
{
    function testMaxVersion()
    {
        // PHP 5 <= 5.0.4
        $res = php_check_syntax(\'bug6581.php\');

        $array1 = array(\'blue\'  => 1, \'red\'  => 2, \'green\'  => 3);
        $array2 = array(\'green\' => 5, \'blue\' => 6, \'yellow\' => 7);

        // PHP 5 >= 5.1.0RC1
        $diff = array_diff_key($array1, $array2);
    }
}
?>';

$source  = array($str1, $str2);
$options = array('is_string' => true, 'debug' => true);

//$r = $compatInfo->parseString($source, $options);
// You may also use the new unified method parseData(), parseString() became an alias
$r = $compatInfo->parseData($source, $options);

/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>