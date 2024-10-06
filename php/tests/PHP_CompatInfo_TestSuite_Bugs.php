<?php
/**
 * Test suite for bugs declared in the PHP_CompatInfo class
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: PHP_CompatInfo_TestSuite_Bugs.php,v 1.23 2008/12/18 23:06:45 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.6.0
 */
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "PHP_CompatInfo_TestSuite_Bugs::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'PHP/CompatInfo.php';

/**
 * Test suite class to test standard PHP_CompatInfo API.
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  Release: 1.9.0
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.6.0
 */
class PHP_CompatInfo_TestSuite_Bugs extends PHPUnit_Framework_TestCase
{
    /**
     * A PCI object
     * @var  object
     */
    protected $pci;

    /**
     * Filename where to write results of debug pci events notification
     * @var   string
     * @since 1.8.0RC1
     */
    private $destLogFile;

    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        include_once "PHPUnit/TextUI/TestRunner.php";

        $suite = new PHPUnit_Framework_TestSuite('PHP_CompatInfo Bugs Tests');
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
        $this->destLogFile = dirname(__FILE__) . DIRECTORY_SEPARATOR .
                             __CLASS__ . '.log';

        $this->pci = new PHP_CompatInfo('null');
        $this->pci->addListener(array(&$this, 'debugNotify'));
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
     * PCI Events notification observer for debug purpose only
     *
     * @param object &$auditEvent Instance of Event_Notification object
     *
     * @return void
     */
    public function debugNotify(&$auditEvent)
    {
        $notifyName = $auditEvent->getNotificationName();
        $notifyInfo = $auditEvent->getNotificationInfo();

        if ($notifyName == PHP_COMPATINFO_EVENT_AUDITSTARTED) {
            $dbt = debug_backtrace();
            error_log('backtrace: '. $dbt[7]['function'] . PHP_EOL,
                      3, $this->destLogFile);
            error_log($notifyName.':'. PHP_EOL .
                      var_export($notifyInfo, true) . PHP_EOL,
                      3, $this->destLogFile);

        } elseif ($notifyName == PHP_COMPATINFO_EVENT_AUDITFINISHED) {
            error_log($notifyName.':'. PHP_EOL .
                      var_export($notifyInfo, true) . PHP_EOL,
                      3, $this->destLogFile);
        }
    }

