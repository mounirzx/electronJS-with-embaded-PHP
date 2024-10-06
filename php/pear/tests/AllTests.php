<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Event_Dispatcher_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'DispatcherTest.php';

class Event_Dispatcher_AllTests
{
    // {{{ main()

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    // }}}
    // {{{ suite()

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Event Dispatcher Tests');
        $suite->addTestSuite('DispatcherTest');

        return $suite;
    }

    // }}}
}

if (PHPUnit_MAIN_METHOD == 'Event_Dispatcher_AllTests::main') {
    Event_Dispatcher_AllTests::main();
}

?>

