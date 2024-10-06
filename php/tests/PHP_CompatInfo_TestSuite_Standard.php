<?php
/**
 * Test suite for the PHP_CompatInfo class
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: PHP_CompatInfo_TestSuite_Standard.php,v 1.54 2008/12/31 14:32:28 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @since    File available since Release 1.6.0
 */
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "PHP_CompatInfo_TestSuite_Standard::main");
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
class PHP_CompatInfo_TestSuite_Standard extends PHPUnit_Framework_TestCase
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

        $suite = new PHPUnit_Framework_TestSuite('PHP_CompatInfo Standard Tests');
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
     * Tests tokenizer with a single file and empty contents
     *
     * @return void
     * @group  standard
     */
    public function testTokenizerWithEmptyFile()
    {
        if (token_name(311) !== 'T_INLINE_HTML') {
            $this->markTestSkipped('Tokens values of Tokenizer have changed');
        }

        $ds = DIRECTORY_SEPARATOR;
        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'empty.php';

        $r     = $this->pci->parser->_tokenize($fn, false);
        $empty = array(0 =>
                   array (
                   0 => 311,
                   1 => "\n",
                   2 => 1));
        $this->assertSame($empty, $r);

        $r     = $this->pci->parser->_tokenize($fn, false, true);
        $empty = array(0 =>
                   array (
                   0 => 311,
                   1 => "\n",
                   2 => 1,
                   3 => 'T_INLINE_HTML'));
        $this->assertSame($empty, $r);
    }

    /**
     * Tests parsing a single file that does not exists
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseInvalidFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'nothere.php';

        $r = $this->pci->parseFile($fn);
        $this->assertFalse($r);
    }

    /**
     * Tests parsing a single file with empty contents
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseEmptyFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'empty.php';

        $r   = $this->pci->parseFile($fn);
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
     * Tests parsing a single file
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseNotEmptyFile()
    {
        $resources   = array('bcmath', 'pcre');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds = DIRECTORY_SEPARATOR;
        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'math.php';

        $r = $this->pci->parseFile($fn);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.0',
                     'classes' => array(),
                     'functions' => array('bcsub', 'preg_match'),
                     'extensions' => array('bcmath', 'pcre'),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_functions' option
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreFunctions()
    {
        $resources   = array('date');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_functions' => array('simplexml_load_file'));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('simplexml_load_file'),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array(),
                     'functions' => array('basename',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'defined',
                                          'dirname',
                                          'function_exists',
                                          'phpversion',
                                          'simplexml_load_file',
                                          'strtoupper',
                                          'substr',
                                          'version_compare'),
                     'extensions' => array('date'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_constants' option
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreConstants()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_constants' => array('PHP_EOL'));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array('PHP_EOL'),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array(),
                     'functions' => array('basename',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'defined',
                                          'dirname',
                                          'function_exists',
                                          'phpversion',
                                          'simplexml_load_file',
                                          'strtoupper',
                                          'substr',
                                          'version_compare'),
                     'extensions' => array('date', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_extensions' option
     *
     * @return void
     * @link   http://www.php.net/zip
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreExtensions()
    {
        $resources   = array('zip');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'zip.php';
        $opt = array('ignore_extensions' => array('zip'));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('zip_close',
                                                  'zip_entry_compressedsize',
                                                  'zip_entry_compressionmethod',
                                                  'zip_entry_filesize',
                                                  'zip_entry_name',
                                                  'zip_open',
                                                  'zip_read'),
                     'ignored_extensions' => array('zip'),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.0.0',
                     'classes' => array(),
                     'functions' => array('zip_close',
                                          'zip_entry_compressedsize',
                                          'zip_entry_compressionmethod',
                                          'zip_entry_filesize',
                                          'zip_entry_name',
                                          'zip_open',
                                          'zip_read'),
                     'extensions' => array('zip'),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_versions' option
     * Ignored all PHP functions between 4.3.10 and 4.4.8
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreVersions()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_versions' => array('4.3.10', '4.4.8'));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array(),
                     'functions' => array('basename',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'defined',
                                          'dirname',
                                          'function_exists',
                                          'phpversion',
                                          'simplexml_load_file',
                                          'strtoupper',
                                          'substr',
                                          'version_compare'),
                     'extensions' => array('date', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'PHP_OS',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing an invalid input
     *
     * @return void
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  standard
     */
    public function testParseInvalidString()
    {
        $in = array();
        $r  = $this->pci->parseString($in);
        $this->assertFalse($r);
    }

    /**
     * Tests parsing a string
     *
     * @return void
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  standard
     */
    public function testParseNotEmptyString()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'sample_req6056.php';
        $str = file_get_contents($fn);

        $r = $this->pci->parseString($str);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '5.0.4',
                     'version' => '5.1.0',
                     'classes' => array(),
                     'functions' => array('array_diff_key',
                                          'php_check_syntax'),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing string DATE constants
     *
     * @return void
     * @link   http://php.net/manual/en/ref.datetime.php Predefined Date Constants
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  standard
     */
    public function testParseDate511String()
    {
        $str = '<?php
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

        $r = $this->pci->parseString($str);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array(),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array('DATE_ATOM', 'DATE_COOKIE',
                                          'DATE_ISO8601',
                                          'DATE_RFC1036', 'DATE_RFC1123',
                                          'DATE_RFC2822',
                                          'DATE_RFC822', 'DATE_RFC850',
                                          'DATE_RSS', 'DATE_W3C'),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing string DATE constants
     *
     * @return void
     * @link   http://php.net/manual/en/ref.datetime.php Predefined Date Constants
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  standard
     */
    public function testParseDate513String()
    {
        $str = '<?php
$nl = "\n";
echo "$nl Rfc3339 = " . DATE_RFC3339;
echo "$nl RSS     = " . DATE_RSS;
?>';

        $r = $this->pci->parseString($str);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.3',
                     'classes' => array(),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array('DATE_RFC3339', 'DATE_RSS'),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing string UPLOAD_ERR constants
     *
     * @return void
     * @link   http://www.php.net/features.file-upload.errors
     *         File Upload Error specific Constants
     * @covers PHP_CompatInfo::parseString
     * @group  parseString
     * @group  standard
     */
    public function testParseUploadErrString()
    {
        $str = '<?php
$uploadErrors = array(
    UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
    UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
    UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
    UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
    UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
    UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
    UPLOAD_ERR_EXTENSION  => "File upload stopped by extension.",
);

$errorCode = $_FILES["myUpload"]["error"];

if ($errorCode !== UPLOAD_ERR_OK) {
    if (isset($uploadErrors[$errorCode])) {
        throw new Exception($uploadErrors[$errorCode]);
    } else {
        throw new Exception("Unknown error uploading file.");
    }
}
?>';
        $r   = $this->pci->parseString($str);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.2.0',
                     'classes' => array('Exception'),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array('UPLOAD_ERR_CANT_WRITE',
                                          'UPLOAD_ERR_EXTENSION',
                                          'UPLOAD_ERR_FORM_SIZE',
                                          'UPLOAD_ERR_INI_SIZE',
                                          'UPLOAD_ERR_NO_FILE',
                                          'UPLOAD_ERR_NO_TMP_DIR',
                                          'UPLOAD_ERR_OK',
                                          'UPLOAD_ERR_PARTIAL'),
                     'tokens' => array('throw'),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory that does not exists
     *
     * @return void
     * @covers PHP_CompatInfo::parseFolder
     * @group  parseDir
     * @group  standard
     */
    public function testParseInvalidDirectory()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds . 'nothere';

        $r = $this->pci->parseFolder($dir);
        $this->assertFalse($r);
    }

    /**
     * Tests parsing a directory without recursive 'recurse_dir' option
     *
     * @return void
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     * @group  standard
     */
    public function testParseNoRecursiveDirectory()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir';
        $opt = array('recurse_dir' => false);

        $r   = $this->pci->parseDir($dir, $opt);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.2',
                     'classes' => array(),
                     'functions' => array('apache_get_modules',
                                          'dl',
                                          'extension_loaded',
                                          'imageantialias',
                                          'imagecreate',
                                          'phpinfo',
                                          'print_r',
                                          'sqlite_libversion',
                                          'xdebug_start_trace',
                                          'xdebug_stop_trace'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(2),
                     $dir . $ds . 'extensions.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '4.3.2',
                               'classes' => array(),
                               'functions' => array('apache_get_modules',
                                                    'dl',
                                                    'extension_loaded',
                                                    'imageantialias',
                                                    'imagecreate',
                                                    'print_r',
                                                    'sqlite_libversion',
                                                    'xdebug_start_trace',
                                                    'xdebug_stop_trace'),
                               'extensions' => array('gd',
                                                     'SQLite',
                                                     'xdebug'),
                               'constants' => array('PHP_SHLIB_SUFFIX',
                                                    'TRUE'),
                               'tokens' => array(),
                               'cond_code' => array(2)),
                     $dir . $ds . 'phpinfo.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '4.0.0',
                               'classes' => array(),
                               'functions' => array('phpinfo'),
                               'extensions' => array(),
                               'constants' => array(),
                               'tokens' => array(),
                               'cond_code' => array(0)));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory with 'recurse_dir' option active
     * and filter files by extension with 'file_ext' option
     *
     * @return void
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     * @group  standard
     */
    public function testParseRecursiveDirectory()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));

        $r   = $this->pci->parseDir($dir, $opt);
        $exp = array('ignored_files' => $this->getIgnoredFileList($dir, $opt),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.2.0',
                     'classes' => array('Exception'),
                     'functions' => array('apache_get_modules',
                                          'dl',
                                          'extension_loaded',
                                          'imageantialias',
                                          'imagecreate',
                                          'phpinfo',
                                          'print_r',
                                          'sqlite_libversion',
                                          'str_replace',
                                          'xdebug_start_trace',
                                          'xdebug_stop_trace'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE',
                                          'UPLOAD_ERR_CANT_WRITE',
                                          'UPLOAD_ERR_EXTENSION',
                                          'UPLOAD_ERR_FORM_SIZE',
                                          'UPLOAD_ERR_INI_SIZE',
                                          'UPLOAD_ERR_NO_FILE',
                                          'UPLOAD_ERR_NO_TMP_DIR',
                                          'UPLOAD_ERR_OK',
                                          'UPLOAD_ERR_PARTIAL'),
                     'tokens' => array('abstract',
                                       'catch',
                                       'clone',
                                       'final',
                                       'implements',
                                       'instanceof',
                                       'interface',
                                       'private',
                                       'protected',
                                       'public',
                                       'throw',
                                       'try'),
                     'cond_code' => array(2),
                     $dir . 'extensions.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '4.3.2',
                               'classes' => array(),
                               'functions' => array('apache_get_modules',
                                                    'dl',
                                                    'extension_loaded',
                                                    'imageantialias',
                                                    'imagecreate',
                                                    'print_r',
                                                    'sqlite_libversion',
                                                    'xdebug_start_trace',
                                                    'xdebug_stop_trace'),
                               'extensions' => array('gd',
                                                     'SQLite',
                                                     'xdebug'),
                               'constants' => array('PHP_SHLIB_SUFFIX',
                                                    'TRUE'),
                               'tokens' => array(),
                               'cond_code' => array(2)),
                     $dir . 'phpinfo.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '4.0.0',
                               'classes' => array(),
                               'functions' => array('phpinfo'),
                               'extensions' => array(),
                               'constants' => array(),
                               'tokens' => array(),
                               'cond_code' => array(0)),
                     $dir . 'PHP5' . $ds . 'tokens.php5' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '5.0.0',
                               'classes' => array('Exception'),
                               'functions' => array('str_replace'),
                               'extensions' => array(),
                               'constants' => array(),
                               'tokens' => array('abstract',
                                                 'catch',
                                                 'clone',
                                                 'final',
                                                 'implements',
                                                 'instanceof',
                                                 'interface',
                                                 'private',
                                                 'protected',
                                                 'public',
                                                 'throw',
                                                 'try'),
                               'cond_code' => array(0)),
                     $dir . 'PHP5' . $ds . 'upload_error.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '5.2.0',
                               'classes' => array('Exception'),
                               'functions' => array(),
                               'extensions' => array(),
                               'constants' => array('UPLOAD_ERR_CANT_WRITE',
                                                    'UPLOAD_ERR_EXTENSION',
                                                    'UPLOAD_ERR_FORM_SIZE',
                                                    'UPLOAD_ERR_INI_SIZE',
                                                    'UPLOAD_ERR_NO_FILE',
                                                    'UPLOAD_ERR_NO_TMP_DIR',
                                                    'UPLOAD_ERR_OK',
                                                    'UPLOAD_ERR_PARTIAL'),
                               'tokens' => array('throw'),
                               'cond_code' => array(0)));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory with 'recurse_dir' option active
     * with 'ignore_files' options
     *
     * @return void
     * @covers PHP_CompatInfo::parseDir
     * @group  parseDir
     * @group  standard
     */
    public function testParseRecursiveDirectoryWithIgnoreFiles()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'ignore_files' => array($dir . 'phpinfo.php'),
                     'file_ext' => array('php'));

        $r   = $this->pci->parseDir($dir, $opt);
        $exp = array('ignored_files' => $this->getIgnoredFileList($dir, $opt),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.2.0',
                     'classes' => array('Exception'),
                     'functions' => array('apache_get_modules',
                                          'dl',
                                          'extension_loaded',
                                          'imageantialias',
                                          'imagecreate',
                                          'print_r',
                                          'sqlite_libversion',
                                          'xdebug_start_trace',
                                          'xdebug_stop_trace'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE',
                                          'UPLOAD_ERR_CANT_WRITE',
                                          'UPLOAD_ERR_EXTENSION',
                                          'UPLOAD_ERR_FORM_SIZE',
                                          'UPLOAD_ERR_INI_SIZE',
                                          'UPLOAD_ERR_NO_FILE',
                                          'UPLOAD_ERR_NO_TMP_DIR',
                                          'UPLOAD_ERR_OK',
                                          'UPLOAD_ERR_PARTIAL'),
                     'tokens' => array('throw'),
                     'cond_code' => array(2),
                     $dir . 'extensions.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '4.3.2',
                               'classes' => array(),
                               'functions' => array('apache_get_modules',
                                                    'dl',
                                                    'extension_loaded',
                                                    'imageantialias',
                                                    'imagecreate',
                                                    'print_r',
                                                    'sqlite_libversion',
                                                    'xdebug_start_trace',
                                                    'xdebug_stop_trace'),
                               'extensions' => array('gd',
                                                     'SQLite',
                                                     'xdebug'),
                               'constants' => array('PHP_SHLIB_SUFFIX',
                                                    'TRUE'),
                               'tokens' => array(),
                               'cond_code' => array(2)),
                     $dir . 'PHP5' . $ds . 'upload_error.php' =>
                         array('ignored_functions' => array(),
                               'ignored_extensions' => array(),
                               'ignored_constants' => array(),
                               'max_version' => '',
                               'version' => '5.2.0',
                               'classes' => array('Exception'),
                               'functions' => array(),
                               'extensions' => array(),
                               'constants' => array('UPLOAD_ERR_CANT_WRITE',
                                                    'UPLOAD_ERR_EXTENSION',
                                                    'UPLOAD_ERR_FORM_SIZE',
                                                    'UPLOAD_ERR_INI_SIZE',
                                                    'UPLOAD_ERR_NO_FILE',
                                                    'UPLOAD_ERR_NO_TMP_DIR',
                                                    'UPLOAD_ERR_OK',
                                                    'UPLOAD_ERR_PARTIAL'),
                               'tokens' => array('throw'),
                               'cond_code' => array(0)));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple file data sources reference
     *
     * @return void
     * @covers PHP_CompatInfo::parseArray
     * @group  parseArray
     * @group  standard
     */
    public function testParseArrayFile()
    {
        $resources   = array('pcre');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseFile';
        $src = array($dir . $ds . 'File_Find-1.3.0__Find.php');

        $r   = $this->pci->parseArray($src);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.0',
                     'classes' => array('File_Find'),
                     'functions' => array('_file_find_match_shell_get_pattern',
                                          'addcslashes',
                                          'array_merge',
                                          'array_pop',
                                          'array_push',
                                          'basename',
                                          'closedir',
                                          'count',
                                          'define',
                                          'defined',
                                          'each',
                                          'explode',
                                          'glob',
                                          'implode',
                                          'is_a',
                                          'is_array',
                                          'is_dir',
                                          'is_readable',
                                          'maptree',
                                          'maptreemultiple',
                                          'opendir',
                                          'preg_match',
                                          'preg_replace',
                                          'preg_split',
                                          'print_r',
                                          'readdir',
                                          'reset',
                                          'search',
                                          'sizeof',
                                          'str_replace',
                                          'strcasecmp',
                                          'strlen',
                                          'strpos',
                                          'substr',
                                          'substr_count'),
                     'extensions' => array('pcre'),
                     'constants' => array('FALSE',
                                          'NULL',
                                          'PREG_SPLIT_DELIM_CAPTURE',
                                          'PREG_SPLIT_NO_EMPTY',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(4));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple file data sources reference
     * with option 'ignore_files'
     *
     * @return void
     * @covers PHP_CompatInfo::parseArray
     * @group  parseArray
     * @group  standard
     */
    public function testParseArrayFileWithIgnoreFiles()
    {
        $resources   = array('pcre');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds   = DIRECTORY_SEPARATOR;
        $inc  = get_included_files();
        $dir  = dirname(__FILE__) . $ds . 'parseFile';
        $src  = array($dir . $ds . 'File_Find-1.3.0__Find.php');
        $excl = array();

        foreach ($inc as $file) {
            if (basename($file) == 'PEAR.php') {
                $excl[] = $file;
                $src[]  = $file;
            }
        }
        $opt = array('ignore_files' => $excl);

        $r   = $this->pci->parseArray($src, $opt);
        $exp = array('ignored_files' => $excl,
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.0',
                     'classes' => array('File_Find'),
                     'functions' => array('_file_find_match_shell_get_pattern',
                                          'addcslashes',
                                          'array_merge',
                                          'array_pop',
                                          'array_push',
                                          'basename',
                                          'closedir',
                                          'count',
                                          'define',
                                          'defined',
                                          'each',
                                          'explode',
                                          'glob',
                                          'implode',
                                          'is_a',
                                          'is_array',
                                          'is_dir',
                                          'is_readable',
                                          'maptree',
                                          'maptreemultiple',
                                          'opendir',
                                          'preg_match',
                                          'preg_replace',
                                          'preg_split',
                                          'print_r',
                                          'readdir',
                                          'reset',
                                          'search',
                                          'sizeof',
                                          'str_replace',
                                          'strcasecmp',
                                          'strlen',
                                          'strpos',
                                          'substr',
                                          'substr_count'),
                     'extensions' => array('pcre'),
                     'constants' => array('FALSE',
                                          'NULL',
                                          'PREG_SPLIT_DELIM_CAPTURE',
                                          'PREG_SPLIT_NO_EMPTY',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(4));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple strings (chunk of code)
     *
     * @return void
     * @covers PHP_CompatInfo::parseArray
     * @group  parseArray
     * @group  standard
     */
    public function testParseArrayString()
    {
        $code1 = "<?php
php_check_syntax('somefile.php');
?>";
        $code2 = "<?php
\$array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
\$array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8);

\$diff = array_diff_key(\$array1, \$array2);
?>";
        $data  = array($code1, $code2);
        $opt   = array('is_string' => true);

        $r   = $this->pci->parseArray($data, $opt);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '5.0.4',
                     'version' => '5.1.0',
                     'classes' => array(),
                     'functions' => array('array_diff_key', 'php_check_syntax'),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0),
                     'string_1' => array(
                          'ignored_functions' => array(),
                          'ignored_extensions' => array(),
                          'ignored_constants' => array(),
                          'max_version' => '5.0.4',
                          'version' => '5.0.0',
                          'classes' => array(),
                          'functions' => array('php_check_syntax'),
                          'extensions' => array(),
                          'constants' => array(),
                          'tokens' => array(),
                          'cond_code' => array(0)),
                     'string_2' => array(
                          'ignored_functions' => array(),
                          'ignored_extensions' => array(),
                          'ignored_constants' => array(),
                          'max_version' => '',
                          'version' => '5.1.0',
                          'classes' => array(),
                          'functions' => array('array_diff_key'),
                          'extensions' => array(),
                          'constants' => array(),
                          'tokens' => array(),
                          'cond_code' => array(0))
                     );
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing nothing (all files are excluded from scope)
     *
     * @return void
     * @covers PHP_CompatInfo::parseArray
     * @group  parseArray
     * @group  standard
     */
    public function testParseArrayWithNoFiles()
    {
        $files = get_included_files();
        $opt   = array('ignore_files' => $files);

        $r = $this->pci->parseArray($files, $opt);
        $this->assertFalse($r);
    }

    /**
     * Tests loading functions list for a PHP version
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersion()
    {
        $resources   = array('date', 'filter', 'gd', 'gmp', 'mbstring', 'ming',
                             'mysql', 'openssl', 'pgsql',
                             'posix', 'snmp', 'spl');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $r   = $this->pci->loadVersion('5.2.0');
        $exp = array('array_fill_keys',
                     'error_get_last',
                     'filter_has_var',
                     'filter_id',
                     'filter_input',
                     'filter_input_array',
                     'filter_list',
                     'filter_var',
                     'filter_var_array',
                     'gmp_nextprime',
                     'imagegrabscreen',
                     'imagegrabwindow',
                     'iterator_apply',
                     'mb_list_encodings_alias_names',
                     'mb_list_mime_names',
                     'mb_stripos',
                     'mb_stristr',
                     'mb_strrchr',
                     'mb_strrichr',
                     'mb_strripos',
                     'mb_strstr',
                     'memory_get_peak_usage',
                     'ming_setswfcompression',
                     'mysql_set_charset',
                     'openssl_csr_get_public_key',
                     'openssl_csr_get_subject',
                     'openssl_pkcs12_export',
                     'openssl_pkcs12_export_to_file',
                     'openssl_pkcs12_read',
                     'openssl_pkey_get_details',
                     'pg_field_table',
                     'php_ini_loaded_file',
                     'posix_initgroups',
                     'preg_last_error',
                     'snmp_set_oid_output_format',
                     'spl_object_hash',
                     'stream_is_local',
                     'stream_socket_shutdown',
                     'sys_get_temp_dir',
                     'timezone_transitions_get');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests loading functions list for a PHP version range
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersionRange()
    {
        $resources   = array('date', 'filter', 'gd', 'gmp', 'mbstring', 'ming',
                             'openssl', 'pgsql',
                             'posix', 'snmp', 'spl');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $r   = $this->pci->loadVersion('5.2.0', '5.2.2');
        $exp = array('array_fill_keys',
                     'error_get_last',
                     'filter_has_var',
                     'filter_id',
                     'filter_input',
                     'filter_input_array',
                     'filter_list',
                     'filter_var',
                     'filter_var_array',
                     'gmp_nextprime',
                     'imagegrabscreen',
                     'imagegrabwindow',
                     'iterator_apply',
                     'mb_list_encodings_alias_names',
                     'mb_list_mime_names',
                     'mb_stripos',
                     'mb_stristr',
                     'mb_strrchr',
                     'mb_strrichr',
                     'mb_strripos',
                     'mb_strstr',
                     'memory_get_peak_usage',
                     'ming_setswfcompression',
                     'openssl_csr_get_public_key',
                     'openssl_csr_get_subject',
                     'openssl_pkcs12_export',
                     'openssl_pkcs12_export_to_file',
                     'openssl_pkcs12_read',
                     'openssl_pkey_get_details',
                     'pg_field_table',
                     'posix_initgroups',
                     'preg_last_error',
                     'snmp_set_oid_output_format',
                     'spl_object_hash',
                     'stream_socket_shutdown',
                     'sys_get_temp_dir',
                     'timezone_transitions_get');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests loading function list for a range of PHP version,
     * group by version number
     *
     * What's new with versions 4.3.2 to 4.4.0 : 13 + 30 + 5 functions
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersionRangeGroupByVersion()
    {
        $resources   = array('bz2', 'gd', 'imap', 'oci8', 'snmp', 'zlib');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $r   = $this->pci->loadVersion('4.3.2', '4.4.0', false, true);
        $exp = array('4.3.2' => array('apache_get_modules',
                                      'apache_get_version',
                                      'domxml_attr_set_value',
                                      'domxml_doc_free_doc',
                                      'imageantialias',
                                      'imagecolorallocatealpha',
                                      'imageistruecolor',
                                      'imagesavealpha',
                                      'memory_get_usage',
                                      'ocipasswordchange',
                                      'session_regenerate_id',
                                      'stream_wrapper_register',
                                      'zlib_get_coding_type'),
                     '4.3.3' => array('bzclose',
                                      'bzcompress',
                                      'bzdecompress',
                                      'bzerrno',
                                      'bzerror',
                                      'bzerrstr',
                                      'bzflush',
                                      'bzopen',
                                      'bzread',
                                      'bzwrite',
                                      'imap_timeout',
                                      'nsapi_request_headers',
                                      'nsapi_response_headers',
                                      'nsapi_virtual',
                                      'snmp_get_valueretrieval',
                                      'snmp_set_valueretrieval',
                                      'solid_fetch_prev',
                                      'swfmovie_add',
                                      'swfmovie_init',
                                      'swfmovie_labelframe',
                                      'swfmovie_nextframe',
                                      'swfmovie_output',
                                      'swfmovie_save',
                                      'swfmovie_savetofile',
                                      'swfmovie_setbackground',
                                      'swfmovie_setdimension',
                                      'swfmovie_setframes',
                                      'swfmovie_setrate',
                                      'swfmovie_streammp3',
                                      'swfsprite_labelframe'),
                     '4.4.0' => array('session_commit',
                                      'snmp2_get',
                                      'snmp2_real_walk',
                                      'snmp2_set',
                                      'snmp2_walk'));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests loading function+constant list for a single PHP version
     *
     * What's new with version 4.3.10 : 0 functions and 2 new constants
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersionRangeWithConstant()
    {
        $r   = $this->pci->loadVersion('4.3.10', '4.3.10', true);
        $exp = array('functions' => array(),
                     'constants' => array('PHP_EOL',
                                          'UPLOAD_ERR_NO_TMP_DIR'));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests loading function list for a range of PHP version,
     * group by version number
     *
     * What's new with versions 4.3.2 to 4.4.0 : 13 + 30 + 5 functions
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersionRangeWithConstantGroupByVersion()
    {
        $resources   = array('bz2', 'gd', 'imap', 'oci8', 'snmp', 'zlib');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $r   = $this->pci->loadVersion('4.3.2', '4.4.0', true, true);
        $exp = array('functions' => array(
                     '4.3.2' => array('apache_get_modules',
                                      'apache_get_version',
                                      'domxml_attr_set_value',
                                      'domxml_doc_free_doc',
                                      'imageantialias',
                                      'imagecolorallocatealpha',
                                      'imageistruecolor',
                                      'imagesavealpha',
                                      'memory_get_usage',
                                      'ocipasswordchange',
                                      'session_regenerate_id',
                                      'stream_wrapper_register',
                                      'zlib_get_coding_type'),
                     '4.3.3' => array('bzclose',
                                      'bzcompress',
                                      'bzdecompress',
                                      'bzerrno',
                                      'bzerror',
                                      'bzerrstr',
                                      'bzflush',
                                      'bzopen',
                                      'bzread',
                                      'bzwrite',
                                      'imap_timeout',
                                      'nsapi_request_headers',
                                      'nsapi_response_headers',
                                      'nsapi_virtual',
                                      'snmp_get_valueretrieval',
                                      'snmp_set_valueretrieval',
                                      'solid_fetch_prev',
                                      'swfmovie_add',
                                      'swfmovie_init',
                                      'swfmovie_labelframe',
                                      'swfmovie_nextframe',
                                      'swfmovie_output',
                                      'swfmovie_save',
                                      'swfmovie_savetofile',
                                      'swfmovie_setbackground',
                                      'swfmovie_setdimension',
                                      'swfmovie_setframes',
                                      'swfmovie_setrate',
                                      'swfmovie_streammp3',
                                      'swfsprite_labelframe'),
                     '4.4.0' => array('session_commit',
                                      'snmp2_get',
                                      'snmp2_real_walk',
                                      'snmp2_set',
                                      'snmp2_walk')),
                     'constants' => array(
                     '4.3.10' => array('PHP_EOL',
                                       'UPLOAD_ERR_NO_TMP_DIR')
                                       ));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests loading function+constant list for a PHP version range
     *
     * What's new with since version 5.2.1 : 24 new functions and 0 constant
     *
     * @return void
     * @covers PHP_CompatInfo::loadVersion
     * @group  loadVersion
     * @group  standard
     */
    public function testLoadVersionWithConstant()
    {
        $resources   = array('gd', 'ming', 'openssl');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $r   = $this->pci->loadVersion('5.2.1', false, true);
        $exp = array('functions' => array('imagegrabscreen',
                                          'imagegrabwindow',
                                          'ming_setswfcompression',
                                          'mysql_set_charset',
                                          'openssl_pkcs12_export',
                                          'openssl_pkcs12_export_to_file',
                                          'openssl_pkcs12_read',
                                          'php_ini_loaded_file',
                                          'stream_is_local',
                                          'stream_socket_shutdown',
                                          'sys_get_temp_dir'),
                     'constants' => array('PCRE_VERSION',
                                          'PREG_BAD_UTF8_OFFSET_ERROR'));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests the PHP Method chaining feature introduced with PHP5
     * Sample #1
     *
     * @link http://cowburn.info/php/php5-method-chaining/
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testPHP5MethodChainingSamp1()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds
             . 'php5_method_chaining.php';
        $r   = $this->pci->parseFile($fn);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array('Person'),
                     'functions' => array('printf'),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests the PHP Method chaining feature introduced with PHP5.
     * Sample #2
     *
     * @return void
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testPHP5MethodChainingSamp2()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds
             . 'php5_method_chaining_samp2.php';
        $r   = $this->pci->parseFile($fn);
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array(),
                     'functions' => array(),
                     'extensions' => array(),
                     'constants' => array(),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_functions_match' option
     * Sample #1
     *
     * Exclude all functions that are referenced by php function_exists(),
     * and match regular expressions :
     * - word file_put_contents (case insensitive)
     * - beginning with debug (case sensitive)
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreFunctionsMatchSamp1()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds
             . 'ignore_functions_match.php';
        $opt = array('ignore_functions_match' =>
                   array('function_exists', array('/^File_put_contents$/i',
                                                  '/^debug/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('debug_backtrace', 'file_put_contents'),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array(),
                     'functions' => array('debug_backtrace',
                                          'debug_print_backtrace',
                                          'fclose',
                                          'file_put_contents',
                                          'fopen',
                                          'fwrite'),
                     'extensions' => array(),
                     'constants' => array('FALSE'),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_functions_match' option
     * Sample #2
     *
     * Exclude (from scope) all functions that are 'debug' prefixed (case sensitive).
     *
     * If you want a case-insensitive search add an "i" after the pattern delimiter,
     * like that:  '/^debug/i'
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreFunctionsMatchSamp2()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'ignore_functions_match.php';
        $opt = array('ignore_functions_match' => array('preg_match', array('/^debug/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('debug_backtrace', 'debug_print_backtrace'),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array(),
                     'functions' => array('debug_backtrace',
                                          'debug_print_backtrace',
                                          'fclose',
                                          'file_put_contents',
                                          'fopen',
                                          'function_exists',
                                          'fwrite'),
                     'extensions' => array(),
                     'constants' => array('FALSE'),
                     'tokens' => array(),
                     'cond_code' => array(1));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_functions_match' option
     * Sample #3
     *
     * When I don't know the script to parse (most case), I would really
     * exclude from scope all functions that are conditionned by function_exists().
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreFunctionsMatchSamp3()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'ignore_functions_match.php';
        $opt = array('ignore_functions_match' => array('function_exists', array('/.*/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('debug_backtrace', 'file_put_contents'),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array(),
                     'functions' => array('debug_backtrace',
                                          'debug_print_backtrace',
                                          'fclose',
                                          'file_put_contents',
                                          'fopen',
                                          'fwrite'),
                     'extensions' => array(),
                     'constants' => array('FALSE'),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_extensions_match' option
     * Sample #1
     *
     * Exclude all extensions (and their functions) that are referenced by
     * php extension_loaded(), and match regular expressions
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreExtensionsMatchSamp1()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseDir' . $ds . 'extensions.php';
        $opt = array('ignore_extensions_match' =>
                   array('extension_loaded', array('/^SQLite$/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('sqlite_libversion'),
                     'ignored_extensions' => array('SQLite'),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.2',
                     'classes' => array(),
                     'functions' => array('apache_get_modules',
                                          'dl',
                                          'imageantialias',
                                          'imagecreate',
                                          'print_r',
                                          'sqlite_libversion',
                                          'xdebug_start_trace',
                                          'xdebug_stop_trace'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(0));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_extensions_match' option
     * Sample #2
     *
     * Exclude all extensions (and their functions) that are referenced freely by
     * preg_match clause, and match regular expression:
     * - beginning with 'xdebug' (case sensitive)
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreExtensionsMatchSamp2()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseDir' . $ds . 'extensions.php';
        $opt = array('ignore_extensions_match' =>
                   array('preg_match', array('/^xdebug/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array('xdebug_start_trace',
                                                  'xdebug_stop_trace'),
                     'ignored_extensions' => array('xdebug'),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '4.3.2',
                     'classes' => array(),
                     'functions' => array('apache_get_modules',
                                          'dl',
                                          'extension_loaded',
                                          'imageantialias',
                                          'imagecreate',
                                          'print_r',
                                          'sqlite_libversion',
                                          'xdebug_start_trace',
                                          'xdebug_stop_trace'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE'),
                     'tokens' => array(),
                     'cond_code' => array(2));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_constants_match' option
     * Sample #1
     *
     * Exclude all constants that are referenced by php defined(),
     * and match regular expressions
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreConstantsMatchSamp1()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_constants_match' =>
                   array('defined', array('/^directory/i')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array('DIRECTORY_SEPARATOR'),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array(),
                     'functions' => array('basename',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'dirname',
                                          'function_exists',
                                          'phpversion',
                                          'simplexml_load_file',
                                          'strtoupper',
                                          'substr',
                                          'version_compare'),
                     'extensions' => array( 'date', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(1));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file with 'ignore_constants_match' option
     * Sample #2
     *
     * Exclude all constants that freely match regular expressions
     *
     * @return void
     * @since  version 1.7.0
     * @covers PHP_CompatInfo::parseFile
     * @group  parseFile
     * @group  standard
     */
    public function testParseFileWithIgnoreConstantsMatchSamp2()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_constants_match' =>
                   array('preg_match', array('/^dir/i', '/^DATE_W3C$/')));

        $r = $this->pci->parseFile($fn, $opt);
        $this->assertType('array', $r);

        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array('DATE_W3C',
                                                  'DIRECTORY_SEPARATOR'),
                     'max_version' => '',
                     'version' => '5.0.0',
                     'classes' => array(),
                     'functions' => array('basename',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'defined',
                                          'dirname',
                                          'function_exists',
                                          'phpversion',
                                          'simplexml_load_file',
                                          'strtoupper',
                                          'substr',
                                          'version_compare'),
                     'extensions' => array('date', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple strings (chunk of code)
     * and return minimum PHP version required
     *
     * @return void
     * @covers PHP_CompatInfo::getVersion
     * @group  getVersion
     * @group  standard
     */
    public function testGetVersion()
    {
        $code1 = "<?php
php_check_syntax('somefile.php');
?>";
        $code2 = "<?php
\$array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
\$array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8);

\$diff = array_diff_key(\$array1, \$array2);
?>";
        $data  = array($code1, $code2);
        $opt   = array('is_string' => true);

        $this->pci->parseArray($data, $opt);
        $r   = $this->pci->getVersion();
        $exp = '5.1.0';
        $this->assertEquals($exp, $r);
    }

    /**
     * Tests parsing multiple strings (chunk of code)
     * and return maximum PHP version
     *
     * @return void
     * @covers PHP_CompatInfo::getVersion
     * @group  getVersion
     * @group  standard
     */
    public function testGetVersionWithMax()
    {
        $code1 = "<?php
php_check_syntax('somefile.php');
?>";
        $code2 = "<?php
\$array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
\$array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8);

\$diff = array_diff_key(\$array1, \$array2);
?>";
        $data  = array($code1, $code2);
        $opt   = array('is_string' => true);

        $this->pci->parseArray($data, $opt);
        $r   = $this->pci->getVersion(false, true);
        $exp = '5.0.4';
        $this->assertEquals($exp, $r);
    }

    /**
     * Tests parsing a file and get classes declared (end-user defined)
     *
     * @return void
     * @covers PHP_CompatInfo::getClasses
     * @group  getClasses
     * @group  standard
     */
    public function testGetClasses()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds
             . 'php5_method_chaining.php';
        $this->pci->parseFile($fn);

        $r   = $this->pci->getClasses();
        $exp = array('Person');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get classes declared (internal)
     *
     * @return void
     * @covers PHP_CompatInfo::getClasses
     * @group  getClasses
     * @group  standard
     */
    public function testGetClassesInternal()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'PHP5' . $ds . 'tokens.php5';
        $r   = $this->pci->getClasses($f);
        $exp = array('Exception');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a file and get functions declared
     * (internal and end-user defined)
     *
     * @return void
     * @covers PHP_CompatInfo::getFunctions
     * @group  getFunctions
     * @group  standard
     */
    public function testGetFunctions()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseFile';
        $src = $dir . $ds . 'File_Find-1.3.0__Find.php';
        $this->pci->parseFile($src);

        $r   = $this->pci->getFunctions();
        $exp = array('_file_find_match_shell_get_pattern',
                     'addcslashes',
                     'array_merge',
                     'array_pop',
                     'array_push',
                     'basename',
                     'closedir',
                     'count',
                     'define',
                     'defined',
                     'each',
                     'explode',
                     'glob',
                     'implode',
                     'is_a',
                     'is_array',
                     'is_dir',
                     'is_readable',
                     'maptree',
                     'maptreemultiple',
                     'opendir',
                     'preg_match',
                     'preg_replace',
                     'preg_split',
                     'print_r',
                     'readdir',
                     'reset',
                     'search',
                     'sizeof',
                     'str_replace',
                     'strcasecmp',
                     'strlen',
                     'strpos',
                     'substr',
                     'substr_count');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get functions declared
     * (internal and end-user defined) for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getFunctions
     * @group  getFunctions
     * @group  standard
     */
    public function testGetFunctionsByFile()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'extensions.php';
        $r   = $this->pci->getFunctions($f);
        $exp = array('apache_get_modules',
                     'dl',
                     'extension_loaded',
                     'imageantialias',
                     'imagecreate',
                     'print_r',
                     'sqlite_libversion',
                     'xdebug_start_trace',
                     'xdebug_stop_trace');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a file and get constants declared
     * (internal and end-user defined)
     *
     * @return void
     * @covers PHP_CompatInfo::getConstants
     * @group  getConstants
     * @group  standard
     */
    public function testGetConstants()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $src = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $this->pci->parseFile($src);

        $r   = $this->pci->getConstants();
        $exp = array('DATE_W3C',
                     'DIRECTORY_SEPARATOR',
                     'FALSE',
                     'PHP_EOL',
                     'PHP_OS',
                     '__FILE__');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get constants declared
     * (internal and end-user defined) for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getConstants
     * @group  getConstants
     * @group  standard
     */
    public function testGetConstantsByFile()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'PHP5' . $ds . 'upload_error.php';
        $r   = $this->pci->getConstants($f);
        $exp = array('UPLOAD_ERR_CANT_WRITE',
                     'UPLOAD_ERR_EXTENSION',
                     'UPLOAD_ERR_FORM_SIZE',
                     'UPLOAD_ERR_INI_SIZE',
                     'UPLOAD_ERR_NO_FILE',
                     'UPLOAD_ERR_NO_TMP_DIR',
                     'UPLOAD_ERR_OK',
                     'UPLOAD_ERR_PARTIAL');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a file and get tokens declared
     *
     * @return void
     * @covers PHP_CompatInfo::getTokens
     * @group  getTokens
     * @group  standard
     */
    public function testGetTokens()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $src = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $this->pci->parseFile($src);

        $r   = $this->pci->getTokens();
        $exp = array();
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get tokens declared
     * for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getTokens
     * @group  getTokens
     * @group  standard
     */
    public function testGetTokensByFile()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'PHP5' . $ds . 'tokens.php5';
        $r   = $this->pci->getTokens($f);
        $exp = array('abstract',
                     'catch',
                     'clone',
                     'final',
                     'implements',
                     'instanceof',
                     'interface',
                     'private',
                     'protected',
                     'public',
                     'throw',
                     'try');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and retrieve the Code Condition level
     *
     * @return void
     * @covers PHP_CompatInfo::getConditions
     * @group  getConditions
     * @group  standard
     */
    public function testGetConditionsWithoutContext()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('debug' => true,
                     'recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));

        $this->pci->parseDir($dir, $opt);
        $r   = $this->pci->getConditions(false, true);
        $exp = 2; // extension condition code level
        $this->assertEquals($exp, $r);
    }

    /**
     * Tests parsing a directory and retrieve the Code Condition
     * contextual data
     *
     * @return void
     * @covers PHP_CompatInfo::getConditions
     * @group  getConditions
     * @group  standard
     */
    public function testGetConditionsWithContext()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('debug' => true,
                     'recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));

        $this->pci->parseDir($dir, $opt);
        $r   = $this->pci->getConditions();
        $exp = array(2, array(array(), array('SQLite'), array()));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and retrieve the Code Condition
     * of a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getConditions
     * @group  getConditions
     * @group  standard
     */
    public function testGetConditionsByFile()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('debug' => false,
                     'recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));

        $this->pci->parseDir($dir, $opt);
        $f   = $dir . 'phpinfo.php';
        $r   = $this->pci->getConditions($f);
        $exp = array(0); // no condition code
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a file and get extensions used
     *
     * @return void
     * @covers PHP_CompatInfo::getExtensions
     * @group  getExtensions
     * @group  standard
     */
    public function testGetExtensions()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds = DIRECTORY_SEPARATOR;
        $fn = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $this->pci->parseFile($fn);

        $r   = $this->pci->getExtensions();
        $exp = array('date', 'SimpleXML');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get extensions used
     * for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getExtensions
     * @group  getExtensions
     * @group  standard
     */
    public function testGetExtensionsByFile()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'extensions.php';
        $r   = $this->pci->getExtensions($f);
        $exp = array('gd',
                     'SQLite',
                     'xdebug');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file and get list of ignored functions
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredFunctions
     * @group  getIgnoredFunctions
     * @group  standard
     */
    public function testGetIgnoredFunctions()
    {
        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'ignore_functions_match.php';
        $opt = array('ignore_functions_match' => array('preg_match', array('/^debug/')));
        $this->pci->parseFile($fn, $opt);

        $r   = $this->pci->getIgnoredFunctions();
        $exp = array('debug_backtrace', 'debug_print_backtrace');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get list of ignored functions
     * for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredFunctions
     * @group  getIgnoredFunctions
     * @group  standard
     */
    public function testGetIgnoredFunctionsByFile()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('ignore_functions_match' => array('preg_match', array('/debug/')));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'extensions.php';
        $r   = $this->pci->getIgnoredFunctions($f);
        $exp = array('xdebug_start_trace', 'xdebug_stop_trace');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file and get list of ignored extensions
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredExtensions
     * @group  getIgnoredExtensions
     * @group  standard
     */
    public function testGetIgnoredExtensions()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseDir' . $ds . 'extensions.php';
        $opt = array('ignore_extensions_match' => array('preg_match', array('/SQL/')));
        $this->pci->parseFile($fn, $opt);

        $r   = $this->pci->getIgnoredExtensions();
        $exp = array('SQLite');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get list of ignored extensions
     * for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredExtensions
     * @group  getIgnoredExtensions
     * @group  standard
     */
    public function testGetIgnoredExtensionsByFile()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('ignore_extensions' => array('gd'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'extensions.php';
        $r   = $this->pci->getIgnoredExtensions($f);
        $exp = array('gd');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a single file and get list of ignored constants
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredConstants
     * @group  getIgnoredConstants
     * @group  standard
     */
    public function testGetIgnoredConstants()
    {
        $resources   = array('date', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $fn  = dirname(__FILE__) . $ds . 'parseFile' . $ds . 'conditional.php';
        $opt = array('ignore_constants' => array('PHP_EOL', '__LINE__'));
        $this->pci->parseFile($fn, $opt);

        $r   = $this->pci->getIgnoredConstants();
        $exp = array('PHP_EOL');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing a directory and get list of ignored constants
     * for a specific file
     *
     * @return void
     * @covers PHP_CompatInfo::getIgnoredConstants
     * @group  getIgnoredConstants
     * @group  standard
     */
    public function testGetIgnoredConstantsByFile()
    {
        $resources   = array('bcmath', 'date', 'pcre', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseFile' . $ds;
        $opt = array('ignore_constants' => array('PHP_EOL', '__LINE__'));
        $this->pci->parseDir($dir, $opt);

        $f   = $dir . 'conditional.php';
        $r   = $this->pci->getIgnoredConstants($f);
        $exp = array('PHP_EOL');
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple data sources and get only the summary result
     * without the function list returns only by debug mode
     *
     * @return void
     * @covers PHP_CompatInfo::getSummary
     * @group  getSummary
     * @group  standard
     */
    public function testGetSummaryWithoutFunctionsForArrayFile()
    {
        $resources   = array('date', 'pcre', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseFile';
        $fn1 = $dir . $ds . 'File_Find-1.3.0__Find.php';
        $fn2 = $dir . $ds . 'conditional.php';
        $opt = array('debug' => false);
        $this->pci->parseData(array($fn1, $fn2), $opt);

        $r   = $this->pci->getSummary();
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array('File_Find'),
                     'extensions' => array('date', 'pcre', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'NULL',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          'PREG_SPLIT_DELIM_CAPTURE',
                                          'PREG_SPLIT_NO_EMPTY',
                                          'TRUE',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5));
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing multiple data sources and get only the summary result
     * including the function list returns by debug mode
     *
     * @return void
     * @covers PHP_CompatInfo::getSummary
     * @group  getSummary
     * @group  standard
     */
    public function testGetSummaryWithFunctionsForArrayFile()
    {
        $resources   = array('date', 'pcre', 'SimpleXML');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseFile';
        $fn1 = $dir . $ds . 'File_Find-1.3.0__Find.php';
        $fn2 = $dir . $ds . 'conditional.php';
        $opt = array('debug' => true);
        $this->pci->parseData(array($fn1, $fn2), $opt);

        $r   = $this->pci->getSummary();
        $exp = array('ignored_files' => array(),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.1.1',
                     'classes' => array('File_Find'),
                     'functions' => array('_file_find_match_shell_get_pattern',
                                          'addcslashes',
                                          'array_merge',
                                          'array_pop',
                                          'array_push',
                                          'basename',
                                          'closedir',
                                          'count',
                                          'date',
                                          'debug_backtrace',
                                          'define',
                                          'defined',
                                          'dirname',
                                          'each',
                                          'explode',
                                          'function_exists',
                                          'glob',
                                          'implode',
                                          'is_a',
                                          'is_array',
                                          'is_dir',
                                          'is_readable',
                                          'maptree',
                                          'maptreemultiple',
                                          'opendir',
                                          'phpversion',
                                          'preg_match',
                                          'preg_replace',
                                          'preg_split',
                                          'print_r',
                                          'readdir',
                                          'reset',
                                          'search',
                                          'simplexml_load_file',
                                          'sizeof',
                                          'str_replace',
                                          'strcasecmp',
                                          'strlen',
                                          'strpos',
                                          'strtoupper',
                                          'substr',
                                          'substr_count',
                                          'version_compare'),
                     'extensions' => array('date', 'pcre', 'SimpleXML'),
                     'constants' => array('DATE_W3C',
                                          'DIRECTORY_SEPARATOR',
                                          'FALSE',
                                          'NULL',
                                          'PHP_EOL',
                                          'PHP_OS',
                                          'PREG_SPLIT_DELIM_CAPTURE',
                                          'PREG_SPLIT_NO_EMPTY',
                                          'TRUE',
                                          '__FILE__'),
                     'tokens' => array(),
                     'cond_code' => array(5,
                                          array(
                                            array('debug_backtrace',
                                                  'simplexml_load_file'),
                                            array(),
                                            array('DIRECTORY_SEPARATOR',
                                                  'FILE_FIND_DEBUG'),
                                          ))
                     );
        $this->assertSame($exp, $r);
    }

    /**
     * Tests parsing directory and get only the summary result
     * without the function list returns only by debug mode
     *
     * @return void
     * @covers PHP_CompatInfo::getSummary
     * @group  getSummary
     * @group  standard
     */
    public function testGetSummaryForDirectory()
    {
        $resources   = array('gd', 'SQLite', 'xdebug');
        $testSkipped = array();
        if (!$this->isResourceAvailable($resources, $testSkipped)) {
            foreach ($testSkipped as $reason) {
                $this->markTestSkipped($reason);
            }
        }

        $ds  = DIRECTORY_SEPARATOR;
        $dir = dirname(__FILE__) . $ds . 'parseDir' . $ds;
        $opt = array('recurse_dir' => true,
                     'file_ext' => array('php', 'php5'));
        $this->pci->parseData($dir, $opt);

        $r   = $this->pci->getSummary();
        $exp = array('ignored_files' => $this->getIgnoredFileList($dir, $opt),
                     'ignored_functions' => array(),
                     'ignored_extensions' => array(),
                     'ignored_constants' => array(),
                     'max_version' => '',
                     'version' => '5.2.0',
                     'classes' => array('Exception'),
                     'extensions' => array('gd',
                                           'SQLite',
                                           'xdebug'),
                     'constants' => array('PHP_SHLIB_SUFFIX',
                                          'TRUE',
                                          'UPLOAD_ERR_CANT_WRITE',
                                          'UPLOAD_ERR_EXTENSION',
                                          'UPLOAD_ERR_FORM_SIZE',
                                          'UPLOAD_ERR_INI_SIZE',
                                          'UPLOAD_ERR_NO_FILE',
                                          'UPLOAD_ERR_NO_TMP_DIR',
                                          'UPLOAD_ERR_OK',
                                          'UPLOAD_ERR_PARTIAL'),
                     'tokens' => array('abstract',
                                       'catch',
                                       'clone',
                                       'final',
                                       'implements',
                                       'instanceof',
                                       'interface',
                                       'private',
                                       'protected',
                                       'public',
                                       'throw',
                                       'try'),
                     'cond_code' => array(2));
        $this->assertSame($exp, $r);
    }
}

// Call PHP_CompatInfo_TestSuite_Standard::main() if file is executed directly.
if (PHPUnit_MAIN_METHOD == "PHP_CompatInfo_TestSuite_Standard::main") {
    PHP_CompatInfo_TestSuite_Standard::main();
}
?>