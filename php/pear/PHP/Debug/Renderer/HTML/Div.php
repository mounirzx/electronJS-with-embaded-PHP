<?php

/**
 * Class of the HTML_Div renderer
 * 
 * Idea from the debug system of the symfony PHP framework 
 * @see http://www.symfony-project.org
 * @author Fabien Potencier
 * @author François Zaninotto
 * 
 * @author  Vernet Loïc
 * 
 * @version CVS: $Id: Div.php,v 1.3 2008/10/05 14:41:37 c0il Exp $
 */

require_once 'PHP/Debug/Renderer/HTML/DivConfig.php';


/**
 * A floating div renderer for PHP_Debug
 *
 * Returns a floating based representation of the debug infos in XHTML sctrict
 * format
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loïc Vernet <qrf_coil at yahoo dot fr>
 * @since V2.1.0 - 30 march 2007
 * 
 * @package PHP_Debug
 * @filesource
 */

class PHP_Debug_Renderer_HTML_Div extends PHP_Debug_Renderer_Common
{    
    // debug types for Vars & Config
    protected static $settingsType = array(
        PHP_DebugLine::TYPE_ENV,
    );

    // debug types for Log & Message tab
    protected static $msgTypes = array(
        PHP_DebugLine::TYPE_STD,
        PHP_DebugLine::TYPE_PAGEACTION,
        PHP_DebugLine::TYPE_APPERROR,
        PHP_DebugLine::TYPE_CREDITS,
        PHP_DebugLine::TYPE_DUMP,
        PHP_DebugLine::TYPE_WATCH,
        PHP_DebugLine::TYPE_PHPERROR
    );

    // debug types for Database tab
    protected static $databaseTypes = array(
        PHP_DebugLine::TYPE_QUERY,
        PHP_DebugLine::TYPE_QUERYREL,
        PHP_DebugLine::TYPE_SQLPARSE,
    );

    /**
     * Debug_Renderer_HTML_Div class constructor
     * 
     * @since V2.1.0 - 3 apr 2007
     */
    function __construct($DebugObject, $options)
    {
        $this->DebugObject = $DebugObject;
        $this->defaultOptions = PHP_Debug_Renderer_HTML_DivConfig::singleton()->getConfig();
        $this->setOptions($options);
        
        if ($this->options['HTML_DIV_disable_credits'] == false) {
            $this->DebugObject->addDebugFirst($this->options['HTML_DIV_credits'], 
                PHP_DebugLine::TYPE_CREDITS);
        }

        // Add execution time
        $this->DebugObject->addProcessPerf();
    }

    /**
     * This is the function to display the debug informations
     *
     * @since V2.0.0 - 07 Apr 2006
     * @see PHP_Debug::Render()
     */
    public function display()
    {
        $buffer = '';

        // Header    	
        $buffer .= $this->displayHeader();

        // Infos
        $debugInfos = $this->DebugObject->getDebugBuffer(); 
             
        // Vars & config
        $buffer .= $this->showVarsAndConfig($debugInfos);

        // Logs & msg
        $buffer .= $this->showLogsAndMsg($debugInfos);

        // Database
        $buffer .= $this->showDatabaseInfos($debugInfos);

        // W3C Validation
        $buffer .= $this->showW3cValidation($debugInfos);

        // Process time
        $buffer .= $this->showProcessTime($debugInfos);
        
        // Footer
        $buffer .= $this->displayFooter();
        
        return $buffer;        
    }

    /**
     * Show W3C validator tab
     * 
     * @author COil
     * @since V2.1.1 - 23 apr 2007
     */
    protected function showW3cValidation()
    {
        return str_replace(
            array(
                '{$imagesPath}',
            ),
            array(
                $this->options['HTML_DIV_images_path']
            ),
            $this->options['HTML_DIV_sfWebDebugW3CDetails']
        );
    }

