<?php
/**
 * Get the Compatibility info for a list of chunk of code (strings)
 *
 * This example show the new options|features available with API 1.8.0
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: pci180_parsearray_strings.php,v 1.2 2008/07/22 20:26:45 farell Exp $
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
 */
$driverOptions = array('silent' => false, 'progress' => 'text');

$compatInfo = new PHP_CompatInfo($driverType, $driverOptions);

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
// CAUTION: if you forget this option, you will have no output except a FALSE result (see $r)
$options = array('is_string' => true);

$r = $compatInfo->parseArray($source, $options);
// You may also use the new unified method parseData(), parseArray() became an alias
//$r = $compatInfo->parseData($source, $options);

/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>