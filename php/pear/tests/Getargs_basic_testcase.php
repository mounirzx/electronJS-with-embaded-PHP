<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The PHP Group                                     |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Bertrand Mansion <bmansion@mamasam.com>                      |
// +----------------------------------------------------------------------+
//
// $Id: Getargs_basic_testcase.php 304307 2010-10-11 11:28:02Z jespino $
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Getargs_Basic_testCase::main');
}

require_once 'Console/Getargs.php';
require_once 'PHPUnit/Framework.php';

/**
 * Unit tests for Console_Getargs package.
 */

class Getargs_Basic_testCase extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Getargs_Basic_testCase');
        PHPUnit_TextUI_TestRunner::run($suite);
    }



    function testFixed1()
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
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals($args[1], $obj->getValue('n'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals($args[1], $obj->getValue('name'), "'$message' Incorrect value returned");
        }
    }

    function testFixed2()
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
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(array($args[1], $args[2]), $obj->getValue('n'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(array($args[1], $args[2]), $obj->getValue('name'), "'$message' Incorrect value returned");
        }

    }

    function testFixed1WithDefault()
    {
        $config = array(
                    'name' => array(
                       'min'     => 1,
                       'max'     => 1,
                       'desc'    => 'Fixed number with default value if not used.',
                       'default' => 'default1')
                       );

        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals($args[1], $obj->getValue('name'), "'$message' Incorrect value returned");
        }

        $args = array();

        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail($obj->getMessage());
        } else {
            $this->assertEquals('default1', $obj->getValue('name'), "Incorrect value returned");
        }

    }

    function testFixed2WithDefault()
    {
        $config = array(
                    'names' => array(
                       'min'     => 2,
                       'max'     => 2,
                       'desc'    => 'Fixed number with default values if not used.',
                       'default' => array('default1', 'default2'))
                       );

        $args = array('--names', 'arg1', 'arg2');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(array($args[1], $args[2]), $obj->getValue('names'), "'$message' Incorrect value returned");
        }

        $args = array();

        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail($obj->getMessage());
        } else {
            $this->assertEquals(array('default1', 'default2'), $obj->getValue('names'), "Incorrect value returned");
        }

    }

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
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertTrue($obj->isDefined('s'), "'$message' Switch not defined");
            $this->assertTrue($obj->isDefined('switch'), "'$message' Switch not defined");
        }

        $args = array('--switch');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertTrue($obj->isDefined('s'), "'$message' Switch not defined");
            $this->assertTrue($obj->isDefined('switch'), "'$message' Switch not defined");
        }

    }

    function testSwitchWithDefaultIfValueNotSet()
    {
        $config = array(
                    'default' => array(
                        'short'   => 'd',
                        'min'     => 0,
                        'max'     => 1,
                        'desc'    => 'A switch with a default value if no value set.',
                        'default' => 3)
                        );

        $args = array('-d');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(3, $obj->getValue('d'), "'$message' Incorrect value returned");
            $this->assertEquals(3, $obj->getValue('default'), "'$message' Incorrect value returned");
        }

        $args = array('-d', 4);
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(4, $obj->getValue('d'), "'$message' Incorrect value returned");
            $this->assertEquals(4, $obj->getValue('default'), "'$message' Incorrect value returned");
        }
    }

    function testVariable1_3()
    {
        $config = array(
                    'name' => array(
                        'min' => 1,
                        'max' => 3,
                        'desc' => 'One to three values.')
                        );
        
        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals('arg1', $obj->getValue('name'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2', 'arg3');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(array('arg1', 'arg2', 'arg3'), $obj->getValue('name'), "'$message' Incorrect values returned");
        }

    }

    function testVariable1_3WithDefault()
    {
        // TODO : using min => 0 should maybe work too
        $config = array(
                    'name' => array(
                        'min'     => 1,
                        'max'     => 3,
                        'desc'    => 'Zero to three values using default if no values set.',
                        'default' => 'default1')
                        );
        
        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals('arg1', $obj->getValue('name'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2', 'arg3');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals(array('arg1', 'arg2', 'arg3'), $obj->getValue('name'), "'$message' Incorrect values returned");
        }

        $args = array();

        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail($obj->getMessage());
        } else {
            $this->assertEquals('default1', $obj->getValue('name'), "'$message' Incorrect values returned");
        }

    }

    function testNoLimit()
    {
        $config = array(
                    'name' => array(
                        'min'     => 1,
                        'max'     => -1,
                        'desc'    => 'Unlimited number of values.')
                        );
        
        $args = array('--name', 'arg1');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail("'$message' ".$obj->getMessage());
        } else {
            $this->assertEquals('arg1', $obj->getValue('name'), "'$message' Incorrect value returned");
        }

        $args = array('--name', 'arg1', 'arg2', 'arg3');
        $message = implode(' ', $args);
        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail($obj->getMessage());
        } else {
            $this->assertEquals(array('arg1', 'arg2', 'arg3'), $obj->getValue('name'), "'$message' Incorrect values returned");
        }
    }

    function testNoLimitWithDefault()
    {
        $config = array(
                    'name' => array(
                        'min'     => 1,
                        'max'     => -1,
                        'desc'    => 'Unlimited number of values.',
                        'default' => 'default1')
                        );

        $args = array();

        $obj =& Console_Getargs::factory($config, $args);

        if (PEAR::isError($obj)) {
            $this->fail($obj->getMessage());
        } else {
            $this->assertEquals('default1', $obj->getValue('name'), "Incorrect values returned");
        }
    }

    function testAlias()
    {
        $config = array(
                    'name|alias' => array(
                        'short'   => 'n|a',
                        'min'     => 0,
                        'max'     => 0,
                        'desc'    => 'Unlimited number of values with alias.')
                        );

        $args = array('-n', '-a', '--name', '--alias');
        foreach ($args as $arg) {
            $message = $arg;
            $obj =& Console_Getargs::factory($config, array($arg));
    
            if (PEAR::isError($obj)) {
                $this->fail($obj->getMessage());
            } else {
                $this->assertTrue($obj->isDefined('name'), "Value 'name' is not defined");
                $this->assertTrue($obj->isDefined('alias'), "Value 'alias' is not defined");
                $this->assertTrue($obj->isDefined('n'), "Value 'n' is not defined");
                $this->assertTrue($obj->isDefined('a'), "Value 'a' is not defined");
            }
        }
    }



    /**
    * isDefined should return true when the parameter is passed on cmdline
    */
    function testIsDefined()
    {
        $config = array(
            'name' => array(
                'short'   => 'n',
                'min'     => 1,
                'max'     => 1,
                'desc'    => 'An argument.',
                'default' => 'john'
            )
        );

        $args = array('-n', 'arg1');
        $obj  =& Console_Getargs::factory($config, $args);

        $this->assertFalse(PEAR::isError($obj));
        $this->assertTrue($obj->isDefined('n'));
        $this->assertTrue($obj->isDefined('name'));
    }



    /**
    * isDefined should return true when the parameter is passed on cmdline,
    * even when checking for the alias name
    */
    function testIsDefinedAlias()
    {
        $config = array(
            'name|alias' => array(
                'short'   => 'n',
                'min'     => 1,
                'max'     => 1,
                'desc'    => 'An argument.',
                'default' => 'john'
            )
        );

        $args = array('-n', 'arg1');
        $obj  =& Console_Getargs::factory($config, $args);

        $this->assertFalse(PEAR::isError($obj));
        $this->assertTrue($obj->isDefined('n'));
        $this->assertTrue($obj->isDefined('name'));
        $this->assertTrue($obj->isDefined('alias'));
    }



    /**
    * isDefined should return false when the parameter is not
    * given on cmdline - even if there is a default value
    */
    function testIsDefinedDefaultOnly()
    {
        $config = array(
            'name' => array(
                'short'   => 'n',
                'min'     => 1,
                'max'     => 1,
                'desc'    => 'An argument.',
                'default' => 'john'
            )
        );

        $args = array();
        $obj  =& Console_Getargs::factory($config, $args);

        $this->assertFalse(PEAR::isError($obj));
        $this->assertFalse($obj->isDefined('n'));
        $this->assertFalse($obj->isDefined('name'));
    }


}

if (PHPUnit_MAIN_METHOD == 'Getargs_Basic_testCase::main') {
    Getargs_Basic_testCase::main();
}

?>
