<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP_Debug : A simple and fast way to debug your PHP code
 * 
 * The basic purpose of PHP_Debug is to provide assistance in debugging PHP
 * code, by 'debug' i don't mean 'step by step debug' but program trace,
 * variables display, process time, included files, queries executed, watch
 * variables... These informations are gathered through the script execution and
 * therefore are displayed at the end of the script (in a nice floating div or a
 * html table) so that it can be read and used at any moment. (especially
 * usefull during the development phase of a project or in production with a
 * secure key/ip)
 *
 * PHP version 5 only
 *
 * Copyright (c) 2007 - Vernet Loïc

 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * @category   PHP
 * @package    PHP_Debug
 * @author     Vernet Loïc <qrf_coil[at]yahoo.fr>
 * @copyright  1997-2007 The PHP Group
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://pear.php.net/package/PHP_Debug
 * @link       http://phpdebug.sourceforge.net
 * @link       http://www.php-debug.com
 * @see 	     Text_Highlighter 
 * @see        Var_Dump, SQL_Parser
 * @since      1.0.0RC1
 * @version    CVS: $Id: Debug.php,v 1.6 2009/01/12 21:13:00 c0il Exp $
 */

/**
 * Factory class for renderer of Debug class
 * 
 * @see Debug/Renderer/*.php
 */
require_once 'PHP/DebugLine.php';
require_once 'PHP/Debug/Renderer.php';

/**
 * External constants
 * 
 * @filesource
 * @package PHP_Debug
 */
if (!defined('CR')) { 
    define('CR', "\n");
}

class PHP_Debug
{

    /**
     * Possible version of class Debug
     */ 
    const VERSION_STANDALONE = 0;
    const VERSION_PEAR       = 1;
    const VERSION_DEFAULT    = self::VERSION_STANDALONE;
    const VERSION            = self::VERSION_STANDALONE;
    const RELEASE            = 'V2.1.5';
    const PEAR_RELEASE       = 'V1.0.3';

    /**
     * These are constant for dump() and DumpObj() functions.
     * 
     * - DUMP_DISP : Tell the function to display the debug info.
     * - DUMP_STR  : Tell the fonction to return the debug info as a string 
     * - DUMP_VARNAME : Default name of Array - DBG_ARR_OBJNAME : Default name
     * of Object
     */
    const DUMP_DISP = 1;
    const DUMP_STR  = 2;
    const DUMP_VARNAME = 'Variable';

    /**
     * These are constant for addDebug functions, they set the behaviour where
     * the function should add the debug information in first or in last
     * position
     */
    const POSITIONLAST =  0;
    const POSITIONFIRST = 1;

    /**
     * These are constants to define Super array environment variables
     */ 
    const GLOBAL_GET     = 0;
    const GLOBAL_POST    = 1;
    const GLOBAL_FILES   = 2;
    const GLOBAL_COOKIE  = 3;
    const GLOBAL_REQUEST = 4;
    const GLOBAL_SESSION = 5;
    const GLOBAL_GLOBALS = 6;

    /**
     * Default configuration options
     * 
     * @since V2.0.0 - 16 apr 2006
     * @see setOptions()
     * @var array
     */
    protected $defaultOptions = array(
        'render_mode'          => 'Div',              // Renderer mode
        'render_type'          => 'HTML',             // Renderer type
        'restrict_access'      => false,              // Restrict or not the access
        'allowed_ip'           => array('127.0.0.1'), // Authorized IP to view the debug when restrict_access is true
        'allow_url_access'     => false,              // Allow to access the debug with a special parameter in the url
        'url_key'              => 'debug',            // Key for url instant access
        'url_pass'             => 'true',             // Password for url instant access
        'enable_watch'         => false,              // Enable the watch function
        'replace_errorhandler' => true,               // Replace or no the PHP errorhandler
        'lang'                 => 'EN',               // Language
    );

    /**
     * Default static options for static functions
     *
     * @since V2.0.0 - 16 apr 2006
     * @see dump()
     * @var array
     */
    protected static $staticOptions = array(
        'dump_method'          => 'print_r',          // print_r or var_dump
        'pear_var_dump_method' => 'Var_Dump::display' // Var_Dump display funtion (not used for now)
    );

