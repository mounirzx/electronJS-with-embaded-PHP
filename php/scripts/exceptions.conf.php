<?php
/**
 * Exceptions definition for Versions, Functions, Classes and Constants
 *
 * PHP versions 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: exceptions.conf.php,v 1.21 2009/01/03 10:19:22 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.9.0b1
 */

/* default version for each extension
   if not defined, then suppose its 4.0.0 */
$version_exceptions = array('bz2' => '4.0.4',
                            'com_dotnet' => '5.0.0',
                            'curl' => '4.0.2',
                            'dom' => '5.0.0',
                            'exif' => '4.2.0',
                            'fileinfo' => '5.3.0',
                            'filter' => '5.2.0',
                            'gmp' => '4.0.4',
                            'json' => '5.2.0',
                            'libxml' => '5.1.0',
                            'openssl' => '4.0.4',
                            'pcre' => '4.0.0',
                            'PDO' => '5.1.0',
                            'phar' => '5.3.0',
                            'pspell' => '4.0.2',
                            'Reflection' => '5.0.0',
                            'shmop' => '4.0.3',
                            'sockets' => '4.0.2',
                            'SimpleXML' => '5.0.0',
                            'SPL' => '5.0.0',
                            'standard' => '4.0.0',
                            'xsl' => '5.0.0',
                            'xmlreader' => '5.1.0',
                            'xmlwriter' => '5.1.2',
                            );
/* if default version is not 4.0.0, then we can fix the right
   constant initial version here */
require_once 'constant_exceptions.php';
require_once 'calendar_const_exceptions.php';
require_once 'date_const_exceptions.php';
require_once 'ftp_const_exceptions.php';
require_once 'gd_const_exceptions.php';
require_once 'gmp_const_exceptions.php';
require_once 'iconv_const_exceptions.php';
require_once 'mysql_const_exceptions.php';
require_once 'mysqli_const_exceptions.php';
require_once 'openssl_const_exceptions.php';
require_once 'pcre_const_exceptions.php';
require_once 'standard_const_exceptions.php';
require_once 'xsl_const_exceptions.php';

/* if default version is not 4.0.0, then we can fix the right
   predefined class initial version here */
require_once 'class_exceptions.php';
require_once 'date_class_exceptions.php';
require_once 'standard_class_exceptions.php';

/* if default is not from PHP core version 4.0.0, then we can fix the right
   function data here */
require_once 'function_exceptions.php';
require_once 'calendar_func_exceptions.php';
require_once 'date_func_exceptions.php';
require_once 'gd_func_exceptions.php';
require_once 'gettext_func_exceptions.php';
require_once 'hash_func_exceptions.php';
require_once 'iconv_func_exceptions.php';
require_once 'libxml_func_exceptions.php';
require_once 'spl_func_exceptions.php';
require_once 'standard_func_exceptions.php';
require_once 'xmlwriter_func_exceptions.php';
require_once 'zlib_func_exceptions.php';

/**
 * Function that provides to return exceptions results
 *
 * @param string $extension Extension name
 * @param sting  $type      Type of exception (version | class | constant)
 *
 * @return mixed Return false if no exception exists for this $extension and $type
 */
function getExceptions($extension, $type)
{
    global $version_exceptions, $class_exceptions, $function_exceptions, $constant_exceptions;

    $exceptions = false;

    switch ($type) {
    case 'version' :
        if (isset($version_exceptions[$extension])) {
            $exceptions = $version_exceptions[$extension];
        }
        break;
    case 'class' :
        if (isset($class_exceptions[$extension])) {
            $exceptions = $class_exceptions[$extension];
        }
        break;
    case 'function' :
        if (isset($function_exceptions[$extension])) {
            $exceptions = $function_exceptions[$extension];
        }
        break;
    case 'constant' :
        if (isset($constant_exceptions[$extension])) {
            $exceptions = $constant_exceptions[$extension];
        }
    }

    return $exceptions;
}
?>