    /**
     * Add the debug informations of the W3C validation process 
     * 
     * @author Vernet Loïc
     * @since 2.1.0 - 23 avr. 2007
     */
    protected function addW3CErrorInfos($res, $key)
    {
        $title = ucwords($key);  
        $type = 'sfW3C'. $title;
        $errorCpt = 1;
        $results = str_replace(
          '{$title}', 
          $title, 
          $this->options['HTML_DIV_sfWebDebugW3CTableHeader']
        );

        foreach ($res->$key as $error) {
            $id = $errorCpt. ($error->messageid ? ' ('. $error->messageid. ')' : '');
            $results .= str_replace(
                array(
                    '{$type}', 
                    '{$cpt}', 
                    '{$line}', 
                    '{$col}', 
                    '{$message}',
                    '{$source}',
                ),
                array(
                    $type,
                    $id,
                    $error->line,
                    $error->col,
                    $error->message,
                    '&nbsp',
                ),
                $this->options['HTML_DIV_sfWebDebugW3CErrorRow']
            );                        
            $errorCpt++;        
        }
        $results .= '</table>';
        
        return $results;
    }

    /**
     * Shows vars & config
     * 
     * @param array debug row
     * 
     * @author COil
     * @since V2.1.0 - 30 march 2007
     */
    protected function showDatabaseInfos($debugInfos)
    {
        $idx = 1;
        $buffer = '';

        foreach ($debugInfos as $debugInfo) {
            $properties = $debugInfo->getProperties();
            if (in_array($properties['type'], self::$databaseTypes)) {                
                $buffer.= '<li>['. $this->processExecTime($properties). '] '. 
                    $this->processDebugInfo($properties) .'</li>'. CR;
            }
        }

        return str_replace(
            array('{$buffer}'),
            array($buffer ? $buffer : '<li>No database debug available</li>'),
            $this->options['HTML_DIV_sfWebDebugDatabaseDetails']
        );
    }

    /**
     * Shows vars & config
     * 
     * @author COil
     * @since V2.1.0 - 30 march 2007
     */
    protected function showLogsAndMsg($debugInfos)
    {
        $idx = 1;
        $buffer = '';

        foreach($debugInfos as $debugInfo) {
            $properties = $debugInfo->getProperties();
            if (in_array($properties['type'], self::$msgTypes)) {
            
                // Error level of debug information
                $level = $this->getLogInfoLevel($properties);   
                $infoImg = $this->getImageInfo($level);
            
                $buffer .= '<tr class=\'sfWebDebugLogLine '. $this->getDebugLevelClass($level). '\'>
                    <td class="sfWebDebugLogNumber"># '. $idx. '</td>
                    <td class="sfWebDebugLogType">
                        <img src="'. $this->options['HTML_DIV_images_path']. '/'. $infoImg .'" alt="" />&nbsp;'. $this->processType($properties).
                    '</td>
                    <td class="sfWebDebugLogFile">'. $this->processFile($properties). '</td>
                    <td class="sfWebDebugLogLine">'. $this->processLine($properties). '</td>
                    <td class="sfWebDebugLogClass">'. $this->processClass($properties). '</td>
                    <td class="sfWebDebugLogFunction">'. $this->processFunction($properties). '</td>
                    <td class="sfWebDebugLogTime">'. $this->processExecTime($properties). '</td>
                    <td class="sfWebDebugLogMessage">'. $this->processDebugInfo($properties). '</td>
                </tr>'. CR;
                $idx++;
            }
        }

        return str_replace(
            array(
                '{$buffer}',
                '{$imagesPath}',
            ),
            array(
                $buffer,
                $this->options['HTML_DIV_images_path']
            ),
            $this->options['HTML_DIV_sfWebDebugLog']
        );
    }

    /**
     * Get the log level of the debug info
     * 
     * @author COil
     * @since V2.1.0 - 2 avr. 2007
     * 
     * @param array debug row
     */
    protected function getLogInfoLevel($properties)
    {
        $level = PHP_DebugLine::INFO_LEVEL;

        switch ($properties['type']) {
            case PHP_DebugLine::TYPE_PAGEACTION:
            case PHP_DebugLine::TYPE_CREDITS:
            case PHP_DebugLine::TYPE_DUMP:
            case PHP_DebugLine::TYPE_WATCH:
            break;

            case PHP_DebugLine::TYPE_APPERROR:
                $level = PHP_DebugLine::ERROR_LEVEL;
            break;

            case PHP_DebugLine::TYPE_PHPERROR:
                $level = $this->getPhpErrorLevel($properties);
            break;
        }
        
        return $level;    	
    }