    /**
     * Retrieve files list to be ignore by parsing process
     *
     * @param string $dir     Directory to parse
     * @param array  $options Parser options
     *
     * @return array
     * @since  version 1.8.0RC1
     */
    private function getIgnoredFileList($dir, $options)
    {
        $files = $this->pci->parser->getFileList($dir, $options);

        $ff               = new File_Find();
        $ff->dirsep       = DIRECTORY_SEPARATOR;
        list(, $allfiles) = $ff->maptree($dir);

        $ignored_files = PHP_CompatInfo_Parser::_arrayDiff($allfiles, $files);
        return $ignored_files;
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
     * Regression test for bug #1626
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=1626
     *         Class calls are seen wrong
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     */
    public function testBug1626()
    {
        $str = '<?php
include("File.php");
File::write("test", "test");
?>';
        $r   = $this->pci->parseString($str);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.0',
                     'classes' => array(),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Regression test for bug #2771
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=2771
     *         Substr($var,4) not working for SAPI_ extensions
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     */
    public function testBug2771()
    {
        $str = '<?php
apache_request_headers();
apache_response_headers();
?>';
        $r   = $this->pci->parseString($str);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.0',
                     'classes' => array(),
                     'functions' => array('apache_request_headers',
                                          'apache_response_headers'),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Regression test for bug #7813
     *
     * Parse source file of PEAR_PackageUpdate 0.5.0
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=7813
     *         wrong PHP minimum version detection
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     */
    public function testBug7813()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'PackageUpdate.php';
        $opt = array('debug' => true,
                     'ignore_functions' => array('debug_backtrace'));
        $r   = $this->pci->parseFile($fn, $opt);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('debug_backtrace'),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.0',
                     'classes' => array('PEAR_Config'),
                     'functions' => array('array_keys',
                                          'array_shift',
                                          'class_exists',
                                          'count',
                                          'debug_backtrace',
                                          'define',
                                          'explode',
                                          'factory',
                                          'fclose',
                                          'file_exists',
                                          'file_get_contents',
                                          'fopen',
                                          'function_exists',
                                          'fwrite',
                                          'get_class',
                                          'get_include_path',
                                          'getenv',
                                          'is_array',
                                          'is_int',
                                          'is_readable',
                                          'reset',
                                          'serialize',
                                          'settype',
                                          'strlen',
                                          'unserialize',
                                          'version_compare'),
                     'extensions' => array(),
                     'constants' => array('DIRECTORY_SEPARATOR',
                                          'E_COMPILE_ERROR',
                                          'E_COMPILE_WARNING',
                                          'E_CORE_ERROR',
                                          'E_CORE_WARNING',
                                          'E_ERROR',
                                          'E_NOTICE',
                                          'E_PARSE',
                                          'E_USER_ERROR',
                                          'E_USER_NOTICE',
                                          'E_USER_WARNING',
                                          'E_WARNING',
                                          'FALSE',
                                          'NULL',
                                          'PATH_SEPARATOR',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(1, array(array('debug_backtrace'),
                                                   array(),
                                                   array())),
                     '4.0.0' =>
                     array(
                       0 =>
                       array(
                         'function' => 'define',
                         'extension' => false,
                         'pecl' => false
                       ),
                       1 =>
                       array (
                         'function' => 'get_class',
                         'extension' => false,
                         'pecl' => false
                       ),
                       2 =>
                       array (
                         'function' => 'function_exists',
                         'extension' => false,
                         'pecl' => false
                       ),
                       3 =>
                       array (
                         'function' => 'count',
                         'extension' => false,
                         'pecl' => false
                       ),
                       4 =>
                       array (
                         'function' => 'class_exists',
                         'extension' => false,
                         'pecl' => false
                       ),
                       5 =>
                       array (
                         'function' => 'explode',
                         'extension' => false,
                         'pecl' => false
                       ),
                       6 =>
                       array (
                         'function' => 'file_exists',
                         'extension' => false,
                         'pecl' => false
                       ),
                       7 =>
                       array (
                         'function' => 'is_readable',
                         'extension' => false,
                         'pecl' => false
                       ),
                       8 =>
                       array (
                         'function' => 'unserialize',
                         'extension' => false,
                         'pecl' => false
                       ),
                       9 =>
                       array (
                         'function' => 'strlen',
                         'extension' => false,
                         'pecl' => false
                       ),
                       10 =>
                       array (
                         'function' => 'getenv',
                         'extension' => false,
                         'pecl' => false
                       ),
                       11 =>
                       array (
                         'function' => 'reset',
                         'extension' => false,
                         'pecl' => false
                       ),
                       12 =>
                       array (
                         'function' => 'array_keys',
                         'extension' => false,
                         'pecl' => false
                       ),
                       13 =>
                       array (
                         'function' => 'fopen',
                         'extension' => false,
                         'pecl' => false
                       ),
                       14 =>
                       array (
                         'function' => 'serialize',
                         'extension' => false,
                         'pecl' => false
                       ),
                       15 =>
                       array (
                         'function' => 'fwrite',
                         'extension' => false,
                         'pecl' => false
                       ),
                       16 =>
                       array (
                         'function' => 'fclose',
                         'extension' => false,
                         'pecl' => false
                       ),
                       17 =>
                       array (
                         'function' => 'settype',
                         'extension' => false,
                         'pecl' => false
                       ),
                       18 =>
                       array (
                         'function' => 'is_int',
                         'extension' => false,
                         'pecl' => false
                       ),
                       19 =>
                       array (
                         'function' => 'is_array',
                         'extension' => false,
                         'pecl' => false,
                       ),
                       20 =>
                       array (
                         'function' => 'array_shift',
                         'extension' => false,
                         'pecl' => false
                       )
                     ),
                     '4.0.7' =>
                     array (
                       0 =>
                       array (
                         'function' => 'version_compare',
                         'extension' => false,
                         'pecl' => false
                       )
                     ),
                     '4.3.0' =>
                     array (
                       0 =>
                       array (
                         'function' => 'get_include_path',
                         'extension' => false,
                         'pecl' => false,
                       ),
                       1 =>
                       array (
                         'function' => 'file_get_contents',
                         'extension' => false,
                         'pecl' => false
                       )
                     ));

        $this->assertSame($exp, $r);
    }

    /**
     * Regression test for bug #8559
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=8559
     *         PHP_CompatInfo fails to scan if it finds empty file in path
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     */
    public function testBug8559()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'emptyDir';
        $r   = $this->pci->parseDir($dir);
        $this->assertFalse($r);
    }

    /**
     * Regression test for bug #10100
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=10100
     *         Wrong parsing of possible attributes in strings
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  bugs
     */
    public function testBug10100()
    {
        $str = '<?php
$test = "public$link";
?>';
        $r   = $this->pci->parseString($str);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.0',
                     'classes' => array(),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Regression test for bug #13873
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=13873
     *         PHP_CompatInfo fails to scan conditional code
     *         if it finds other than encapsed string
     * @covers PHP_CompatInfo::parseFolder
     * @group  parseDir
     * @group  bugs
     */
    public function testBug13873()
    {
        $resources   = array('date', 'pcre');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'beehiveforum082' . $ds . 'forum';
        $opt = array();
        $r   = $this->pci->parseFolder($dir, $opt);
        $exp = array('ignored_files' => $this->getIgnoredFileList($dir, $opt),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.6',
                     'classes' => array(),
                     'functions' => array('_htmlentities',
                                          '_stripslashes',
                                          'array_map',
                                          'array_merge',
                                          'basename',
                                          'bh_session_check_perm',
                                          'bh_session_get_value',
                                          'bh_setcookie',
                                          'db_affected_rows',
                                          'db_connect',
                                          'db_escape_string',
                                          'db_fetch_array',
                                          'db_insert_id',
                                          'db_num_rows',
                                          'db_query',
                                          'db_trigger_error',
                                          'defined',
                                          'delete_attachment_by_aid',
                                          'explode',
                                          'fclose',
                                          'file_exists',
                                          'filesize',
                                          'fix_html',
                                          'floor',
                                          'folder_get_available_by_forum',
                                          'fopen',
                                          'form_checkbox',
                                          'form_input_hidden',
                                          'form_input_password',
                                          'form_submit',
                                          'forum_apply_user_permissions',
                                          'forum_check_global_setting_name',
                                          'forum_check_password',
                                          'forum_check_setting_name',
                                          'forum_closed_message',
                                          'forum_delete',
                                          'forum_delete_tables',
                                          'forum_get_all_prefixes',
                                          'forum_get_global_settings',
                                          'forum_get_password',
                                          'forum_get_saved_password',
                                          'forum_get_setting',
                                          'forum_get_settings_by_fid',
                                          'forum_process_unread_cutoff',
                                          'forum_restricted_message',
                                          'forum_search',
                                          'forum_start_page_get_html',
                                          'fread',
                                          'function_exists',
                                          'fwrite',
                                          'get_forum_data',
                                          'get_request_uri',
                                          'get_table_prefix',
                                          'get_webtag',
                                          'header',
                                          'html_display_error_msg',
                                          'html_display_warning_msg',
                                          'html_draw_bottom',
                                          'html_draw_top',
                                          'html_get_top_frame_name',
                                          'implode',
                                          'in_array',
                                          'install_get_table_conflicts',
                                          'intval',
                                          'is_array',
                                          'is_dir',
                                          'is_md5',
                                          'is_null',
                                          'is_numeric',
                                          'load_language_file',
                                          'md5',
                                          'mkdir',
                                          'mt_rand',
                                          'ob_end_clean',
                                          'ob_get_contents',
                                          'ob_start',
                                          'perm_group_get_users',
                                          'preg_match',
                                          'sizeof',
                                          'sprintf',
                                          'str_replace',
                                          'stristr',
                                          'strlen',
                                          'strtoupper',
                                          'time',
                                          'trim',
                                          'user_get_logon',
                                          'word_filter_rem_ob_tags'),
                     'extensions' => array('date', 'pcre'),
                     'constants' => array('FALSE',
                                          'TRUE',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(4)
                    );
        $this->assertSame($exp, $r);
    }

    /**
     * Regression test for bug #14696
     *
     * @return void
     * @link   http://pear.php.net/bugs/bug.php?id=14696
     *         PHP_CompatInfo fails to scan code line when not ended with ;
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  bugs
     */
    public function testBug14696()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'kohana22'
                                 . $ds . 'modules' . $ds . 'gmaps'
                                 . $ds . 'javascript.php';

        $r   = $this->pci->parseFile($fn);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.0',
                     'classes' => array(),
                     'functions' => array('substr'),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0)
                     );

        $this->assertSame($exp, $r);
    }
}

// Call PHP_CompatInfo_TestSuite_Bugs::main() if file is executed directly.
if (PHPUnit_MAIN_METHOD == "PHP_CompatInfo_TestSuite_Bugs::main") {
    PHP_CompatInfo_TestSuite_Bugs::main();
}
?>