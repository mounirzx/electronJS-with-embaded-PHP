<?php

require_once 'PHP/Debug/Renderer/Common.php';

/**
 * A loader class for the renderers.
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loic Vernet <qrf_coil at yahoo dot fr>
 * @since V2.0.0 - 10 Apr 2006
 * 
 * @package PHP_Debug
 * @filesource
 * @version    CVS: $Id: Renderer.php,v 1.1 2008/05/02 14:26:37 c0il Exp $
 */

class PHP_Debug_Renderer
{

    /**
     * Attempt to return a concrete Debug_Renderer instance.
     *
     * @param string $mode Name of the renderer.
     * @param array $options Parameters for the rendering.
     * @access public
     */
    public static function factory($debugObject, $options)
    {
        $className = 'PHP_Debug_Renderer_'. $options['render_type']. 
            '_'. $options['render_mode'];
        $classPath = 'PHP/Debug/Renderer/'. $options['render_type']. 
            '/'. $options['render_mode']. '.php';

        include_once $classPath;

        if (class_exists($className)) {
            $obj = new $className($debugObject, $options);
        } else {
            include_once 'PEAR.php';
            PEAR::raiseError('PHP_Debug: renderer &gt;' . 
                $options['DEBUG_render_mode'] . '&lt; not found', true);
            return NULL;
        }
        return $obj;
    }
}