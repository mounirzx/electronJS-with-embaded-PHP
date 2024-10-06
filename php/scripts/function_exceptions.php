<?php
/**
 * Predefined Functions official version known for PHP_CompatInfo 1.9.0a1 or better
 *
 * Sources came from :
 * Revision  Link
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/apache/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/apc/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/apd/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/bc/versions.xml
 * 1.2       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/bzip2/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/com/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/ctype/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/datetime/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/dom/versions.xml
 * 1.2       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/filter/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/ftp/versions.xml
 * 1.2       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/gmp/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/hash/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/iconv/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/image/versions.xml
 * 1.3       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/json/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/mbstring/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/mysql/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/mysqli/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/openssl/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/pcre/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/pdo/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/posix/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/session/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/simplexml/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/spl/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/ssh2/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/tidy/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/wddx/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/xml/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/xmlreader/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/xmlwriter/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/xslt/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/zip/versions.xml
 * 1.1       http://cvs.php.net/viewvc.cgi/phpdoc/en/reference/zlib/versions.xml
 *
 * PHP versions 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Davey Shafik <davey@php.net>
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: function_exceptions.php,v 1.5 2008/12/29 10:27:42 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 */

$function_exceptions = array();

$ds = DIRECTORY_SEPARATOR;

$version_pattern = '\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?';
$ext_pattern     = '(.*)\\'.$ds.'phpdocref\\'.$ds.'(.*)\\'.$ds.'versions.xml';

$data_dir = '@data_dir@'.$ds.'@package_name@'.$ds.'data'.$ds.'phpdocref'.$ds;

$aliases = array('bc' => 'bcmath',
                 'bzip2' => 'bz2',
                 'datetime' => 'date',
                 'image' => 'gd',
                 'pdo' => 'PDO',
                 'simplexml' => 'SimpleXML',
                 'spl' => 'SPL'
                 );

// Iterating over extension specific version files
foreach(glob($data_dir.'*'.$ds.'versions.xml') as $file) {

    preg_match('/'.$ext_pattern.'/', $file, $matches);
    $ext = $matches[2];
    // real extension name, is case sensitive
    if (isset($aliases[$ext])) {
        $ext = $aliases[$ext];
    }

    $xml = simplexml_load_file($file);

    foreach ($xml->function as $function) {
        $name = (string) $function['name'];
        if (strpos($name, '::') !== false) {
            // Do not keep extension methods class
            continue;
        }
        $from = (string) $function['from'];

        /**
         * Match strings like :
         *  "PHP 3 &gt;= 3.0.7, PHP 4, PHP 5"          for _
         *  "PHP 5 &gt;= 5.1.0RC1"                     for date_modify
         *  "PHP 3 &gt;= 3.0.7, PHP 4 &lt;= 4.2.3"     for aspell_check
         *  "PHP 5 &gt;= 5.2.0, PECL zip:1.1.0-1.9.0"  for addfile
         */
        if (preg_match('/>= ('.$version_pattern.')/', $from, $matches)) {
            $init = $matches[1];
            if (preg_match('/<= ('.$version_pattern.')/', $from, $matches)) {
                $end = $matches[1];
            } else {
                $end = false;
            }
            if (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')-('.
                $version_pattern.')/', $from, $matches)) {

                $function_exceptions[$ext][$name]['init'] = $matches[3];
                $function_exceptions[$ext][$name]['end']  = $matches[4];
                $function_exceptions[$ext][$name]['ext']  = $ext;
                $function_exceptions[$ext][$name]['pecl'] = true;

            } elseif (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')/',
                $from, $matches)) {

                $function_exceptions[$ext][$name]['init'] = $matches[3];
                $function_exceptions[$ext][$name]['end']  = $matches[3];
                $function_exceptions[$ext][$name]['ext']  = $ext;
                $function_exceptions[$ext][$name]['pecl'] = true;
            } else {
                $function_exceptions[$ext][$name]['init'] = $init;
                if ($end !== false) {
                    $function_exceptions[$ext][$name]['end'] = $end;
                }
                $function_exceptions[$ext][$name]['ext']  = $ext;
                $function_exceptions[$ext][$name]['pecl'] = false;
            }
            continue;

            /**
             * Match string like :
             *  "PHP 5 &lt;= 5.0.4"                       for php_check_syntax
             *  "PHP 4 &lt;= 4.2.3, PECL cybercash:1.18"  for cybercash_base64_decode
             */
        } elseif (preg_match('/<= ('.$version_pattern.')/', $from, $matches)) {
            $end = $matches[1];

            if (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')-('.
                $version_pattern.')/', $from, $matches)) {

                $function_exceptions[$ext][$name]['init'] = $matches[3];
                $function_exceptions[$ext][$name]['end']  = $matches[4];
                $function_exceptions[$ext][$name]['ext']  = $ext;
                $function_exceptions[$ext][$name]['pecl'] = true;
                continue;

            } elseif (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')/',
                $from, $matches)) {

                $function_exceptions[$ext][$name]['init'] = $matches[3];
                $function_exceptions[$ext][$name]['end']  = $matches[3];
                $function_exceptions[$ext][$name]['ext']  = $ext;
                $function_exceptions[$ext][$name]['pecl'] = true;
                continue;
            }
            $function_exceptions[$ext][$name]['end']  = $end;
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;

            /**
             * Match string like :
             *  "4.0.2 - 4.0.6 only"    for accept_connect
             */
        } elseif (preg_match('/('.$version_pattern.') - ('.$version_pattern.') only/',
            $from, $matches)) {

            $function_exceptions[$ext][$name]['init'] = $matches[1];
            $function_exceptions[$ext][$name]['end']  = $matches[2];
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;
            continue;

            /**
             * Match string like :
             *  "PHP 3.0.5 only"    for PHP3_UODBC_FIELD_NUM
             *  "PHP 4.0.6 only"    for ocifreecoll
             */
        } elseif (preg_match('/PHP (\d)('.$version_pattern.') only/',
            $from, $matches)) {

            $function_exceptions[$ext][$name]['init'] = $matches[1] .'.0.0';
            $function_exceptions[$ext][$name]['end']  = $matches[2];
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;
            continue;

            /**
             * Match string like :
             *  "PHP 5, PECL oci8:1.1-1.2.4"   for oci_error
             */
        } elseif (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')-('.
            $version_pattern.')/', $from, $matches)) {

            $function_exceptions[$ext][$name]['init'] = $matches[3];
            $function_exceptions[$ext][$name]['end']  = $matches[4];
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = true;
            continue;

            /**
             * Match string like :
             *  "PHP 4, PHP 5, PECL mysql:1.0"  for mysql_connect
             */
        } elseif (preg_match('/(\.*)PECL ([a-zA-Z_]+):('.$version_pattern.')/',
            $from, $matches)) {

            $function_exceptions[$ext][$name]['init'] = $matches[3];
            $function_exceptions[$ext][$name]['end']  = $matches[3];
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = true;
            continue;
        }

        if (strpos($function['from'], 'PHP 3') !== false) {
            $function_exceptions[$ext][$name]['init'] = '3.0.0';
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;
            continue;
        }
        if (strpos($function['from'], 'PHP 4') !== false) {
            $function_exceptions[$ext][$name]['init'] = '4.0.0';
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;
            continue;
        }
        if (strpos($function['from'], 'PHP 5') !== false) {
            $function_exceptions[$ext][$name]['init'] = '5.0.0';
            $function_exceptions[$ext][$name]['ext']  = $ext;
            $function_exceptions[$ext][$name]['pecl'] = false;
            continue;
        }
    }
} // end-iterating over extension specific version files
?>