    /**
     * Functions from this class that must be excluded in order to have the
     * correct backtrace information
     *
     * @see PHP_DebugLine::setTraceback()
     * @since V2.0.0 - 13 apr 2006
     * @var array
     */
    public static $excludedBackTraceFunctions = array(
        'add', 
        'dump',
        'error', 
        'query', 
        'addDebug', 
        'setAction', 
        'addDebugFirst',
        'watchesCallback',
        'errorHandlerCallback'
    );

    /**
     * Correspondance between super array constant and variable name
     * Used by renderers
     *
     * @since V2.0.0 - 18 apr 2006
     * @var array
     */
    public static $globalEnvConstantsCorresp = array(  
        self::GLOBAL_GET    => '_GET',
        self::GLOBAL_POST   => '_POST',
        self::GLOBAL_FILES  => '_FILES',
        self::GLOBAL_COOKIE => '_COOKIE',
        self::GLOBAL_REQUEST=> '_REQUEST',
        self::GLOBAL_SESSION=> '_SESSION',
        self::GLOBAL_GLOBALS=> 'GLOBALS'
    );

    /**
     * Default configuration options
     *
     * @since V2.0.0 - 13 apr 2006
     * @see setOptions() 
     * @var array
     */
    protected $options = array();

    /**
     * This is the array where the debug lines are collected.
     *
     * @since V2.0.0 - 11 apr 2006
     * @see DebugLine
     * @var array
     */
    protected  $debugLineBuffer = array();
    
    /**
     * This is the array containing all the required/included files of the 
     * script
     *
     * @since V2.0.0 - 17 apr 2006
     * @see render(), PHP_DebugLine::TYPE_TEMPLATES
     * @var array
     */    
    protected $requiredFiles = array();

    /**
     * This is the array containing all the watched variables
     *
     * @since V2.0.0 - 16 apr 2006
     * @see watch()
     * @var array
     */    
    protected $watches = array();
    
    /** 
     * Execution start time
     * 
     * @since V2.0.0 - 11 apr 2006
     * @see __construct()
     * @var float          
     */
    protected $startTime;
        
    /** 
     * Exection end time
     * 
     * @since V2.0.0 - 11 apr 2006
     * @see render()
     * @var float
     */
    protected $endTime;
    
    /** 
     * Number of queries executed during script 
     * 
     * @since V2.0.0 - 19 apr 2006
     * @var integer          
     */
    protected $queryCount = 0;

    /**
     * PHP_Debug class constructor
     * 
     * Here we set :
     * - the execution start time
     * - the options
     * - the error and watch call back functions
     * 
     * @param array $options    Array containing options to affect to Debug 
     *                          object and his childs
     * 
     * @since V2.0.0 - 11 apr 2006
     */
    function __construct($options = array())
    {
        $this->startTime = PHP_Debug::getMicroTimeNow();
        $this->options = array_merge($this->defaultOptions, $options);
        $this->setWatchCallback();
        $this->setErrorHandler();
    }

    /**
     * Add a debug information
     *
     * @param string  $info  The main debug information 
     *                      (may be empty for some debug line types)
     * @param integer $type Type of the DebugLine
     * 
     * @see Debug constants
     * @since V1.0.0 - 07 Apr 2006
     */     
    public function addDebug($info, $type = PHP_DebugLine::TYPE_STD, 
        $position = self::POSITIONLAST)
    {    	
        // Add info
        $debugLine = new PHP_DebugLine($info, $type);
        if ($position == self::POSITIONLAST) {        
            $this->debugLineBuffer[] = $debugLine;
        } else {
            array_unshift($this->debugLineBuffer, $debugLine);
        }
        
        // Additional process for some types
        switch ($type) {
			case PHP_DebugLine::TYPE_QUERY:
                $this->queryCount++;
				break;
		
			default:
				break;
		}

        // Return debugline
        return $debugLine;
    }

    /**
     * Add a debug info before all the existing other debug lines
     * It is an alias for addDebug($info, self::POSITIONLAST)
     * 
     * @see addDebug
     * @since V1.0.0 - 13 Apr 2006 
     */
    public function addDebugFirst($info, $type = PHP_DebugLine::TYPE_STD)
    {
        return $this->addDebug($info, $type, self::POSITIONFIRST);
    }

    /**
     * This is an alias for the addDebug function
     *
     * @see addDebug()
     * @since  V2.0.0 - 20 apr 2006
     */
    public function add($info, $type = PHP_DebugLine::TYPE_STD)
    {
        return $this->addDebug($info, $type);
    }

