<?php
/**
 * Request #6056 Add support for reporting max PHP version
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: checkMax.php,v 1.5 2008/07/22 20:27:11 farell Exp $
 * @link     http://pear.php.net/bugs/bug.php?id=6056
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

$info = new PHP_CompatInfo();

$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sample_req6056.php';
$arr  = array($file, __FILE__);

$options = array('debug' => true);

$res = $info->parseArray($arr, $options);

echo 'PHP min = ' . $res['version'] . "<br />\n";
if ($res['max_version'] != '') {
    echo 'PHP max = ' . $res['max_version'] . "<br />\n";

    foreach ($arr as $file) {
        $min = $res[$file]['version'];
        $max = $res[$file]['max_version'];
        if (($max != '') && (version_compare($min, $max) === 1)) {
            echo 'ATTENTION file "'.$file.
                '" cannot run : PHP version Min > Max '."\n";
        }
    }
}
if (count($res['extensions'])) {
    echo 'Extensions required : ' . implode(', ', $res['extensions']) . "<br />\n";
}

echo '<pre>';
var_dump($res);
echo '</pre>';

?>