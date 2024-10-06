<?php

/**
 * Class of the HTML_Table renderer
 */
require_once 'PHP/Debug/Renderer/HTML/TableConfig.php';

/**
 * A concrete renderer for Debug
 *
 * Returns a table-based representation of the debug infos in HTML 4
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loic Vernet <qrf_coil at yahoo dot fr>
 * @since V2.0.0 - 10 Apr 2006
 * 
 * @package PHP_Debug
 * @filesource
 * 
 * @version    CVS: $Id: Table.php,v 1.2 2009/01/12 21:13:00 c0il Exp $
 */

class PHP_Debug_Renderer_HTML_Table extends PHP_Debug_Renderer_Common
{    
    /**
     * Debug_Renderer_HTML_Table class constructor
     * 
     * @since V2.0.0 - 13 apr 2006
     */
    function __construct($DebugObject, $options)
    {
        $this->DebugObject = $DebugObject;
        $this->defaultOptions = PHP_Debug_Renderer_HTML_TableConfig::singleton()->getConfig();
        $this->setOptions($options);
        
        // Now add in first the predefined debugline depending on the configuration
        if ($this->options['HTML_TABLE_enable_search'] == true)
            $this->DebugObject->addDebugFirst('', PHP_DebugLine::TYPE_SEARCH);

        if ($this->options['HTML_TABLE_disable_credits'] == false)
            $this->DebugObject->addDebugFirst(
                $this->options['HTML_TABLE_credits'], 
                PHP_DebugLine::TYPE_CREDITS);

        // Now add in last positions the others predefined debuglines

        // Add execution time 
        $this->DebugObject->addDebug('', PHP_DebugLine::TYPE_PROCESSPERF);
        
        // Add templates 
        if ($this->options['HTML_TABLE_show_templates'] == true)
            $this->DebugObject->addDebug(STR_N, PHP_DebugLine::TYPE_TEMPLATES);
            
        // Add env variables
        $this->addSuperArray();

    }

    /**
     * This is the function to display the debug information
     *
     * @since V2.0.0 - 07 Apr 2006
     * @see PHP_Debug::Render()
     */
    public function display()
    {
        $buffer = '';
    	   
        // Header    	
        $buffer .= $this->displayHeader();
           
        // Body     
        foreach ($this->DebugObject->getDebugBuffer() as $lvalue) {

            // Check if the debug must be displayed
            if ($this->checkType($lvalue) == true) {

                $tmpBuff = $this->displayDebugLine($lvalue);

                // Check if we have a search criteria
                if ($this->checkSearch($tmpBuff)) {
                
                    // Pre-row
                    $buffer .= $this->options['HTML_TABLE_prerow'];

                    // Row body
                    $buffer .= $this->highlight($tmpBuff);
    
                    // Post-row
                    $buffer .= $this->options['HTML_TABLE_postrow'];
                
                }
            }
        }

        // Footer
        $buffer .= $this->displayFooter();
        
        // Output Buffer
        return $buffer;        
    }

    /**
     * This function highligth the searched keyword
     *
     * @param string $debugLineStr The formatted debug line object to check
     * @return string Formatted string with keyword highligthed
     * 
     * @since V2.0.0 - 2 May 2006
     */
    protected function highlight($debugLineStr)
    {   
        // Check if search is activated   
        if (!empty($_GET['PHPDEBUG_SEARCH']) && 
              trim($_GET['PHPDEBUG_SEARCH']) != '') {
            if (!empty($_GET['PHPDEBUG_SEARCH_CS'])) {
                $replaceFunction = 'str_replace';
            } else {
                $replaceFunction = 'str_ireplace';
            }
            return $replaceFunction($_GET['PHPDEBUG_SEARCH'], 
                '<span class="pd-search-hl">'. $_GET['PHPDEBUG_SEARCH']. 
                '</span>' , $debugLineStr);        
        } else {
        	return $debugLineStr;
        }
    }

