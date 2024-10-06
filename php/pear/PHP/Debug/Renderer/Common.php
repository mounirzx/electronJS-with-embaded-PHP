<?php

/**
 * A base class for Debug renderers, must be inherited by all such.
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loic Vernet <qrf_coil at yahoo dot fr>
 * @since V2.0.0 - 10 Apr 2006
 * 
 * @package PHP_Debug
 * @filesource
 * @version    CVS: $Id: Common.php,v 1.1 2008/05/02 14:26:37 c0il Exp $
 */

class PHP_Debug_Renderer_Common
{
    /**
     * 
     * @var Debug object
     * This is the debug object to render
     */    
    protected $DebugObject = null;

    /**
     * Run-time configuration options.
     *
     * @var array
     * @access public
     */
    protected $options = array();

    /**
     * Default configuration options.
     *
     * @See Debug/Renderer/*.php for the complete list of options
     * @var array
     * @access public
     */
    protected $defaultOptions = array();

    /**
     * Set run-time configuration options for the renderer
     *
     * @param array $options Run-time configuration options.
     * @access public
     */
    public function setOptions($options = array())
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Default output function
     */
    public function __toString()
    {
        return '<pre>'. 
            PHP_Debug::dumpVar(
                $this, 
                __CLASS__, 
                PHP_DEBUG_DUMP_ARR_STR
            ) . '<pre>';  
    }

    /**
     * PHP_DebugOutput class destructor
     */
    function __destruct()
    {
    }
}