    /**
     * This is an alias for the addDebug function when wanting to add a query
     * debug information
     * 
     * @see addDebug(), PHP_DebugLine::TYPE_QUERY
     * @since V2.0.0 - 21 Apr 2006
     */
    public function query($qry)
    {
        return $this->addDebug($qry, PHP_DebugLine::TYPE_QUERY);
    }

    /**
     * This is an alias for the addDebug function when wanting to add a
     * database related debug info
     * 
     * @see addDebug(), PHP_DebugLine::TYPE_QUERYREL
     * @since V2.1.0 - 3 apr 2007
     */
    public function queryRel($info)
    {
        return $this->addDebug($info, PHP_DebugLine::TYPE_QUERYREL);
    }

    /**
     * This is an alias for the addDebug function when wanting to add an
     * application error
     * 
     * @see addDebug(), PHP_DebugLine::TYPE_APPERROR
     * @since V2.0.0 - 21 Apr 2006
     */
    public function error($info)
    {
        return $this->addDebug($info, PHP_DebugLine::TYPE_APPERROR);
    }

    /**
     * This is an alias for adding the monitoring of processtime
     * 
     * @see addDebug(), PHP_DebugLine::TYPE_PROCESSPERF
     * @since V2.1.0 - 21 Apr 2006
     */
    public function addProcessPerf()
    {
        return $this->addDebug('', PHP_DebugLine::TYPE_PROCESSPERF);
    }

   /**
     * This a method to dump the content of any variable and add the result in
     * the debug information
     * 
     * @param   mixed       $var        Variable to dump 
     * @param   string      $varname    Name of the variable
     * 
     * @since V2.0.0 - 25 Apr 2006
     */  
    public function dump($obj, $varName = '')
    {
        $info[] = $varName;
        $info[] = $obj;
        return $this->addDebug($info, PHP_DebugLine::TYPE_DUMP);
    }

    /**
     * Set the main action of PHP script
     * 
     * @param string $action Name of the main action of the file
     * 
     * @since V2.0.0 - 25 Apr 2006
     * @see PHP_DebugLine::TYPE_CURRENTFILE
     */  
    public function setAction($action)    
    {
        $this->add($action, PHP_DebugLine::TYPE_PAGEACTION);
    }

    /**
     * Add an application setting
     * 
     * @param string $action Name of the main action of the file
     * 
     * @since V2.1.0 - 02 Apr 2007
     * @see PHP_DebugLine::TYPE_ENV
     */  
    public function addSetting($value, $name)
    {
        $this->add($name. ': '. $value, PHP_DebugLine::TYPE_ENV);
    }

    /**
     * Add a group of settings
     * 
     * @param string $action Name of the main action of the file
     * 
     * @since V2.1.0 - 2 Apr 2007
     * @see PHP_DebugLine::TYPE_ENV
     */  
    public function addSettings($values, $name)
    {
        $this->add($name. ': '. 
            PHP_Debug::dumpVar(
                $values, 
                $name, 
                false, 
                PHP_Debug::DUMP_STR
            ), 
            PHP_DebugLine::TYPE_ENV
        );
    }

    /**
     * Set the callback fucntion to process the watches, enabled depending of 
     * the options flag 'enable_watch' 
     * 
     * @since V2.0.0 - 16 apr 2006
     * @see options, watches, watchesCallback()
     */
    protected function setWatchCallback()
    {
        if ($this->options['enable_watch'] == true) {
            if (count($this->watches) === 0) {
                $watchMethod = array($this, 'watchesCallback');
                register_tick_function($watchMethod);
            }
        }
    }

    /**
     * Set the callback function to process replace the php error handler, 
     * enabled depending of the options flag 'replace_errorhandler'
     * 
     * @since V2.0.0 - 16 apr 2006
     * @see options, errorHandlerCallback()
     */
    protected function setErrorHandler()
    {
        if ($this->options['replace_errorhandler'] == true) {

            $errorhandler = array(
                $this,
                'errorHandlerCallback'
            );
            set_error_handler($errorhandler);
        }
    }

    /**
     * Callback function for php error handling
     * 
     * Warning : the only PHP error codes that are processed by this user
     * handler are : E_WARNING, E_NOTICE, E_USER_ERROR
     * For the other error codes the standart php handler will be used  
     *
     * @since V2.0.0 - 17 apr 2006
     * @see options, setErrorHandler()
     */
    public function errorHandlerCallback() 
    {
        $details = func_get_args();
        $popNumber = 3;

        // We already have line & file with setBackTrace function
        for ($index = 0; $index < $popNumber; $index++) {
		  array_pop($details);	
		}
        
        if ($details[0] != E_STRICT)                            
            $this->addDebug($details, PHP_DebugLine::TYPE_PHPERROR);
    }

