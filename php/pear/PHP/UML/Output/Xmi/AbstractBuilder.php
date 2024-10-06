<?php
/**
 * PHP_UML (PHP_UML_Output_Xmi_AbstractBuilder)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 166 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-07 23:20:01 +0200 (mer., 07 sept. 2011) $
 */

/**
 * Abstract class to generate UML elements in XMI code.
 * 
 * To deal with the two different versions of XMI (1.4 and 2.1), you must use one of
 * the two specialized versions:
 * PHP_UML_Output_Xmi_BuilderImpl1, or PHP_UML_Output_Xmi_BuilderImpl2
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Output_Xmi_AbstractBuilder implements PHP_UML_Output_Xmi_Builder
{
    const EXPORTER_NAME = 'PEAR\PHP_UML';
    const PHP_FILE      = 'PHP File';

    static protected $allStereotypes = array('File', self::PHP_FILE);
    static protected $allExtensions  = array(''=>'File', 'php'=>self::PHP_FILE);

    protected $xmiVersion = 2;
    
    protected $encoding = 'iso-8859-1';
    protected $logicalView = true;
    protected $deploymentView = false;
    protected $componentView = false;
    protected $stereotypes = false;
    
    /**
     * Sets the XML encoding
     *
     * @param string $encoding Encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
    
    /**
     * Enables the inclusion of a logical view in the serialized model
     *
     * @param boolean $value True/False
     */
    public function setLogicalView($value)
    {
        $this->logicalView = $value;
    }
    
    /**
     * Enables the inclusion of a deployment view in the serialized model
     *
     * @param boolean $value True/False
     */
    public function setDeploymentView($value)
    {
        $this->deploymentView = $value;
    }
    
    /**
     * Enables the inclusion of a component view in the serialized model
     *
     * @param boolean $value True/False
     */
    public function setComponentView($value)
    {
        $this->componentView = $value;
    }
    
    /**
     * Enables the inclusion of the stereotypes in the serialized model
     *
     * @param boolean $value True/False
     */
    public function setStereotypes($value)
    {
        $this->stereotypes = $value;
    }
    
    
    /**
     * Generates an ID for an element. A partial identifier can be provided
     * (used for classes and their idrefs)
     *
     * @param string $prefix Prefix
     * 
     * @return string ID
     */
    static protected function getUID($prefix = null)
    {
        if (is_null($prefix))
            return PHP_UML_SimpleUID::getUID();
        else
            return md5(self::EXPORTER_NAME.'#'.$prefix);
    }

    /**
     * Gets an XML header for the XMI file
     *
     * @return string
     */
    protected function getXmlHeader()
    {
        return '<?xml version="1.0" encoding="'.$this->encoding.'"?>'; 
    }

    /**
     * Factory method. Retrieves a proper implementation class,
     * matching the XMI version.
     *
     * @param float $version XMI version
     * 
     * @return PHP_UML_Output_Xmi_Builder An XMI builder object 
     */
    static function getInstance($version)
    {
        if ($version < 2)
            return new PHP_UML_Output_Xmi_BuilderImpl1();
        else
            return new PHP_UML_Output_Xmi_BuilderImpl2();
    }
    
   

    /**
     * Get all packages, recursively, with all the objects they contain
     * Initially called by generateXmi() on the root package
     * 
     * @param PHP_UML_Metamodel_Package $package Base package
     * 
     * @return string XMI code
     */
    protected function getAllPackages(PHP_UML_Metamodel_Package $package)
    {
        $str = $this->getPackageOpen($package);

        $str .= $this->getNamespaceOpen();

        $str .= $this->getOwnedElements($package);

        foreach ($package->nestedPackage as $pkg)
            $str .= $this->getAllPackages($pkg, false);

        $str .= $this->getNamespaceClose();
        $str .= $this->getPackageClose();
       
        return $str;
    }

    /**
     * Get the different types owned by a package
     *
     * @param PHP_UML_Metamodel_Package $package Package to get the types of
     * 
     * @return string XMI code
     */
    protected function getOwnedElements(PHP_UML_Metamodel_Package $package)
    {
        $str = '';
        foreach ($package->ownedType as &$elt) {
            switch(get_class($elt)) {
            case 'PHP_UML_Metamodel_Interface':
                $str .= $this->getInterface($elt);
                break;
            case 'PHP_UML_Metamodel_Datatype':
                $str .= $this->getDatatype($elt);
                break;
            case 'PHP_UML_Metamodel_Artifact':
                $str .= $this->getArtifact($elt, $elt->manifested);
                break;
            default:
                $str .= $this->getClass($elt);
            }
        }
        // we will finally not use this since it leads to unvalid XMI:
        /*foreach ($package->ownedOperation as &$elt) {
            $str .= $this->getOperation($elt);
        }
        foreach ($package->ownedAttribute as &$elt) {
            $str .= $this->getProperty($elt);
        }*/
        return $str;
    }

    /**
     * Get all components, with its provided classes
     * PHP_UML considers each logical package as a component, and each owned class
     * as a provided class.
     *
     * @param PHP_UML_Metamodel_Package $package Package to map to a component
     * 
     * @return string XMI code
     */
    protected function getAllComponents(PHP_UML_Metamodel_Package $package)
    {
        $str      = '';
        $cv       = new PHP_UML_Metamodel_Package;
        $cv->id   = self::getUID();
        $cv->name = $package->name;

        $classes = array();
        foreach ($package->ownedType as &$elt) {
            $classes[] = $elt;
        }
        $str .= $this->getComponentOpen($cv, $classes, array());

        foreach ($package->nestedPackage as $pkg)
            $str .= $this->getAllComponents($pkg);

        $str .= $this->getComponentClose();
        return $str;
    }
    
    /**
     * Return the full serialized XMI metamodel of a given Superstructure.
     *
     * @param PHP_UML_Metamodel_Superstructure $structure Full metamodel
     * 
     * @return PHP_UML_Output_XmiDocument The serialized metamodel, as an XmiDocument
     *
     */
    public function getXmiDocument(PHP_UML_Metamodel_Superstructure $structure)
    {
        if (empty($structure) || empty($structure->packages)) {
            throw new PHP_UML_Exception('No model given');
        }
        
        $xmi  = $this->getXmlHeader();
        $xmi .= $this->getXmiHeaderOpen();

        $_root = $structure->packages;

        $xmi .= $this->getModelOpen($_root);
        $xmi .= $this->getNamespaceOpen();
 
        if ($this->logicalView) {
            $xmi .= $this->addLogicalView($_root);
        }

        if ($this->componentView) {
            $xmi .= $this->addComponentView($_root);
        }

        if ($this->deploymentView) {
            $xmi .= $this->addDeploymentView($structure->deploymentPackages);
        }

        $xmi .= $this->getNamespaceClose();
        $xmi .= $this->getModelClose();

        if ($this->stereotypes) {    // = XML metadata only for the moment
            $xmi .= $this->addStereotypeInstances($structure);
        }

        $xmi .= $this->getXmiHeaderClose();

        if (strcasecmp($this->encoding, 'utf-8')==0) {
            $xmi = utf8_encode($xmi);
        }
        
        $xmiDocument = new PHP_UML_Output_XmiDocument($xmi);
        
        return $xmiDocument;
    }
       
    /**
     * Return the logical view of the model
     *
     * @param PHP_UML_Metamodel_Package $package Package
     */
    private function addLogicalView(PHP_UML_Metamodel_Package $package)
    {
        $xmi = $this->getStereotypes();

        $xmi .= $this->getOwnedElements($package);
        foreach ($package->nestedPackage as $pkg)
            $xmi .= $this->getAllPackages($pkg, false);
            
        return $xmi;
    }
    
    /**
     * Return a component view of the logical system
     *
     * @param PHP_UML_Metamodel_Package $package Root package to browse into
     */
    private function addComponentView(PHP_UML_Metamodel_Package $package)
    {
        return $this->getAllComponents($package);
    }

    /**
     * Return a deployment view of the scanned files.
     * A file is viewed as an artifact (artifacts exist from UML 1.4)
     * Filesystem's folders are treated as packages.
     * TODO: use a package-tree, like with logical packages 
     *
     * @param PHP_UML_Metamodel_Package $package The root deployment package
     */
    private function addDeploymentView(PHP_UML_Metamodel_Package $package)
    {
        return $this->getAllPackages($package);
    }

    /**
     * Return the instances of stereotypes
     * At the current time, there are only XML metadata, not real UML stereotypes
     *
     * @param PHP_UML_Metamodel_Superstructure $structure Metamodel
     */
    private function addStereotypeInstances(PHP_UML_Metamodel_Superstructure $structure)
    {
        $xmi = '';
        foreach ($structure->stereotypes as $s) {
            $xmi .= $this->getStereotypeInstance($s);
        }
        return $xmi;
    }
}
?>
