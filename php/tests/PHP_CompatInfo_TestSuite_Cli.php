<?php
/**
 * Test suite for the PHP_CompatInfo_Cli class
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: PHP_CompatInfo_TestSuite_Cli.php,v 1.26 2008/12/18 23:06:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.6.0
 */
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "PHP_CompatInfo_TestSuite_Cli::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'PHP/CompatInfo/Cli.php';

/**
 * Test suite class to test standard PHP_CompatInfo API.
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.6.0
 */
class PHP_CompatInfo_TestSuite_Cli extends PHPUnit_Framework_TestCase
{
    /**
     * A PCI object
     * @var  object
     */
    protected $pci;

    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        include_once "PHPUnit/TextUI/TestRunner.php";

        $suite = new PHPUnit_Framework_TestSuite('PHP_CompatInfo CLI Tests');
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->pci = new PHP_CompatInfo_Cli();
    }

    /**
     * Tears down the fixture.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->pci);
    }

    /**
     * Test if a dictionary for an Extension is available or not
     *
     * @param array  $resources   List of Extension dictionaries
     *                            that should be present to perform a unit test
     * @param array &$testSkipped Reasons of tests skipped
     *
     * @return bool
     * @since  version 1.9.0b2
     */
    private function isResourceAvailable($resources, &$testSkipped)
    {
        $dict = array();
        foreach ($resources as $ext) {
            if (!isset($GLOBALS['_PHP_COMPATINFO_FUNC_'.strtoupper($ext)])) {
                $dict[] = $ext;
            }
        }
        if (count($dict) == 1) {
            $testSkipped[] = 'The '. $dict[0] .
                             ' function dictionary is not available.';
        } elseif (count($dict) > 1) {
            $testSkipped[] = 'The '. implode(',', $dict) .
                             ' function dictionaries are not available.';
        }
        return (count($testSkipped) == 0);
    }

    /**
     * Assert results of php exec returns
     *
     * @param string $args Arguments list that will be pass to the 'pci' command
     * @param array  $exp  PCI output results expected
     *
     * @return void
     */
    private function assertPhpExec($args, $exp)
    {
        $command = '@php_bin@ '
                 . '-f @bin_dir@' . DIRECTORY_SEPARATOR . 'pci -- ';

        $output = array();
        $return = 0;
        exec("$command $args", $output, $return);
        if ($return == 0) {
            $this->assertEquals($exp, $output);
        } else {
            $this->assertTrue($return);
        }
    }

    /**
     * Regression test for bug #3657
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=3657
     *         php5 clone constant/token in all sources
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     */
    public function testBug3657()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $exp = array('+-----------------------------+---------+------------+--------------------+',
                     '| File                        | Version | Extensions | Constants/Tokens   |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...File'.$ds.'phpweb-entities.php | 4.0.0   |            | FALSE              |',
                     '|                             |         |            | __FILE__           |',
                     '+-----------------------------+---------+------------+--------------------+');

        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'phpweb-entities.php';

        $args = '-o 30 -f ' . $fn;
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Regression test for bug #6581
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=6581
     *         Functions missing in func_array.php
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     */
    public function testBug6581()
    {
        $resources   = array('bcmath', 'pcre');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $exp = array('+-----------------------------+---------+------------+--------------------+',
                     '| File                        | Version | Extensions | Constants/Tokens   |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...tests'.$ds.'parseFile'.$ds.'math.php | 4.0.0   | bcmath     |                    |',
                     '|                             |         | pcre       |                    |',
                     '+-----------------------------+---------+------------+--------------------+');

        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'math.php';

        $args = '-o 30 -f ' . $fn;
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Tests parsing an empty directory
     *
     * Regression test for bug #8559
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=8559
     *         PHP_CompatInfo fails to scan if it finds empty file in path
     * @see    PHP_CompatInfo_TestSuite_Bugs::testBug8559()
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     */
    public function testBug8559()
    {
        $ds = DIRECTORY_SEPARATOR;
        $dn = dirname(__FILE__) . $ds . 'emptyDir';

        $exp = array('Usage: pci [options]',
                     '',
                     '  -d   --dir (optional)value                      Parse DIR to get its',
                     '                                                  compatibility info ()',
                     '  -f   --file (optional)value                     Parse FILE to get its',
                     '                                                  compatibility info ()',
                     '  -s   --string (optional)value                   Parse STRING to get its',
                     '                                                  compatibility info ()',
                     '  -v   --verbose (optional)value                  Set the verbose level (1)',
                     '  -n   --no-recurse                               Do not recursively parse files',
                     '                                                  when using --dir',
                     '  -if  --ignore-files (optional)value             Data file name which contains',
                     '                                                  a list of file to ignore',
                     '                                                  (files.txt)',
                     '  -id  --ignore-dirs (optional)value              Data file name which contains',
                     '                                                  a list of directory to ignore',
                     '                                                  (dirs.txt)',
                     '  -in  --ignore-functions (optional)value         Data file name which contains',
                     '                                                  a list of php function to',
                     '                                                  ignore (functions.txt)',
                     '  -ic  --ignore-constants (optional)value         Data file name which contains',
                     '                                                  a list of php constant to',
                     '                                                  ignore (constants.txt)',
                     '  -ie  --ignore-extensions (optional)value        Data file name which contains',
                     '                                                  a list of php extension to',
                     '                                                  ignore (extensions.txt)',
                     '  -iv  --ignore-versions values(optional)         PHP versions - functions to',
                     '                                                  exclude when parsing source',
                     '                                                  code (5.0.0)',
                     '  -inm --ignore-functions-match (optional)value   Data file name which contains',
                     '                                                  a list of php function pattern',
                     '                                                  to ignore',
                     '                                                  (functions-match.txt)',
                     '  -iem --ignore-extensions-match (optional)value  Data file name which contains',
                     '                                                  a list of php extension',
                     '                                                  pattern to ignore',
                     '                                                  (extensions-match.txt)',
                     '  -icm --ignore-constants-match (optional)value   Data file name which contains',
                     '                                                  a list of php constant pattern',
                     '                                                  to ignore',
                     '                                                  (constants-match.txt)',
                     '  -fe  --file-ext (optional)value                 A comma separated list of file',
                     '                                                  extensions to parse (only',
                     '                                                  valid if parsing a directory)',
                     '                                                  (php, php4, inc, phtml)',
                     '  -r   --report (optional)value                   Print either "xml" or "csv"',
                     '                                                  report (text)',
                     '  -o   --output-level (optional)value             Print Path/File + Version with',
                     '                                                  additional data (31)',
                     '  -t   --tab (optional)value                      Columns width (29,12,20)',
                     '  -p   --progress (optional)value                 Show a wait message [text] or',
                     '                                                  a progress bar [bar] (bar)',
                     '  -S   --summarize                                Print only summary when',
                     '                                                  parsing directory',
                     '  -V   --version                                  Print version information',
                     '  -h   --help                                     Show this help',
                     '',
                     'No valid files into directory "'. $dn .
                     '". Please check your spelling and try again.',
                     '');

        $args = '-d ' . $dn;
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Regression test for bug #12350
     *
     * Be sure ( chdir() ) to check
     * if file (checkMax.php) is in current directory ( dirname(__FILE__) )
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=12350
     *         file in current directory is not found
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  cli
     */
    public function testBug12350()
    {
        $exp = array('+-----------------------------+---------+------------+--------------------+',
                     '| File                        | Version | Extensions | Constants/Tokens   |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| checkMax.php                | 4.0.7   |            | ...CTORY_SEPARATOR |',
                     '|                             |         |            | TRUE               |',
                     '|                             |         |            | __FILE__           |',
                     '+-----------------------------+---------+------------+--------------------+');

        chdir(dirname(__FILE__));
        $args = '-o 30 -f checkMax.php';
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Parsing string with -s | --string parameter
     *
     * @return void
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  cli
     */
    public function testParseString()
    {
        $exp = array('+-----------------------------+---------+---+------------+--------------------+',
                     '| Source code                 | Version | C | Extensions | Constants/Tokens   |',
                     '+-----------------------------+---------+---+------------+--------------------+',
                     '| <?php ... ?>                | 5.1.1   | 0 |            | DATE_RSS           |',
                     '+-----------------------------+---------+---+------------+--------------------+');

        $str = "\"echo DATE_RSS;\"";

        $args = '-s ' . $str;
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Regression test for request#13147
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=13147
     *         CLI: add filter file extension option on parsing directory
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     * @group  cli
     */
    public function testRequest13147()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $exp = array('+-----------------------------+---------+------------+--------------------+',
                     '| Files                       | Version | Extensions | Constants/Tokens   |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...patInfo'.$ds.'tests'.$ds.'parseDir'.$ds.'* | 5.2.0   | gd         | PHP_SHLIB_SUFFIX   |',
                     '|                             |         | SQLite     | TRUE               |',
                     '|                             |         | xdebug     | ..._ERR_CANT_WRITE |',
                     '|                             |         |            | ...D_ERR_EXTENSION |',
                     '|                             |         |            | ...D_ERR_FORM_SIZE |',
                     '|                             |         |            | ...AD_ERR_INI_SIZE |',
                     '|                             |         |            | UPLOAD_ERR_NO_FILE |',
                     '|                             |         |            | ..._ERR_NO_TMP_DIR |',
                     '|                             |         |            | UPLOAD_ERR_OK      |',
                     '|                             |         |            | UPLOAD_ERR_PARTIAL |',
                     '|                             |         |            | abstract           |',
                     '|                             |         |            | catch              |',
                     '|                             |         |            | clone              |',
                     '|                             |         |            | final              |',
                     '|                             |         |            | implements         |',
                     '|                             |         |            | instanceof         |',
                     '|                             |         |            | interface          |',
                     '|                             |         |            | private            |',
                     '|                             |         |            | protected          |',
                     '|                             |         |            | public             |',
                     '|                             |         |            | throw              |',
                     '|                             |         |            | try                |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...'.$ds.'parseDir'.$ds.'extensions.php | 4.3.2   | gd         | PHP_SHLIB_SUFFIX   |',
                     '|                             |         | SQLite     | TRUE               |',
                     '|                             |         | xdebug     |                    |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...sts'.$ds.'parseDir'.$ds.'phpinfo.php | 4.0.0   |            |                    |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...arseDir'.$ds.'PHP5'.$ds.'tokens.php5 | 5.0.0   |            | abstract           |',
                     '|                             |         |            | catch              |',
                     '|                             |         |            | clone              |',
                     '|                             |         |            | final              |',
                     '|                             |         |            | implements         |',
                     '|                             |         |            | instanceof         |',
                     '|                             |         |            | interface          |',
                     '|                             |         |            | private            |',
                     '|                             |         |            | protected          |',
                     '|                             |         |            | public             |',
                     '|                             |         |            | throw              |',
                     '|                             |         |            | try                |',
                     '+-----------------------------+---------+------------+--------------------+',
                     '| ...ir'.$ds.'PHP5'.$ds.'upload_error.php | 5.2.0   |            | ..._ERR_CANT_WRITE |',
                     '|                             |         |            | ...D_ERR_EXTENSION |',
                     '|                             |         |            | ...D_ERR_FORM_SIZE |',
                     '|                             |         |            | ...AD_ERR_INI_SIZE |',
                     '|                             |         |            | UPLOAD_ERR_NO_FILE |',
                     '|                             |         |            | ..._ERR_NO_TMP_DIR |',
                     '|                             |         |            | UPLOAD_ERR_OK      |',
                     '|                             |         |            | UPLOAD_ERR_PARTIAL |',
                     '|                             |         |            | throw              |',
                     '+-----------------------------+---------+------------+--------------------+');

        $dn = dirname(__FILE__) . $ds . 'parseDir';
        $fe = 'php,php5';

        $args = '-o 30 -fe '. $fe . ' -d '. $dn;
        $this->assertPhpExec($args, $exp);
    }

    /**
     * Regression test for request#13147
     * with a better reading of Extensions and Constants/Tokens columns
     *
     * @return void
     * @see    testRequest13147()
     * @link   http://pear.php.net/bugs/bug.php?id=13147
     *         CLI: add filter file extension option on parsing directory
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     * @group  cli
     */
    public function testReq13147ImproveRender()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $exp = array('+-----------------------------+---------+-------------+-----------------------+',
                     '| Files                       | Version | Extensions  | Constants/Tokens      |',
                     '+-----------------------------+---------+-------------+-----------------------+',
                     '| ...patInfo'.$ds.'tests'.$ds.'parseDir'.$ds.'* | 5.2.0   | gd          | PHP_SHLIB_SUFFIX      |',
                     '|                             |         | SQLite      | TRUE                  |',
                     '|                             |         | xdebug      | UPLOAD_ERR_CANT_WRITE |',
                     '|                             |         |             | UPLOAD_ERR_EXTENSION  |',
                     '|                             |         |             | UPLOAD_ERR_FORM_SIZE  |',
                     '|                             |         |             | UPLOAD_ERR_INI_SIZE   |',
                     '|                             |         |             | UPLOAD_ERR_NO_FILE    |',
                     '|                             |         |             | UPLOAD_ERR_NO_TMP_DIR |',
                     '|                             |         |             | UPLOAD_ERR_OK         |',
                     '|                             |         |             | UPLOAD_ERR_PARTIAL    |',
                     '|                             |         |             | abstract              |',
                     '|                             |         |             | catch                 |',
                     '|                             |         |             | clone                 |',
                     '|                             |         |             | final                 |',
                     '|                             |         |             | implements            |',
                     '|                             |         |             | instanceof            |',
                     '|                             |         |             | interface             |',
                     '|                             |         |             | private               |',
                     '|                             |         |             | protected             |',
                     '|                             |         |             | public                |',
                     '|                             |         |             | throw                 |',
                     '|                             |         |             | try                   |',
                     '+-----------------------------+---------+-------------+-----------------------+',
                     '| ...'.$ds.'parseDir'.$ds.'extensions.php | 4.3.2   | gd          | PHP_SHLIB_SUFFIX      |',
                     '|                             |         | SQLite      | TRUE                  |',
                     '|                             |         | xdebug      |                       |',
                     '+-----------------------------+---------+-------------+-----------------------+',
                     '| ...sts'.$ds.'parseDir'.$ds.'phpinfo.php | 4.0.0   |             |                       |',
                     '+-----------------------------+---------+-------------+-----------------------+',
                     '| ...arseDir'.$ds.'PHP5'.$ds.'tokens.php5 | 5.0.0   |             | abstract              |',
                     '|                             |         |             | catch                 |',
                     '|                             |         |             | clone                 |',
                     '|                             |         |             | final                 |',
                     '|                             |         |             | implements            |',
                     '|                             |         |             | instanceof            |',
                     '|                             |         |             | interface             |',
                     '|                             |         |             | private               |',
                     '|                             |         |             | protected             |',
                     '|                             |         |             | public                |',
                     '|                             |         |             | throw                 |',
                     '|                             |         |             | try                   |',
                     '+-----------------------------+---------+-------------+-----------------------+',
                     '| ...ir'.$ds.'PHP5'.$ds.'upload_error.php | 5.2.0   |             | UPLOAD_ERR_CANT_WRITE |',
                     '|                             |         |             | UPLOAD_ERR_EXTENSION  |',
                     '|                             |         |             | UPLOAD_ERR_FORM_SIZE  |',
                     '|                             |         |             | UPLOAD_ERR_INI_SIZE   |',
                     '|                             |         |             | UPLOAD_ERR_NO_FILE    |',
                     '|                             |         |             | UPLOAD_ERR_NO_TMP_DIR |',
                     '|                             |         |             | UPLOAD_ERR_OK         |',
                     '|                             |         |             | UPLOAD_ERR_PARTIAL    |',
                     '|                             |         |             | throw                 |',
                     '+-----------------------------+---------+-------------+-----------------------+');

        $dn = dirname(__FILE__) . $ds . 'parseDir';
        $fe = 'php,php5';

        $args = '-o 30 -t 29,13,23 -fe '. $fe . ' -d '. $dn;
        $this->assertPhpExec($args, $exp);
    }
}

// Call PHP_CompatInfo_TestSuite_Cli::main() if file is executed directly.
if (PHPUnit_MAIN_METHOD == "PHP_CompatInfo_TestSuite_Cli::main") {
    PHP_CompatInfo_TestSuite_Cli::main();
}
?>