    /**
     * This function check if the user has chosen a search criteria and
     * make the search on the formatted debug info
     *
     * @param string $debugLineStr The formatted debug line object to check
     * @return boolean Search criteria has been found of search is disabled
     * 
     * @since V2.0.0 - 2 May 2006
     */
    protected function checkSearch($debugLineStr)
    {        
        // Check if search is activated   
        if (!empty($_GET['PHPDEBUG_SEARCH']) && 
              trim($_GET['PHPDEBUG_SEARCH']) != '') {
           
            if (!empty($_GET['PHPDEBUG_SEARCH_CS'])) {
            	$searchFunction = 'strstr';
            } else {
                $searchFunction = 'stristr';
            }
            return $searchFunction($debugLineStr, trim($_GET['PHPDEBUG_SEARCH']));
        } else {
            return true;
        }
    }

    /**
     * This function check if the user has chosen a filter in the debug type
     * combobox and it returns of the debug line is allowed to be output or no
     *
     * @param DebugLine $debugLine The debug line object to check
     * @return boolean true type is allowed to be
     * 
     * @since V2.0.0 - 26 Apr 2006
     */
    protected function checkType($debugLine)
    {
        $properties = $debugLine->getProperties(); 
    	
        // Check if we must only show debug information of a kind	
      	if ($this->options['HTML_TABLE_search_forced_type'][$properties['type']] == false) {
        	if (!empty($_GET['PHPDEBUG_SEARCH_TYPE'])) {
                if ($properties['type'] == $_GET['PHPDEBUG_SEARCH_TYPE']) {                	
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * Default render function for HTML_Table renderer
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
        return $this->options['HTML_TABLE_header'];
    }        

    /**
     * Diplays the footer of the PHP_Debug object
     *
     * @since V2.0.0 - 08 Apr 2006
     * @see PHP_Debug
     */
    protected function displayFooter()
    {
        return $this->options['HTML_TABLE_footer'];
    }        
    
    /**
     * This is the function that displays a debug line, each step correspond 
     * to a new cell, actully there are 6 types : 
     * - File 
     * - Line 
     * - Function 
     * - Class 
     * - Debug main information 
     * - Execution time
     * 
     * @param DebugLine DebugLine, the debug line to process
     *
     * @since V2.0.0 - 07 Apr 2006
     */    
    protected function displayDebugLine($DebugLine)    
    {
         // DebugLine properties
        $properties = $DebugLine->getProperties();

        // 1 - File
        $buffer = $this->processFile($properties);
        
        // 2 - Line
        $buffer .= $this->processLine($properties);

        // 3 - Function
        $buffer .= $this->processFunction($properties);
                
        // 4 - Class
        $buffer .= $this->processClass($properties);

        // 5 - Type
        $buffer .= $this->processType($properties);

        // 6 - Debug info
        $buffer .= $this->processDebugInfo($properties);
                        
        // 7 - Execution time
        $buffer .= $this->processExecTime($properties);

        // Output display buffer
        return $buffer;        
        
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
        $buffer = $this->options['HTML_TABLE_interrow_time'];
        
        if (!empty($properties['endTime'])) {
            $buffer .=  $this->span(PHP_Debug::getElapsedTime(
                $properties['startTime'], 
                $properties['endTime']), 
                'time');
        } else {
            $buffer .= '&nbsp;';
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
        
        switch($properties['type'])
        {
            // Case for each of the debug lines types
            // 1 : Standard
            case PHP_DebugLine::TYPE_STD:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->span($properties['info'], 'std');
                break;
            
            // 2 : Query
            case PHP_DebugLine::TYPE_QUERY:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->span($properties['info'], 'query');
                break;

            // 3 : Query related
            case PHP_DebugLine::TYPE_QUERYREL:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->span($properties['info'], 'query');
                break;
                
            // 4 : Environment
            case PHP_DebugLine::TYPE_ENV:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showSuperArray($properties['info']);
                break;

            // 6 : User app error
            case PHP_DebugLine::TYPE_APPERROR:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->span('/!\\ User error : '. 
                    $properties['info'] . ' /!\\', 'app-error');
                break;
                
            // 7
            case PHP_DebugLine::TYPE_CREDITS:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->span($properties['info'], 'credits');            
                break;

            // 8
            case PHP_DebugLine::TYPE_SEARCH:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showSearch();
                break;

            // 9
            case PHP_DebugLine::TYPE_DUMP:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showDump($properties);
                break;

            // 10
            case PHP_DebugLine::TYPE_PROCESSPERF:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showProcessTime();
                break;

            // 11
            case PHP_DebugLine::TYPE_TEMPLATES:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showTemplates();
                break;

            // 12 : Main Page Action
            case PHP_DebugLine::TYPE_PAGEACTION;
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $txtPageAction = 'Page Action';
                $buffer .= $this->span("[ $txtPageAction : ". 
                    $properties['info']. ' ]', 'pageaction');
                break;

            // 14 : SQL parse 
            case PHP_DebugLine::TYPE_SQLPARSE:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $properties['info'];
                break;

            // 15 : Watches
            case PHP_DebugLine::TYPE_WATCH:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $infos = $properties['info'];
                $buffer .= 'Variable '. $this->span($infos[0], 'watch').
                           ' changed from value '. $this->span($infos[1], 'watch-val').
                           ' ('. gettype($infos[1]). 
                           ') to value '. $this->span($infos[2], 'watch-val'). 
                           ' ('. gettype($infos[2]). ')';
                break;

            // 16 : PHP errors
            case PHP_DebugLine::TYPE_PHPERROR:                
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= $this->showError($properties['info']);
                break;

            default:
                $buffer = $this->options['HTML_TABLE_interrow_info'];
                $buffer .= "<b>Default(". $properties['type'].  
                           ")</b>: TO IMPLEMENT OR TO CORRECT : >". 
                           $properties['info']. '<';            
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
        return '<span class="pd-'. $class .'">'. $info .'</span>'; 
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
        $buffer = $this->options['HTML_TABLE_interrow_type'];
        $buffer .= PHP_DebugLine::$debugLineLabels[$properties['type']];
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
                        
                $buffer .= $this->options['HTML_TABLE_interrow_class'];
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

                $buffer .= $this->options['HTML_TABLE_interrow_class'];
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
                        
                $buffer .= $this->options['HTML_TABLE_interrow_function'];
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

                $buffer .= $this->options['HTML_TABLE_interrow_function'];
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
                        
                $buffer.= $this->options['HTML_TABLE_interrow_line'];
                if (!empty($properties['line'])) {
                    $buffer.= '<span class="pd-line">'. $properties['line']. '</span>';
                } else {
                    $buffer.= '&nbsp;';
                }        

                break;
                        
            case PHP_DebugLine::TYPE_CREDITS: 
            case PHP_DebugLine::TYPE_SEARCH:
            case PHP_DebugLine::TYPE_PROCESSPERF:
            case PHP_DebugLine::TYPE_TEMPLATES:
            case PHP_DebugLine::TYPE_ENV:

                $buffer.= $this->options['HTML_TABLE_interrow_line'];
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

                $buffer .= $this->options['HTML_TABLE_interrow_file'];
                        
                if (!empty($properties['file'])) {
                    if (!empty($this->options['HTML_TABLE_view_source_script_path']) &&
                        !empty($this->options['HTML_TABLE_view_source_script_name'])) {
                        $buffer .= '<a href="'. $this->options['HTML_TABLE_view_source_script_path']
                                . '/'. $this->options['HTML_TABLE_view_source_script_name']  
                                .'?file='. urlencode($properties['file']);

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

                $buffer .= $this->options['HTML_TABLE_interrow_file'];
                $buffer .=  '&nbsp;';

                break;
        
            default:
                break;
        }
        
        return $buffer;
    }

    /**
     * Dump a variable
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
            $title = "dump of '";
        } 
        
        $title .= $varName. "' (".  gettype($properties['info'][1]) .") : ";
        
        $buffer .= $this->span($title , 'dump-title');
        
        if ($preDisplay == true){
            $buffer .= '<pre>';                   
            $buffer .= PHP_Debug::dumpVar($properties['info'][1], 
                '', false, PHP_Debug::DUMP_STR);
        } else {
            $buffer .= $this->span(PHP_Debug::dumpVar(
                $properties['info'][1], 
                '', 
                false, 
                PHP_Debug::DUMP_STR), 'dump-val');
        }

        if ($preDisplay == true){
            $buffer .= '</pre>';                  
        }

        return $buffer;
    }

    /**
     * Process the search combo box
     * 
     * @since V2.0.0 - 26 Apr 2006
     */ 
    protected function showSearch()
    {
        // Repost all posted data
        $txtGo             = 'Go !';
        $txtStringToSearch = 'Search for';
        $txtCaseSensitive  = 'Case sensitive';
        $txtSelectByType   = 'Select only info of type';        
        $buffer = '';
        
        $debugSearchVal   = isset($_REQUEST["PHPDEBUG_SEARCH"])    ? trim($_REQUEST["PHPDEBUG_SEARCH"]) : '';
        $debugSearchCSVal = isset($_REQUEST["PHPDEBUG_SEARCH_CS"]) ? ' checked="checked"' : '';
        
        $buffer .= '
        <form id="phpDebugForm" action="'. $_SERVER['PHP_SELF']. '">
        <table>
        <tr>
          <td class="pd-search">'. $txtStringToSearch .'</td>
          <td class="pd-search">:</td>
          <td class="pd-search">
            <input class="pd-search" type="text" name="PHPDEBUG_SEARCH" value="'. $debugSearchVal. '" />
          </td>
          <td class="pd-search">'. $txtCaseSensitive .'</td>
          <td class="pd-search">:</td>
          <td class="pd-search">
            <input class="pd-search" type="checkbox" name="PHPDEBUG_SEARCH_CS" '. $debugSearchCSVal .' />
          </td>
        </tr>
        <tr>
          <td class="pd-search">'. $txtSelectByType. '</td>
          <td class="pd-search">:</td>
          <td class="pd-search">
            <select class="pd-search" name="PHPDEBUG_SEARCH_TYPE">';
                    foreach (PHP_DebugLine::$debugLineLabels as $lkey => $lvalue) {
                        $debugSearchTypeVal = (!empty($_REQUEST["PHPDEBUG_SEARCH_TYPE"]) 
                                           & $lkey == $_REQUEST["PHPDEBUG_SEARCH_TYPE"]) ? ' selected="selected"' : '';
                        $buffer .= "              <option value=\"$lkey\"$debugSearchTypeVal>&raquo; $lvalue</option>". CR;
                    }                                   
                    $buffer .= '
            </select>
          </td>
          <td class="pd-search">&nbsp;</td>
          <td class="pd-search">&nbsp;</td>        
          <td class="pd-search">
            <input class="pd-search" type="submit" value="'. $txtGo. '" />
          </td>
        </tr>
        </table>
        </form>';
            
        return $buffer;
    }

    /**
     * Process the templates
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
        	
            if ($this->options['HTML_TABLE_view_source_excluded_template']) {
            	foreach ($this->options['HTML_TABLE_view_source_excluded_template'] as $template) {        		
            		if (stristr($lvalue, $template)) {
            			$isToDisplay = false;
            		}
            	}
            }
        	
        	if ($isToDisplay == true) {
        	
	            $buffer .= $this->span($lvalue, 'files');
	            $buffer .= ' <a href="'. $this->options['HTML_TABLE_view_source_script_path']
	                         . '/'. $this->options['HTML_TABLE_view_source_script_name']  
	                         .'?file='. urlencode($lvalue). '">View source</a> ';
	                
	            // Mark main file    
	            if ($idx == 1) {
	                $buffer .= $this->span('&laquo; '. $txtMainFile, 'main-file');
	            }                       
	            $idx++;
	            $buffer .= '<br />'. CR;
        	}            
        }        

        $buffer .= '<br />'. CR;
        return $buffer; 
    }
    
    /**
     * Process an error info
     * 
     * @param array $info Array containing information about the error
     * 
     * @since V2.0.0 - 25 Apr 2006
     * @see PHP_DEBUGLINE_PHPERROR
     */ 
    protected function showError($infos)    
    {
        $buffer = '';
        $infos[1] = str_replace("'", '"', $infos[1]);
        $infos[1] = str_replace('href="function.', ' href="http://www.php.net/'. $this->options['lang']. '/', $infos[1]);

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
                $buffer .= $errorlevel. ' is not implemented in PHP_Debug ('. 
                    __FILE__. ','. __LINE__. ')';
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
        $NoVariable    =  ' -- '. $txtNoVariable. ' -- ';
        $SuperArray    = null;
        $buffer        = '';

        $ArrayTitle = PHP_Debug::$globalEnvConstantsCorresp[$SuperArrayType];
        $SuperArray = $GLOBALS[$ArrayTitle];
        $Title = $ArrayTitle. ' '. $txtVariable;
        $SectionBasetitle = '<b>$Title ('. count($SuperArray). ') :';

        if (count($SuperArray)) {
            $buffer .= $SectionBasetitle. '</b>';
            $buffer .= '<pre>'. PHP_Debug::dumpVar(
                $SuperArray, 
                $ArrayTitle, 
                false, 
                PHP_Debug::DUMP_STR). '</pre>';
        }
        else {
            $buffer .= $SectionBasetitle. "$NoVariable</b>";
        }
        return $buffer;
    }

    /**
     * Add the environment display depending on the current configuration
     *
     * @since V2.0.0 - 18 apr 2006
     */
    protected function addSuperArray()
    {
        if ($this->options['HTML_TABLE_show_super_array'] == true) {            
            
            // Divide Request tab
            if ($this->options['HTML_TABLE_use_request_arr'] == false) {
                // Include Post Var
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_POST, PHP_DebugLine::TYPE_ENV);
    
                // Include Get Var
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_GET, PHP_DebugLine::TYPE_ENV);
    
                // Include File Var
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_FILES, PHP_DebugLine::TYPE_ENV);
                
                // Include Cookie Var
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_COOKIE, PHP_DebugLine::TYPE_ENV);
            }
            else {
                // Only display Request Tab
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_REQUEST, PHP_DebugLine::TYPE_ENV);
            }
    
            // Include sessions variabmes, check if we have any
            if (!empty($_SESSION)) {
                $this->DebugObject->addDebug(PHP_Debug::GLOBAL_SESSION, PHP_DebugLine::TYPE_ENV);
            }
        }
    }

    /**
     * Add the process time information to the debug information
     * 
     * @since V2.0.0 - 18 Apr 2006
     */ 
    protected function showProcessTime()
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
        
        $buffer .= '<div><table class="pd-perf-table"><tr><td class="pd-perf" align="center">'. $txtExecutionTime;
        $buffer .= '</td><td class="pd-perf" align="center">'. $processTime . $txtSECOND;
        $buffer .= '</td><td class="pd-perf" align="center">100%';
        $buffer .= '</td><td class="pd-perf" align="center">&nbsp;</td></tr>';

        $buffer .= '<tr><td class="pd-perf" align="center">'. $txtPHP;
        $buffer .= '</td><td class="pd-perf" align="center">'. $phpTime . $txtSECOND;
        $buffer .= '</td><td class="pd-perf" align="center">'. $phpPercent .'%';
        $buffer .= '</td><td class="pd-perf" align="center">&nbsp;</td></tr>';
        
        $buffer .= '<tr><td class="pd-perf" align="center">'. $txtSQL;
        $buffer .= '</td><td class="pd-perf" align="center">'. $sqlTime. $txtSECOND;
        $buffer .= '</td><td class="pd-perf" align="center">'. $sqlPercent . '%';
        $buffer .= '</td><td class="pd-perf" align="center">'. $queryCount. $txtQuery. '</td></tr>';
        
        $buffer .= '</table></div>';      
                      
        return $buffer;
    }
}