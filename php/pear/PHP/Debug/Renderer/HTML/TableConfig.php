<?php

/**
 * Configuration file for HTML_Table renderer
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loic Vernet <qrf_coil at yahoo dot fr>
 * @since V2.0.0 - 10 Apr 2006
 * 
 * @package PHP_Debug
 * @filesource
 * 
 * @version    CVS: $Id: TableConfig.php,v 1.1 2008/05/02 14:26:37 c0il Exp $
 */

class PHP_Debug_Renderer_HTML_TableConfig
{    
    /**
     * Config container for Debug_Renderer_HTML_Table
     * 
     * @var array
     * @since V2.0.0 - 11 apr 2006
     */
    protected static $options = array();
    
    /**
     * Static Instance of class
     *  
     * @var array
     * @since V2.0.0 - 11 apr 2006
     */
    protected static $instance = null;
        
    /**
     * Debug_Renderer_HTML_Table_Config class constructor
     * 
     * @since V2.0.0 - 11 apr 2006
     */
    protected function __construct()
    {
        /**
         * Enable or disable Credits in debug infos 
         */
        self::$options['HTML_TABLE_disable_credits'] = false;

        /**
         * Enable or disable included and required files
         */ 
        self::$options['HTML_TABLE_show_templates'] = true;
        
        /**
         * Enable or disable pattern removing in included files
         */
        self::$options['HTML_TABLE_remove_templates_pattern'] = false;
        
        /**
         * Pattern list to remove in the display of included files
         * if HTML_TABLE_remove_templates_pattern is set to true
         */ 
        self::$options['HTML_TABLE_templates_pattern'] = array(); 

        /**
         * Enable or disable visualisation of $globals var in debug
         */
        self::$options['HTML_TABLE_show_globals'] = false;   

        /** 
         * Enable or disable search in debug 
         */ 
        self::$options['HTML_TABLE_enable_search'] = true; 

        /** 
         * Enable or disable view of super arrays 
         */
        self::$options['HTML_TABLE_show_super_array'] = true;

        /** 
         * Enable or disable the use of $_REQUEST array instead of 
         * $_POST + _$GET + $_COOKIE + $_FILES
         */
        self::$options['HTML_TABLE_use_request_arr'] = false;  

        /** 
         * View Source script path
         */
        self::$options['HTML_TABLE_view_source_script_path'] = '.';  
        
        /** 
         * View source script file name
         */     
        self::$options['HTML_TABLE_view_source_script_name'] = 'PHP_Debug_ShowSource.php'; 

        /** 
         * css path
         */     
        self::$options['HTML_TABLE_css_path'] = 'css'; 

        /** 
         * Tabsize for view source script
         */     
        self::$options['HTML_TABLE_view_source_tabsize'] = 4; 

        /** 
         * Tabsize for view source script
         */     
        self::$options['HTML_TABLE_view_source_numbers'] = 2; //HL_NUMBERS_TABLE 

       /** 
        * Define wether the display must be forced for the debug type when
        * in search mode
        */
        self::$options['HTML_TABLE_search_forced_type'] = array( 
            PHP_DebugLine::TYPE_STD         => false, 
            PHP_DebugLine::TYPE_QUERY       => false, 
            PHP_DebugLine::TYPE_QUERYREL    => false,
            PHP_DebugLine::TYPE_ENV         => false,
            PHP_DebugLine::TYPE_APPERROR    => false,
            PHP_DebugLine::TYPE_CREDITS     => false,
            PHP_DebugLine::TYPE_SEARCH      => true,
            PHP_DebugLine::TYPE_DUMP        => false,
            PHP_DebugLine::TYPE_PROCESSPERF => false,
            PHP_DebugLine::TYPE_TEMPLATES   => false,
            PHP_DebugLine::TYPE_PAGEACTION  => false,
            PHP_DebugLine::TYPE_SQLPARSE    => false,
            PHP_DebugLine::TYPE_WATCH       => false,
            PHP_DebugLine::TYPE_PHPERROR    => false
        );    

        /**
         * After this goes all HTML related variables
         * 
         * 
         * HTML code for header 
         */         
         self::$options['HTML_TABLE_header'] = '
<div id="pd-div">
<br />
<a name="pd-anchor" id="pd-anchor" />
<table class="pd-table" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td class="pd-table-header" align="center">File</td>
    <td class="pd-table-header" align="center">Line</td>
    <td class="pd-table-header" align="center">Inside/From function</td>
    <td class="pd-table-header" align="center">Inside/From Class</td>  
    <td class="pd-table-header" align="center">Type</td>  
    <td class="pd-table-header" align="center">Debug information</td>
    <td class="pd-table-header" align="center">Execution time (sec)</td>
  </tr>
        ';

        /**
         * HTML code for footer 
         */         
         self::$options['HTML_TABLE_credits'] = '
        PHP_Debug ['. PHP_Debug::PEAR_RELEASE .'] | By COil (2007) | 
        <a href="http://www.coilblog.com">http://www.coilblog.com</a> | 
        <a href="http://phpdebug.sourceforge.net/">PHP_Debug Project Home</a> 
        ';

        /**
         * HTML code for a basic header 
         */         
         self::$options['HTML_TABLE_simple_header'] = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Pear::PHP_Debug</title>
';

        /**
         * HTML code for a basic footer 
         */         
         self::$options['HTML_TABLE_simple_footer'] = '
</body>
</html>
';

        /**
         * HTML pre-row code for debug column file 
         */         
         self::$options['HTML_TABLE_prerow'] = '
  <tr>';

        /**
         * HTML pre-row code for debug column file 
         */         
         self::$options['HTML_TABLE_interrow_file'] = '
    <td class="pd-td" align="center">';

        /**
         * HTML post-row code for debug column line (centered)
         */         
        self::$options['HTML_TABLE_interrow_line'] = '
    </td>
    <td class="pd-td" align="center">';

        self::$options['HTML_TABLE_interrow_function'] = self::$options['HTML_TABLE_interrow_line']; 
        self::$options['HTML_TABLE_interrow_class']    = self::$options['HTML_TABLE_interrow_line']; 
        self::$options['HTML_TABLE_interrow_type']     = self::$options['HTML_TABLE_interrow_line']; 
        self::$options['HTML_TABLE_interrow_time']     = self::$options['HTML_TABLE_interrow_line']; 

        /**
         * HTML pre-row code for debug column info
         */         
        self::$options['HTML_TABLE_interrow_info'] = '
    </td>
    <td class="pd-td" align="left">';


        /**
         * HTML post-row code for debugline 
         */         
         self::$options['HTML_TABLE_postrow'] = '
    </td>
  </tr>
';

        /**
         * HTML code for footer 
         */         
         self::$options['HTML_TABLE_footer'] = '
</table>
</div>
';

    }

    /**
     * returns the static instance of the class
     *
     * @since V2.0.0 - 11 apr 2006
     * @see PHP_Debug
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
        return self::$instance;
    }
    
    /**
     * returns the configuration
     *
     * @since V2.0.0 - 07 apr 2006
     * @see PHP_Debug
     */
    public static function getConfig()
    {
        return self::$options;
    }
    
    /**
     * HTML_Table_Config
     * 
     * @since V2.0.0 - 26 Apr 2006
     */
    public function __toString()
    {
        return '<pre>'. PHP_Debug::dumpVar(
            $this->singleton()->getConfig(), 
            __CLASS__, 
            false,
            PHP_DEBUG_DUMP_ARR_STR). '</pre>';
    }   
}