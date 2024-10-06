<?php
/**
 * PHP_UML
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 179 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 16:09:54 +0200 (lun., 19 sept. 2011) $
 */

/**
 * This class is a set of various data/switches that can be used by the
 * implementations of the exporter
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_Exporter
{
    const APP_NAME = 'PHP_UML';
    
    /**
     * A UML model
     * @var PHP_UML_Metamodel_Superstructure
     */
    protected $structure;
   
    /**
     * Constructor
     *
     * @param string $format Usual format name
     */
    public function __construct()
    {
    }
    
    /**
     * Setter for the model.
     * 
     * @param PHP_UML_Metamodel_Superstructure $model A UML model
     */
    public function setModel(PHP_UML_Metamodel_Superstructure $model)
    {
        $this->structure = $model;
    }
    
    
    /**
     * Export the current model to a particular output format.
     * 
     * @param string $outDir Directory where the resulting output must be stored
     */
    abstract public function export($outDir);
    
    /**
     * Factory method to retrieve an implementation of an exporter, given the "common name" of
     * a format.
     * 
     * @param string $format Common name of the desired export format
     * 
     * @return PHP_UML_Output_Exporter
     */
    static function getInstance($format)
    {
        if (empty($format))
            $format = 'xmi';
        
        $format = strtolower($format);
        
        $ucf = ucfirst($format);
        if ($ucf == "Htmlnew")
            $ucf = "HtmlNew";
        
        $className = 'PHP_UML_Output_'.$ucf.'_Exporter';
        
        if (!class_exists($className)) {
            throw new PHP_UML_Exception('Unknown export format: "'.$format.'"');
        }
        
        $instance = new $className();

        return $instance;
    }
}
?>