    /**
	 * Add a variable to the watchlist. Watched variables must be in a declare
	 * (ticks=n) block so that every n ticks the watched variables are checked
	 * for changes. If any changes were made, the new value of the variable is
	 * recorded
     * 
     * @param string $variableName      Variable to watch
     * @since V2.0.0 - 17 apr 2006
     * @see watchesCallback()
     */
    public function watch($variableName) 
    {   
        if ($this->options['enable_watch'] == true) {
            if (isset($GLOBALS[$variableName])) {
                $this->watches[$variableName] = $GLOBALS[$variableName];
            } else {
                $this->watches[$variableName] = null;
            }
        } else {
            throw new Exception('The Watch function is disabled please set the option \'enable_watch\' to \'true\' to be able to use this feature, it\'s stable with a Unix server');
        }
    }

    /**
     * Watch callback function, process watches and add changes to the debug 
     * information
     * 
     * @since V2.0.0 - 17 apr 2006
     * @see watch()
	 */
    public function watchesCallback() 
    {
        // Check if there are variables to watch
        if (count($this->watches)) {
            foreach ($this->watches as $variableName => $variableValue) {
                if ($GLOBALS[$variableName] !== $this->watches[$variableName]) {

                    $info = array(
                        $variableName,
                        $this->watches[$variableName],
                        $GLOBALS[$variableName]
                    );
                                        
                    $this->watches[$variableName] = $GLOBALS[$variableName];
                    $this->addDebug($info, PHP_DebugLine::TYPE_WATCH);
                }
            }
        }
    }

    /**
     * Get global process time
     * 
     * @return  float     		Execution process time of the script
     * 
     * @see getElapsedTime()
     * @since V2.0.0 - 21 Apr 2006
     */ 
    public function getProcessTime()
    {
        return $this->getElapsedTime($this->startTime, $this->endTime);
    }

    /**
     * Get database related process time
     * 
     * @return  float      Execection process time of the script for all
     * 					   database	specific tasks
     * 
     * @see PHP_DebugLine::TYPE_QUERY, PHP_DebugLine::TYPE_QUERYREL
     * @since V2.0.0 - 21 Apr 2006
     */ 
    public function getQueryTime()
    {
    	  $queryTime = 0;        
        
        foreach($this->debugLineBuffer as $lkey => $lvalue)  {
            $properties = $lvalue->getProperties();
        	if ($properties['type'] == PHP_DebugLine::TYPE_QUERY 
             || $properties['type'] == PHP_DebugLine::TYPE_QUERYREL) {
                if (!empty($properties['endTime'])) {
                	$queryTime = $queryTime + 
                        $this->getElapsedTime(
                            $properties['startTime'], 
                            $properties['endTime']);
                }
            }
        }
        return $queryTime;
    }

    /**
     * PHP_Debug default output function, first we finish the processes and
     * then a render object is created and its render method is invoked
     * 
     * The renderer used is set with the options, all the possible renderer
     * are in the directory Debug/Renderer/*.php
     * (not the files ending by '_Config.php')
     * 
     * @since V2.0.0 - 13 apr 2006
     * @see Debug_Renderer
     */
    public function render()
    {
        // Finish process
        $this->endTime = PHP_Debug::getMicroTimeNow();

        // Render output if we are allowed to
        if ($this->isAllowed()) {

            // Create render object and invoke its render function
            $renderer = PHP_Debug_Renderer::factory($this, $this->options);
    
            // Get required files here to have event all Debug classes
            $this->requiredFiles = get_required_files();
    
            // Call rendering
            return $renderer->render();
        }
    }

    /**
     * Alias for the render function
     * 
     * @since V2.0.0 - 17 apr 2006
     * @see render()
     */
    public function display()
    {
        echo $this->render();
    }
    
    /**
     * Return the output without displaying it
     * 
     * @since V2.0.1 - 17 apr 2006
     * @see render()
     */
    public function getOutput()
    {
        return $this->render();
    }
    
    /**
     * Restrict access to a list of IP
     * 
     * @param array $ip     Array with IP to allow access
     * @since V2.0.0 - 11 Apr 2006
     * @see $options, isAllowed()
     */ 
    function restrictAccess($ip)
    {
        $this->options['allowed_ip'] = $ip;
    }

