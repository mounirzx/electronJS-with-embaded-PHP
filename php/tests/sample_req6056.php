<?php
/**
 * Request #6056 Add support for reporting max PHP version. Sample code.
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version  CVS: $Id: sample_req6056.php,v 1.4 2007/11/15 18:23:40 farell Exp $
 * @link     http://pear.php.net/bugs/bug.php?id=6056
 * @ignore
 */

class Request6056
{
    /**
     * Sample code to check max php version
     *
     * @return void
     */
    function testMaxVersion()
    {
        // PHP 5 <= 5.0.4
        $res = php_check_syntax('bug6581.php');

        $array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3);
        $array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7);

        // PHP 5 >= 5.1.0RC1
        $diff = array_diff_key($array1, $array2);
    }
}

?>