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
 * @version  SVN: $Revision: 178 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 03:08:06 +0200 (lun., 19 sept. 2011) $
 */

/**
 * Defines a way to import data by relying on a scan of a set of files/folders,
 * and then get the result as a UML model (as a superstructure).
 * Implements PHP_UML_Input_Importer.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @abstract
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Input_ImporterFileScanner extends PHP_UML_FileScanner
{
    /**
     * Superstructure (model) to fill
     *
     * @var PHP_UML_Metamodel_Superstructure
     */
    protected $model;
    
    public function __construct(PHP_UML_Metamodel_Superstructure $model = null)
    {
        if (!isset($model)) {
            $this->model = new PHP_UML_Metamodel_Superstructure();
            $this->model->initModel();
        } else {
            $this->model = $model;
        }
    }
    
    /**
     * Get the resulting, final model
     * 
     * @return PHP_UML_Metamodel_Superstructure
     */
    public function getModel()
    {
        return $this->model;
    }
    
    public abstract function import();
    
    /**
     * Set an initial model
     * 
     * @param PHP_UML_Metamodel_Superstructure $model New model
     */
    public function setModel(PHP_UML_Metamodel_Superstructure $model)
    {
        $this->model = $model;
    }
    
    public function raiseUnknownFolderException($basedir)
    {
        throw new PHP_UML_Exception($basedir.': unknown folder.');
    }
}
?>