    /**
     * Test if the client is allowed to access the debug information
     * There are several possibilities : 
     * - 'restrict_access' flag is set to false
     * - 'restrict_access' flag is set to true and client IP is the
     * allowed ip in the options 'allowed_ip'
     * - Access by url is allowed with flag 'allow_url_access' then 
     * the client must enter the good key and password in the url
     * 
     * @since V2.0.0 - 20 apr 2006
     * @see $options, restrictAcess()
     */ 
    protected function isAllowed()
    {
        if ($this->options['restrict_access'] == true) {

            // Check if client IP is among the allowed ones
            if (in_array(
                $_SERVER['REMOTE_ADDR'], 
                $this->options['allowed_ip']
            )) {
                return true;
            }
            // Check if instant access is allowed and test key and password
            elseif ($this->options['allow_url_access'] == true) {
                
                $key = $this->options['url_key'];
                
                if (!empty($_GET[$key])) {
                    if ($_GET[$key] == $this->options['url_pass']) {
                        return true;
                    } else {
                        return false;                        
                    }
                }
                else {
                    return false;
                }                
            } else {
                return false;
            }
        } else {
            // Access is not restricted
            return true;
        }
    }

    /**
     * Return microtime from a timestamp
     *   
     * @param $time     Timestamp to retrieve micro time
     * @return numeric  Microtime of timestamp param
     * 
     * @since V1.1.0 - 14 Nov 2003
     * @see $DebugMode
     */ 
    public static function getMicroTime($time)
    {   
        list($usec, $sec) = explode(' ', $time);
        return (float)$usec + (float)$sec;
    }

    /**
     * Alias for getMicroTime(microtime()
     *   
     * @see getMicroTime()
     * @since V2.0.0 - 19 apr 2006
     */ 
    public static function getMicroTimeNow()
    {   
        return PHP_Debug::getMicroTime(microtime());
    }

    /**
     * Get elapsed time between 2 timestamp
     *   
     * @param   float $timeStart    Start time
     * @param   float $timeEnd      End time
     * @return  float               Numeric difference between the two times 
     *                              ref in format 00.0000 sec
     * 
     * @see getMicroTime()
     * @since V1.0.0 - 20 Oct 2003
     */ 
    public static function getElapsedTime($timeStart, $timeEnd)
    {           
        return round($timeEnd - $timeStart, 4);
    }

   /**
    * Returns Uri prefix, including protocol, hostname and server port.
    *
    * @return string Uniform resource identifier prefix
    */
    public static function getUriPrefix()
    {
        $pathArray = $_SERVER;

        if (PHP_Debug::isSecure()) {
          $standardPort = '443';
          $proto = 'https';
        } else {
          $standardPort = '80';
          $proto = 'http';
        }
    
        $port = $pathArray['SERVER_PORT'] == $standardPort || !$pathArray['SERVER_PORT'] ? '' : ':'.$pathArray['SERVER_PORT'];    
        return $proto.'://'. $pathArray['SERVER_NAME']. $port;
    }

    /**
     * Test if url is secured
     * 
     * @since V2.1.1 - 23 avr. 2007
     */ 
    public static function isSecure()
    {
        return $_SERVER['SERVER_PORT'] != 80;    	
    }

    /**
     * Returns current host name.
     * 
     * @since    V2.1.1 - 23 avr. 2007
     */
    public static function getHost()
    {
        $pathArray = $_SERVER;
        return isset($pathArray['HTTP_X_FORWARDED_HOST']) ? $pathArray['HTTP_X_FORWARDED_HOST'] : (isset($pathArray['HTTP_HOST']) ? $pathArray['HTTP_HOST'] : '');
    }

    /**
     * Returns current script name.
     * 
     * @return         string
     * @since V2.1.1 - 23 avr. 2007
     */
    public static function getScriptName()
    {
        $pathArray = $_SERVER;
        return isset($pathArray['SCRIPT_NAME']) ? $pathArray['SCRIPT_NAME'] : (isset($pathArray['ORIG_SCRIPT_NAME']) ? $pathArray['ORIG_SCRIPT_NAME'] : '');
    }

    /**
     * Return the query string
     * 
     * @author Vernet Loic
     * @since 2.1.1 - 23 avr. 2007
     */
    public static function getQueryString()
    {
    	return $_SERVER['QUERY_STRING'] ? '?'. $_SERVER['QUERY_STRING'] : '';
    }

