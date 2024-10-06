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
 * @version  SVN: $Revision: 169 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-12 01:28:43 +0200 (lun., 12 sept. 2011) $
 */

/**
 * This is the exportation class relying on the API (= on the full hierarchy of
 * metaclasses stored in the model). Note that another way to export a model would
 * be to use ExporterXSL, which is based on an XSL transformation of XMI. 
 * A class implementing ExporterAPI must reside in a subfolder containing a class
 * named PHP_UML_<name of the output format>_Exporter. This class must also have a
 * public method "generate", which is used to start the serialization process.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_ExporterAPI extends PHP_UML_Output_Exporter
{
    /**
     * Object storing some contextual data
     * 
     * @var PHP_UML_Output_ApiContextPackage
     */
    protected $ctx;
        
    public function export($outDir)
    {
        if (!file_exists($outDir))
            throw new PHP_UML_Exception('Export directory ('.$outDir.') does not exist.');
        if (empty($this->structure) || empty($this->structure->packages)) {
            throw new PHP_UML_Exception('No model to export');
        }
    }
    
    /**
     * Return the ApiContextPackage currently associated to the rendering
     * 
     * @return PHP_UML_Output_ApiContextPackage
     */
    public function getContextPackage()
    {
        return $this->ctx;
    }
    
    /**
    * Sets the allInherited/-ing arrays with all the classifiers that a given
    * classifier inherits from
    *
    * @param PHP_UML_Metamodel_Classifier $s The initial reference classifier
    * @param PHP_UML_Metamodel_Classifier $t The current classifier to check
    */
    protected function setAllInherited(PHP_UML_Metamodel_Classifier $s, PHP_UML_Metamodel_Classifier $t)
    {
        if (!empty($t->superClass) && is_object($t->superClass[0])) {
            $h = $t->superClass[0];
            $this->setAllInherited($s, $h);
            $this->ctx->allInherited[$s->id][]  = $h;
            $this->ctx->allInheriting[$h->id][] = $s;
        }
    }
    
    /**
     * Sets the allImplemented/-ing arrays with all the interfaces that a given
     * class implements (including those of the inherited classes)
     *
     * @param PHP_UML_Metamodel_Class      $s The initial reference class
     * @param PHP_UML_Metamodel_Classifier $t The current classifier to check
     */
    protected function setAllImplemented(PHP_UML_Metamodel_Class $s, PHP_UML_Metamodel_Classifier $t)
    {
        if (!empty($t->superClass) && is_object($t->superClass[0])) {
            $this->setAllImplemented($s, $t->superClass[0]);
        }
        if (isset($t->implements) && is_array($t->implements)) {
            foreach ($t->implements as $impl) {
                if (is_object($impl)) {
                    $this->setAllImplemented($s, $impl);
                    $this->ctx->allImplemented[$s->id][]     = $impl;
                    $this->ctx->allImplementing[$impl->id][] = $s;
                }
            }
        }
    }
    
    /**
     * Recurses into all the packages to build a list of all the generalizations
     * and realizations between elements.
     * We normally do this before creating the detailed files.
     *
     * @param PHP_UML_Metamodel_Package $pkg Starting package
     */
    protected function setAllSuperClassifiers(PHP_UML_Metamodel_Package $pkg)
    {
        foreach ($pkg->ownedType as $type) {
            switch (get_class($type)) {
            case PHP_UML_Metamodel_Superstructure::META_CLASS:
                $this->setAllImplemented($type, $type);
            case PHP_UML_Metamodel_Superstructure::META_INTERFACE:
                $this->setAllInherited($type, $type);
            }
        }
        foreach ($pkg->nestedPackage as $np) {
            $this->setAllSuperClassifiers($np);
        }
    }
    
    protected function initContextPackage(PHP_UML_Metamodel_Package $pkg, $dir, $rpt)
    {
        $this->ctx->classes    = array();
        $this->ctx->interfaces = array();
        $this->ctx->datatypes  = array();
        
        $this->ctx->dir = $dir;
        $this->ctx->rpt = $rpt;
        
        foreach ($pkg->ownedType as $type) {
            switch (get_class($type)) {
            case PHP_UML_Metamodel_Superstructure::META_INTERFACE:
                $this->ctx->interfaces[] = $type;
                break;
            case PHP_UML_Metamodel_Superstructure::META_DATATYPE:
                $this->ctx->datatypes[] = $type;
                break;
            default:
                $this->ctx->classes[] = $type;
            }
        }
    }
}
?>
