<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * getValues Test case for Console_Getargs
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Console
 * @package    Console_Getargs
 * @subpackage Tests
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Getargs_getValues_testcase.php 269139 2008-11-17 10:02:30Z clockwerx $
 * @link       http://pear.php.net/package/Console_Getargs
 */
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Getargs_getValues_testCase::main');
}

require_once 'Console/Getargs.php';
require_once 'PHPUnit/Framework.php';

/**
 * getValues Test case for Console_Getargs
 *
 * @category   Console
 * @package    Console_Getargs
 * @subpackage Tests
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Console_Getargs
 */
class Getargs_getValues_testCase extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Getargs_getValues_testCase');
        PHPUnit_TextUI_TestRunner::run($suite);
    }



   /**
    * Test getValues('long') with one argument
    *
    * @access  public
    * @return  void
    */
    function testLong1()
    {
        $config = array(
                    'name' => array(
                        'short' => 'n',
                        'min'   => 1,
                        'max'   => 1,
                        'desc'  => 'An argument.')
                       );

        $args = array('-n', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => 'arg1'), $obj->getValues('long'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => 'arg1'), $obj->getValues('long'), "'$message' Incorrect value returned");
        }
    }

   /**
    * Test getValues('long') with one argument and two values
    *
    * @access  public
    * @return  void
    */
    function testLong2()
    {
        $config = array(
                    'name' => array(
                        'short' => 'n',
                        'min'   => 2,
                        'max'   => 2,
                        'desc'  => 'Two arguments.')
                       );

        $args = array('-n', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => array('arg1', 'arg2')), $obj->getValues('long'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => array('arg1', 'arg2')), $obj->getValues('long'), "'$message' Incorrect value returned");
        }
    }

   /**
    * Test getValues('short') with one argument
    *
    * @access  public
    * @return  void
    */
    function testShort()
    {
        $config = array(
                    'name' => array(
                        'short' => 'n',
                        'min'   => 1,
                        'max'   => 1,
                        'desc'  => 'An argument.')
                       );

        $args = array('-n', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('n' => 'arg1'), $obj->getValues('short'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('n' => 'arg1'), $obj->getValues('short'), "'$message' Incorrect value returned");
        }
    }

   /**
    * Test getValues('short') with one argument and two values
    *
    * @access  public
    * @return  void
    */
    function testShort2()
    {
        $config = array(
                    'name' => array(
                        'short' => 'n',
                        'min'   => 2,
                        'max'   => 2,
                        'desc'  => 'Two arguments.')
                       );

        $args = array('-n', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('n' => array('arg1', 'arg2')), $obj->getValues('short'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('n' => array('arg1', 'arg2')), $obj->getValues('short'), "'$message' Incorrect value returned");
        }
    }

   /**
    * Test getValues('short') and getValues('long') with a switch
    *
    * @access  public
    * @return  void
    */
    function testSwitch()
    {
        $config = array(
                    'switch' => array(
                        'short' => 's',
                        'max'   => 0,
                        'desc'  => 'A switch.')
                        );

        $args = array('-s');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertTrue($obj->isDefined('s'), "'$message' Switch not defined");
            $this->assertTrue($obj->isDefined('switch'), "'$message' Switch not defined");
        }

        $args = array('--switch');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        
        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('switch' => true), $obj->getValues('long'), "'$message' Switch not defined");
            $this->assertEquals(array('s' => true), $obj->getValues('short'), "'$message' Switch not defined");
        }

    }

   /**
    * Test getValues('long') and getValues('short') with two arguments
    *
    * @access  public
    * @return  void
    */
    function testMultiple()
    {
        $config = array(
                    'name' => array(
                        'short' => 'n',
                        'min'   => 1,
                        'max'   => 1,
                        'desc'  => 'One argument.'),
                    'email' => array(
                        'short' => 'e',
                        'min'   => 2,
                        'max'   => 2,
                        'desc'  => 'Two arguments.'),
                       );

        $args = array('-n', 'arg1', '-e', 'schst@php.net', 'wenz@php.net');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => $args[1], 'email' => array($args[3], $args[4])), $obj->getValues('long'), "'$message' Incorrect value returned");
            $this->assertEquals(array('n' => $args[1], 'e' => array($args[3], $args[4])), $obj->getValues('short'), "'$message' Incorrect value returned");
        }
        
        $args = array('--name', 'arg1', '--email', 'schst@php.net', 'wenz@php.net');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' " . $obj->getMessage());
        } else {
            $this->assertEquals(array('name' => $args[1], 'email' => array($args[3], $args[4])), $obj->getValues('long'), "'$message' Incorrect value returned");
            $this->assertEquals(array('n' => $args[1], 'e' => array($args[3], $args[4])), $obj->getValues('short'), "'$message' Incorrect value returned");
        }
    }
}

if (PHPUnit_MAIN_METHOD == 'Getargs_getValues_testCase::main') {
    Getargs_getValues_testCase::main();
}

?>