    /**
     * Return the global error level corresponding to the related php error
     * level
     * 
     * @param array debug row
     * 
     * @author COil
     * @since 2.1.0 - 3 apr 2007
     */
    protected function getPhpErrorLevel($properties)
    {
        $infos = $properties['info'];

        switch ($infos[0]) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return PHP_DebugLine::ERROR_LEVEL;
            break;                
            
            case E_WARNING:
            case E_CORE_WARNING:
            case E_NOTICE:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            case E_ALL:
            case E_STRICT:
            case E_RECOVERABLE_ERROR:
                return PHP_DebugLine::WARNING_LEVEL;
            break;                

            default:
                return PHP_DebugLine::ERROR_LEVEL;
            break;                
        }
    }

    /**
     * Get the image info for the current debug type
     * 
     * @author COil
     * @since V2.1.0 - 2 avp 2007
     */
    protected function getDebugLevelClass($debug_level)
    {
        return $this->options['HTML_DIV_debug_level_classes'][$debug_level];
    }

    /**
     * Get the image info for the current debug type
     * 
     * @author COil
     * @since V2.1.0 - 2 avp 2007
     */
    protected function getImageInfo($debug_level)
    {
        $info = $this->options['HTML_DIV_image_info'];
        $warning = $this->options['HTML_DIV_image_warning'];
        $error   = $this->options['HTML_DIV_image_error'];

    	switch ($debug_level) {
            case PHP_DebugLine::INFO_LEVEL:
                $level = $info;
            break;

            case PHP_DebugLine::WARNING_LEVEL:
                $level = $warning;
            break;

            case PHP_DebugLine::ERROR_LEVEL:
                $level = $error;
            break;
    	}
        
        return $level;
    }

    /**
     * Shows vars & config
     * 
     * @author COil
     * @since V2.1.0 - 30 march 2007
     */
    protected function showVarsAndConfig($debugInfos)
    {
        return str_replace(
            array(
                '{$sfWebDebugRequest}',
                '{$sfWebDebugResponse}',
                '{$sfWebDebugSettings}',
                '{$sfWebDebugConstants}',
                '{$sfWebDebugGlobals}',
                '{$sfWebDebugPhp}',
                '{$sfWebDebugFiles}',
                '{$imagesPath}',
            ),
            array(
                $this->showSuperArray(PHP_Debug::GLOBAL_REQUEST),
                $this->showSuperArray(PHP_Debug::GLOBAL_COOKIE),
                $this->showArray($this->settingsAsArray($debugInfos), 'Settings'),
                $this->showArray(get_defined_constants(true)),
                $this->showArray($this->globalsAsArray(), 'Globals'),
                $this->showArray($this->phpInfoAsArray(), 'PHP Infos'),
                $this->showTemplates(),
                $this->options['HTML_DIV_images_path'],
            ),
            $this->options['HTML_DIV_sfWebDebugConfig']
        );
    }

    /**
     * Return all settings of application
     * 
     * @author COil
     * @since V2.1.0 - 2 apr 2007
     */
    public function settingsAsArray($debugInfos)
    {
        $settings = array();
        foreach($debugInfos as $debugInfo) {
            $infos = $debugInfo->getProperties();
            if (in_array($infos['type'], self::$settingsType)) {
                $settings[] = $infos['info']; 
            }
        }	
    
        return $settings;
    }

   /**
    * Returns PHP globals variables as a sorted array.
    *
    * @return array PHP globals
    * @since V2.1.0 - 2 apr 2007
    */
    public static function globalsAsArray()
    {
        $values = array();
        foreach (array('cookie', 'server', 'get', 'post', 'files', 'env', 'session') as $name) {

            if (!isset($GLOBALS['_'.strtoupper($name)])) {
                continue;
            }
    
            $values[$name] = array();
            foreach ($GLOBALS['_'. strtoupper($name)] as $key => $value) {
                $values[$name][$key] = $value;
            }
            ksort($values[$name]);
        }   

        ksort($values);

        return $values;
    }

    /**
     * Returns PHP information as an array.
     * 
     * @return  array An array of php information
     * @since V2.1.0 - 2 apr 2007
     */
    public static function phpInfoAsArray()
    {
        $values = array(
            'php'        => phpversion(),
            'os'         => php_uname(),
            'extensions' => get_loaded_extensions(),
        );

        // assign extension version if available
        if ($values['extensions']) {
        	foreach ($values['extensions'] as $lkey => $extension) {
        		$values['extensions'][$lkey] = phpversion($extension) ? $extension. 
                    ' ('. phpversion($extension). ')' : $extension;
        	}
        }

        return $values;
    }

    /**
     * Add the process time information to the debug information
     * 
     * @since V2.0.0 - 18 Apr 2006
     */ 
    protected function showProcessTime($debugInfos)
    {
        // Lang
        $txtExecutionTime = 'Global execution time ';
        $txtPHP           = 'PHP';
        $txtSQL           = 'SQL';              
        $txtSECOND        = 's';
        $txtOneQry        = ' query';
        $txtMultQry       = ' queries';
        $queryCount       = $this->DebugObject->getQueryCount();
        $txtQuery         = $queryCount > 1 ? $txtMultQry : $txtOneQry;
        $buffer           = '';

        // Performance Debug
        $processTime = $this->DebugObject->getProcessTime();
        $sqlTime    = $this->DebugObject->getQueryTime();
        $phpTime    = $processTime - $sqlTime;
    
        $sqlPercent = round(($sqlTime / $processTime) * 100, 2);                              
        $phpPercent = round(($phpTime / $processTime) * 100, 2);

        $processTime = $processTime*1000;
        $sqlTime    = $sqlTime*1000;
        $phpTime    = $phpTime*1000;
        
        if ($debugInfos) {
            $buffer .= '
            <tr>
                <th>message</th>
                <th>time (ms)</th>
                <th>percent</th>
            </tr>'. CR;

        	foreach($debugInfos as $debugInfo) {
                $properties = $debugInfo->getProperties();
                if ($properties['startTime'] && $properties['endTime']) {

                    $localPercent = round((($properties['endTime'] - 
                        $properties['startTime'])*1000 / $processTime) * 100, 2);
                    $buffer .= '
                    <tr>
                        <td class="sfWebDebugLogMessagePerf">'. $this->ProcessDebugInfo($properties). '</td>
                        <td style="text-align: right">'. $this->ProcessExecTime($properties). '</td>
                        <td style="text-align: right">'. $localPercent. '%</td>
                    </tr>'. CR;
                }
            }
        }

        return str_replace(
            array(
                '{$txtExecutionTime}',
                '{$processTime}',
                '{$txtPHP}',
                '{$phpTime}',
                '{$phpPercent}',
                '{$txtSQL}',
                '{$sqlTime}',
                '{$sqlPercent}',
                '{$queryCount}',
                '{$txtQuery}',
                '{$buffer}'
                
            ),
            array(
                $txtExecutionTime,
                $processTime,
                $txtPHP,
                $phpTime,
                $phpPercent,
                $txtSQL,
                $sqlTime,
                $sqlPercent,
                $queryCount,
                $txtQuery,
                $buffer
            ),
            $this->options['HTML_DIV_sfWebDebugTimeDetails']       
        );
    }

    /**
     * Default render function for HTML_Div renderer
     *
     * @since V2.0.0 - 11 Apr 2006
     * @see Renderer
     */
    public function render()
    {
        return $this->display();
    }

    /**
     * Displays the header of the PHP_Debug object
     *
     * @since V2.0.0 - 08 Apr 2006
     * @see PHP_Debug
     */
    protected function displayHeader()
    {
        return str_replace(
            array(
                '{$nb_queries}', 
                '{$exec_time}',
                '{$imagesPath}',
                '{$phpDebugVersion}'
            ),
            array(
                $this->DebugObject->getQueryCount(), 
                $this->DebugObject->getProcessTime() * 1000,
                $this->options['HTML_DIV_images_path'],
                PHP_Debug::PEAR_RELEASE
            ),        
            $this->options['HTML_DIV_header']);  
    }        

    /**
     * Diplays the footer of the PHP_Debug object
     *
     * @since V2.0.0 - 08 Apr 2006
     * @see PHP_Debug
     */
    protected function displayFooter()
    {
        return $this->options['HTML_DIV_footer'];
    }        
    
    /**
     * process display of the execution time of debug information  
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the main debug info
     * @since V2.0.0 - 28 Apr 2006
     */ 
    protected function processExecTime($properties)
    {   
        // Lang
        $txtPHP = 'PHP';
        $txtSQL = 'SQL';
        $txtSECOND = 's';

        if (!empty($properties['endTime'])) {

            $time = round(PHP_Debug::getElapsedTime(
                $properties['startTime'], 
                $properties['endTime']
            ) * 1000);

            $buffer = $this->span($time > 1 ? $time. ' ms' : '&lt; 1 ms', 'time');

        } else {
            $buffer = '&nbsp;';
        }

        return $buffer; 
    }
    
    /**
     * process display of the main information of debug 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the main debug info
     * @since V2.0.0 - 28 Apr 2006
     */ 
    protected function processDebugInfo($properties)
    {   
        $buffer = '';

        switch($properties['type']) {

            // Case for each of the debug lines types
            // 1 : Standard
            case PHP_DebugLine::TYPE_STD:
                $buffer .= $this->span($properties['info'], 'std');
                break;
            
            // 2 : Query
            case PHP_DebugLine::TYPE_QUERY:
                $buffer .= preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', 
                    '<span class="sfWebDebugLogInfo">\\1</span>', 
                    $properties['info']);
                break;

            // 3 : Query related
            case PHP_DebugLine::TYPE_QUERYREL:
                $buffer .= $this->span($properties['info'], 'query');
                break;
                
            // 4 : Environment
            case PHP_DebugLine::TYPE_ENV:
                $buffer .= $this->showSuperArray($properties['info']);
                break;

            // 6 : User app error
            case PHP_DebugLine::TYPE_APPERROR:
                $buffer .= $this->span('/!\\ User error : '. 
                    $properties['info']. ' /!\\', 'app-error');
                break;
                
            // 7
            case PHP_DebugLine::TYPE_CREDITS:
                $buffer .= $this->span($properties['info'], 'credits');            
                break;

            // 9
            case PHP_DebugLine::TYPE_DUMP:
                $buffer .= $this->showDump($properties);
                break;

            // 10
            case PHP_DebugLine::TYPE_PROCESSPERF:
                $buffer .= $this->showProcessTime();
                break;

            // 12 : Main Page Action
            case PHP_DebugLine::TYPE_PAGEACTION;
                $buffer .= $this->span('[Action : '. 
                    $properties['info']. ']', 'pageaction');
                break;

            // 14 : SQL parse 
            case PHP_DebugLine::TYPE_SQLPARSE:
                $buffer .= $properties['info'];
                break;

            // 15 : Watches
            case PHP_DebugLine::TYPE_WATCH:
                $infos = $properties['info'];
                $buffer .= 'Variable '. $this->span($infos[0], 'watch').
                           ' changed from value '. 
                            $this->span($infos[1], 'watch-val'). ' ('. gettype($infos[1]). 
                            ') to value '. $this->span($infos[2], 'watch-val'). 
                            ' ('. gettype($infos[2]). ')';
                break;

            // 16 : PHP errors
            case PHP_DebugLine::TYPE_PHPERROR:                
                $buffer .= $this->showError($properties['info']);
                break;

            default:
                $buffer .= '<b>Default('. $properties['type'].
                           ')</b>: TO IMPLEMENT OR TO CORRECT : &gt;'. 
                           $properties['info']. '&lt;';
                break;
        }

        return $buffer;
    }

    /**
     * Return a string with applying a span style on it
     * 
     * @param string $info String to apply the style
     * @param string $class CSS style to apply to the string
     * @return string Formatted string with style applied
     * @since V2.0.0 - 05 May 2006
     */ 
    protected function span($info, $class)
    {   
        return '<span class="'. $class .'">'. $info .'</span>'; 
    }

    /**
     * process display of the type of the debug information 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the debug type
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function processType($properties)
    {   
        $buffer = PHP_DebugLine::$debugLineLabels[$properties['type']];
        return $buffer;
    }

    /**
     * process display of Class 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the class
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function processClass($properties)
    {
        $buffer = '';

        switch ($properties['type'])
        {
            case PHP_DebugLine::TYPE_STD:
            case PHP_DebugLine::TYPE_QUERY:
            case PHP_DebugLine::TYPE_QUERYREL:
            case PHP_DebugLine::TYPE_APPERROR:             
            case PHP_DebugLine::TYPE_PAGEACTION:
            case PHP_DebugLine::TYPE_PHPERROR:
            case PHP_DebugLine::TYPE_SQLPARSE:
            case PHP_DebugLine::TYPE_WATCH:
            case PHP_DebugLine::TYPE_DUMP:
                        
                if (!empty($properties['class'])) {
                    $buffer .= $properties['class'];
                } else {
                    $buffer .= '&nbsp;';
                }

                break;
                        
            case PHP_DebugLine::TYPE_CREDITS: 
            case PHP_DebugLine::TYPE_SEARCH:
            case PHP_DebugLine::TYPE_PROCESSPERF:
            case PHP_DebugLine::TYPE_TEMPLATES:
            case PHP_DebugLine::TYPE_ENV:

                $buffer .= '&nbsp;';

                break;
        
            default:
                break;
        }
        
        return $buffer;
    }

    /**
     * process display of function 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the function
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function processFunction($properties)
    {
        $buffer = '';

        switch ($properties['type'])
        {
            case PHP_DebugLine::TYPE_STD:
            case PHP_DebugLine::TYPE_QUERY:
            case PHP_DebugLine::TYPE_QUERYREL:
            case PHP_DebugLine::TYPE_APPERROR:             
            case PHP_DebugLine::TYPE_PAGEACTION:
            case PHP_DebugLine::TYPE_PHPERROR:
            case PHP_DebugLine::TYPE_SQLPARSE:
            case PHP_DebugLine::TYPE_WATCH:
            case PHP_DebugLine::TYPE_DUMP:
                        
                if (!empty($properties['function'])) {                	
                    if ($properties['function'] != 'unknown') { 
                        $buffer .= $properties['function']. '()';
                    } else {
                        $buffer .= '&nbsp;';
                }
                } else {
                    $buffer .= '&nbsp;';
                }

                break;
                        
            case PHP_DebugLine::TYPE_CREDITS: 
            case PHP_DebugLine::TYPE_SEARCH:
            case PHP_DebugLine::TYPE_PROCESSPERF:
            case PHP_DebugLine::TYPE_TEMPLATES:
            case PHP_DebugLine::TYPE_ENV:

                $buffer .= '&nbsp;';
                break;
        
            default:
                break;
        }
        
        return $buffer;
    }


    /**
     * process display of line number 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the line number
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function processLine($properties)
    {
        $buffer = '';

        switch ($properties['type'])
        {
            case PHP_DebugLine::TYPE_STD:
            case PHP_DebugLine::TYPE_QUERY:
            case PHP_DebugLine::TYPE_QUERYREL:
            case PHP_DebugLine::TYPE_APPERROR:             
            case PHP_DebugLine::TYPE_PAGEACTION:
            case PHP_DebugLine::TYPE_PHPERROR:
            case PHP_DebugLine::TYPE_SQLPARSE:
            case PHP_DebugLine::TYPE_WATCH:
            case PHP_DebugLine::TYPE_DUMP:
                        
                if (!empty($properties['line'])) {
                    $buffer.= '<span class="line">'. 
                        $properties['line']. '</span>';
                } else {
                    $buffer.= '&nbsp;';
                }        

                break;
                        
            case PHP_DebugLine::TYPE_CREDITS: 
            case PHP_DebugLine::TYPE_SEARCH:
            case PHP_DebugLine::TYPE_PROCESSPERF:
            case PHP_DebugLine::TYPE_TEMPLATES:
            case PHP_DebugLine::TYPE_ENV:

                $buffer.= '&nbsp;';

                break;
        
            default:
                break;
        }
        
        return $buffer;
    }

    /**
     * process display of file name 
     * 
     * @param array $properties Properties of the debug line
     * @return string Formatted string containing the file
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function processFile($properties)
    {
    	$buffer = '';

        switch ($properties['type'])
        {
        	case PHP_DebugLine::TYPE_STD:
            case PHP_DebugLine::TYPE_QUERY:
            case PHP_DebugLine::TYPE_QUERYREL:
            case PHP_DebugLine::TYPE_APPERROR:             
            case PHP_DebugLine::TYPE_PAGEACTION:
            case PHP_DebugLine::TYPE_PHPERROR:
            case PHP_DebugLine::TYPE_SQLPARSE:
            case PHP_DebugLine::TYPE_WATCH:
            case PHP_DebugLine::TYPE_DUMP:

                if (!empty($properties['file'])) {
                    if (!empty($this->options['HTML_DIV_view_source_script_path']) && 
                        !empty($this->options['HTML_DIV_view_source_script_name'])) {
                        $buffer .= '<a href="'. 
                                $this->options['HTML_DIV_view_source_script_path'].
                                '/'. 
                                $this->options['HTML_DIV_view_source_script_name'].  
                                '?file='. urlencode($properties['file']);

                        $buffer .= '">'. basename($properties['file']). '</a>'; 

                    } else {
                        $buffer .= basename($properties['file']);                    	
                    }
                } else {
                    $buffer .=  '&nbsp;';
                }        
        
                break;
                        
            case PHP_DebugLine::TYPE_CREDITS: 
            case PHP_DebugLine::TYPE_SEARCH:
            case PHP_DebugLine::TYPE_PROCESSPERF:
            case PHP_DebugLine::TYPE_TEMPLATES:
            case PHP_DebugLine::TYPE_ENV:

                $buffer .=  '&nbsp;';

                break;
        
            default:
                break;
        }
        
        return $buffer;
    }

    /**
     * Dump of a variable
     * 
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function showDump($properties)
    {
    	$buffer = '';

        // Check display with a <pre> design
        if (is_array($properties['info'][1])) {
            $preDisplay = true;                      
        } elseif (is_object($properties['info'][1])) {
            $preDisplay = true;                      
        } else {
            $preDisplay = false;                      
        }

        // Check var name
        if (empty($properties['info'][0])) {
            if (is_array($properties['info'][1])) {
                $varName = 'Array';
            } elseif (is_object($properties['info'][1])) {
                $varName = get_class($properties['info'][1]);
            } else {
                $varName = 'Variable';                              
            }
        } else {
            $varName = $properties['info'][0];
        }
        
        // Output
        if ($properties['type'] != PHP_DebugLine::TYPE_ENV) { 
            $title = 'dump of \'';
        } 
        
        $title .= $varName. '\' ('.  gettype($properties['info'][1]) .') : ';
        
        $buffer .= $this->span($title , 'dump-title');
        
        if ($preDisplay == true){
            $buffer .= '<pre>';                   
            $buffer .= PHP_Debug::dumpVar(
                $properties['info'][1], 
                '', 
                false, 
                PHP_Debug::DUMP_STR);
        } else {
            $buffer .= $this->span(
                PHP_Debug::dumpVar(
                    $properties['info'][1], 
                    '', 
                    false, 
                    PHP_Debug::DUMP_STR
                ), 'dump-val');
        }

        if ($preDisplay == true) {
            $buffer .= '</pre>';                  
        }

        return $buffer;
    }

    /**
     * Get the templates info
     * 
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function showTemplates()
    {
        $txtMainFile = 'MAIN File';
        $idx = 1;
        $buffer = '<br />';

        foreach($this->DebugObject->getRequiredFiles() as $lvalue) {
        	
        	$isToDisplay = true;

            if ($this->options['HTML_DIV_view_source_excluded_template']) {        	
            	foreach ($this->options['HTML_DIV_view_source_excluded_template'] as $template) {        		
            		if (stristr($lvalue, $template)) {
            			$isToDisplay = false;
            		}
            	}
            }

        	if ($isToDisplay == true) {

                $buffer .= '<div class="source">';
	            $buffer .= $this->span($this->truncate($lvalue), 'files');
	            $buffer .= ' <a href="'. 
                             $this->options['HTML_DIV_view_source_script_path'].
	                         '/'. $this->options['HTML_DIV_view_source_script_name'].  
	                         '?file='. urlencode($lvalue). '">View source</a> ';
	                
	            // main file    
	            if ($idx == 1) {
	                $buffer .= $this->span('&laquo; '. $txtMainFile, 'main-file');
	            }                       
	            $idx++;
	            $buffer .= '</div><br />'. CR;
        	}            
        }        

        $buffer .= '<br />'. CR;
        return $buffer; 
    }
    
    
    /**
     * Truncate/replace a pattern from the file path
     * 
     * @param string full file path
     * 
     * @author COil
     * @since V2.1.0 - 3 apr 2007
     * 
     * @see 
     * - HTML_DIV_remove_templates_pattern
     * - HTML_DIV_templates_pattern
     */
    protected function truncate($file)
    {
    	if ($this->options['HTML_DIV_remove_templates_pattern'] && 
            $this->options['HTML_DIV_templates_pattern']) {
            return strtr($file, $this->options['HTML_DIV_templates_pattern']);
    	} 

        return $file;
    }
    
    /**
     * Process an error info
     * 
     * @param array $info Array containing information about the error
     * 
     * @since V2.0.0 - 25 Apr 2006
     * @see PHP_DebugLine::TYPE_PHPERROR
     */ 
    protected function showError($infos)    
    {
        $buffer = '';
        $infos[1] = str_replace("'", '"', $infos[1]);
        $infos[1] = str_replace(
            'href="function.', 
            ' href="http://www.php.net/'. 
            $this->options['lang']. '/', $infos[1]);

        switch ($infos[0])
        {
            case E_WARNING:
                $errorlevel = 'PHP WARNING : ';
                $buffer .= '<span class="pd-php-warning"> /!\\ '. 
                    $errorlevel. $infos[1] . ' /!\\ </span>';                
                break;

            case E_NOTICE:
                $errorlevel = 'PHP notice : ';
                $buffer .= '<span class="pd-php-notice">'. 
                    $errorlevel. $infos[1] . '</span>';
                break;

            case E_USER_ERROR:
                $errorlevel = 'PHP User error : ';
                $buffer .= '<span class="pd-php-user-error"> /!\\ '. 
                    $errorlevel. $infos[1] . ' /!\\ </span>';
                break;

            case E_STRICT:
                
                $errorlevel = 'PHP STRICT error : ';
                $buffer .= '<span class="pd-php-user-error"> /!\\ '. 
                    $errorlevel. $infos[1] . ' /!\\ </span>';
                break;

            default:
                $errorlevel = 'PHP errorlevel = '. $infos[0]. ' : ';
                $buffer .= $errorlevel. 
                    ' is not implemented in PHP_Debug ('. __FILE__. ','. __LINE__. ')';
                break;
        }
        
        return $buffer;
    }

    /**
     * Show a super array
     * 
     * @param string $SuperArrayType Type of super en array to add
     * @since V2.0.0 - 07 Apr 2006
     */ 
    protected function showSuperArray($SuperArrayType)    
    {
        // Lang
        $txtVariable   = 'Var';
        $txtNoVariable = 'NO VARIABLE';
        $NoVariable    = ' -- '. $txtNoVariable. ' -- ';
        $SuperArray    = null;
        $buffer        = '';

        $ArrayTitle = PHP_Debug::$globalEnvConstantsCorresp[$SuperArrayType];
        $SuperArray = $GLOBALS[$ArrayTitle];
        $Title = $ArrayTitle. ' '. $txtVariable;
        $SectionBasetitle = '<b>'. $Title. '('. count($SuperArray). ') :';

        if (count($SuperArray)) {
            $buffer .= $SectionBasetitle. '</b>';
            $buffer .= '<pre>'. 
                PHP_Debug::dumpVar(
                    $SuperArray, 
                    $ArrayTitle, 
                    false, 
                    PHP_Debug::DUMP_STR
                    ). '</pre>';
        } else {
            $buffer .= $SectionBasetitle. $NoVariable. '</b>';
        }
        
        return $buffer;
    }

    /**
     * Displays an array.
     * 
     * @param string $SuperArrayType Type of super en array to add
     * @since V2.0.0 - 07 Apr 2006
     */ 
    protected function showArray($array, $name)    
    {
        // Lang
        $txtNoVariable = 'NO VARIABLE';
        $NoVariable    = ' -- '. $txtNoVariable. ' -- ';
        $buffer        = '';
        $SectionBasetitle = '<b>'. $name. '('. count($array). ') :';

        if (count($array)) {
            $buffer .= $SectionBasetitle. '</b>';
            $buffer .= '<pre>'. PHP_Debug::dumpVar(
                $array, 
                $name, 
                false, 
                PHP_Debug::DUMP_STR). '</pre>';
        } else {
            $buffer .= $SectionBasetitle. $NoVariable. '</b>';
        }
        
        return $buffer;
    }
}