    /**
     * Return the full url
     * 
     * @author Vernet Loi
     * @since 2.1.1 - 23 avr. 2007
     */
    public static function getUrl() 
    {
        return self::getUriPrefix(). self::getScriptName(). self::getQueryString();
    }

    /**
     * Set the endtime for a DebugLine in order to monitor the performance
     * of a part of script
     *   
     * @see PHP_DebugLine::endTime
     * @since V2.0.0 - 19 apr 2006
     */ 
    public function stopTimer()
    {
        $this->debugLineBuffer[count($this->debugLineBuffer)-1]->setEndTime();
    }

    /**
     * Display the content of any kind of variable
     * 
     * - Mode PHP_DEBUG_DUMP_ARR_DISP display the array
     * - Mode PHP_DEBUG_DUMP_ARR_STR return the infos as a string
     * 
     * @param   mixed       $var        Variable to dump 
     * @param   string      $varname    Name of the variable
     * @param   integer     $mode       Mode of function
     * @param   boolean     $stopExec   Stop the process after display of debug
     * @return  mixed                   Nothing or string depending on the mode
     * 
     * @since V2.0.0 - 25 Apr 2006
     */ 
    public static function dumpVar(
        $var, 
        $varName = self::DUMP_VARNAME, 
        $stopExec = false, 
        $mode = self::DUMP_DISP) {
        $dumpMethod = self::$staticOptions['dump_method'];
        ob_start();
        $dumpMethod($var);
        
        $dbgBuffer = htmlentities(ob_get_contents());
        ob_end_clean();
        
        switch ($mode) {
            default:
            case self::DUMP_DISP:

                if (empty($varName)) {
                    if (is_array($var)) {
                        $varName = 'Array';
                    } elseif (is_object($var)) {
                        $varName = get_class($var);
                    } else {
                        $varName = 'Variable';                              
                    }
                }
            
                $dbgBuffer = '<pre><b>dump of \''. $varName. '\'</b> :'. 
                    CR. $dbgBuffer. '</pre>';
                echo $dbgBuffer;
                break;

            case PHP_Debug::DUMP_STR:
                return($dbgBuffer);
        }        

        // Check process stop
        if ($stopExec) {
            $backtrace = debug_backtrace();
            $dieMsg  = '<pre><b>Process stopped by PHP_Debug</b>'. CR;
            $dieMsg .= $backtrace[0]['file'] ?     '&raquo; file     : <b>'. 
                $backtrace[0]['file'] .'</b>'. CR : '';  
            $dieMsg .= $backtrace[0]['line'] ?     '&raquo; line     : <b>'. 
                $backtrace[0]['line'] .'</b>'. CR : '';  
            $dieMsg .= $backtrace[1]['class'] ?    '&raquo; class    : <b>'. 
                $backtrace[1]['class'] .'</b>'. CR : '';  
            $dieMsg .= $backtrace[1]['function'] ? '&raquo; function : <b>'. 
                $backtrace[1]['function'] .'</b>'. CR : '';  
            $dieMsg .= '</pre>'; 
            die($dieMsg);
        }
    }

    /**
     * Get one option
     *
     * @param string $optionsIdx Name of the option to get
     * @since V2.0.0 - 13 apr 2006
     */
    public function getOption($optionIdx)
    {
        return $this->options[$optionIdx];
    }

    /**
     * Getter of requiredFiles property
     * 
     * @return array Array with the included/required files
     * @since V2.0.0 - 13 apr 2006
     * @see requiredFiles
     */
    public function getRequiredFiles()
    {
        return $this->requiredFiles;
    }

    /**
     * Getter of debugString property
     * 
     * @since V2.0.0 - 13 apr 2006
     * @see debugLineBuffer
     */
    public function getDebugBuffer()
    {
        return $this->debugLineBuffer;           
    }

    /**
     * Getter of queryCount property
     * 
     * @since V2.0.0 - 21 Apr 2006
     * @see queryCount
     */
    public function getQueryCount()
    {
        return $this->queryCount;           
    }

    /**
     * Debug default output function, simply uses the static dump fonction
     * of this class 
     * 
     * @since V2.0.0 - 11 apr 2006
     * @see dump
     */
    public function __toString()
    {
        return '<pre>'. PHP_Debug::dumpVar(
            $this, 
            __CLASS__. ' class instance',
            false, 
            PHP_Debug::DUMP_STR
        ). '</pre>';  
    }
}