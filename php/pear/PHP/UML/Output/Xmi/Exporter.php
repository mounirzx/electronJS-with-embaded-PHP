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
 * @version  SVN: $Revision: 176 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-19 00:03:11 +0200 (lun., 19 sept. 2011) $
 */

/**
 * This class generates the XMI from a UML model (PHP_UML_Metamodel)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Xmi
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Xmi_Exporter extends PHP_UML_Output_ExporterAPI
{    
    /**
     * Reference to an XmiDocument object. Such an object is needed by the export()
     * method of Xmi_Exporter and its subclasses (unlike the ExporterAPI implementations,
     * which work on the metamodel directly, and don't need to know anything about XMI).
     * 
     * @var PHP_UML_Output_XmiDocument
     */
    protected $xmiDocument;
    
    /**
     * Default XMI version used for serialization. Note that all XSL-based
     * exporters will generate XMI with the default xmiVersion and encoding.
     * 
     * @var float
     */
    protected $xmiVersion = 2;
    
    /**
     * Default XML encoding of the XMI document.
     * 
     * @var string
     */
    protected $encoding = 'iso-8859-1';
    
    /**
     * A logical view is included in the model by default.
     * 
     * @var boolean
     */
    protected $logicalView = true;
    
    /**
     * A deployment view is not included in the model by default.
     * 
     * @var boolean
     */
    protected $deploymentView = false;
    
    /**
     * A component view is not included in the model by default.
     * 
     * @var boolean
     */
    protected $componentView = false;
    
    /**
     * Stereotypes are not included in the model by default.
     * 
     * @var boolean
     */
    protected $stereotypes = false;
    
    
    /**
     * Setter for the XML encoding
     *
     * @param string $encoding Encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
    
    /**
     * Setter for the XMI version
     *
     * @param float $version XMI version
     */
    public function setXmiVersion($version)
    {
        $this->xmiVersion = $version;
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
     * If true, all the stereotypes related to the UML model (currently, these
     * data correspond to the PHP docblocks) will be added to the XMI model.
     *
     * @param boolean $value True/False
     */
    public function setStereotypes($value)
    {
        $this->stereotypes = $value;
    }
    
    /**
     * Setter for the XmiDocument
     * 
     * @param PHP_UML_Output_XmiDocument $xmiDocument XMI Document
     */
    public function setXmiDocument(PHP_UML_Output_XmiDocument $xmiDocument)
    {
        $this->xmiDocument = $xmiDocument;
    }
    
    /**
     * Getter for the XmiDocument
     * 
     * @return PHP_UML_Output_XmiDocument
     */
    public function getXmiDocument()
    {
        return $this->xmiDocument;
    }
    
    public function export($outDir)
    {
        if (empty($this->structure))
            throw new PHP_UML_Exception('No model given.'); 
        
        //if (empty($this->xmiDocument)) {
        $this->generateXmi();
        //}

        if ($outDir=='')
            return $this->xmiDocument->dump();
        else {
            if (!is_dir($outDir))
                $this->save($outDir);
            else
            {
                $fileName = empty($this->structure->packages) ? 'undefined' : $this->structure->packages->name;
                $this->save($outDir.DIRECTORY_SEPARATOR.$fileName.'.xmi');
            }
        }
    }

    /**
     * Internal method to save the generated XMI to a file.
     * Use export() instead if you need to save from outside the Exporter object.
     *
     * @param string $outputFile Filename
     */
    protected function save($outputFile)
    {        
        if ($ptr = fopen($outputFile, 'w+')) {
            fwrite($ptr, $this->xmiDocument->dump());
            fclose($ptr);
        } else {
            throw new PHP_UML_Exception(
                'File '.$outputFile.' could not be created.'
            );
        }
    }

    
    /**
     * Serialize the metamodel (contained in the property $structure) into XMI code
     * 
     * It is not necessary to call this method before calling export();
     */
    public function generateXmi()
    {
        $builder = $this->getXmiBuilder();
        
        $this->xmiDocument = $builder->getXmiDocument($this->structure);
    }
    
    /**
     * Getter for the XMI factory
     * 
     * @return PHP_UML_Output_Xmi_Builder An XMI builder object
     */
    protected function getXmiBuilder()
    {
        $builder = PHP_UML_Output_Xmi_AbstractBuilder::getInstance($this->xmiVersion);
        $builder->setEncoding($this->encoding);
        $builder->setLogicalView($this->logicalView);
        $builder->setComponentView($this->componentView);
        $builder->setDeploymentView($this->deploymentView);
        $builder->setStereotypes($this->stereotypes);
        return $builder;
    }
    

    /**
     * Read the content of an existing XMI file.
     * If the file is UML/XMI 1, a conversion to version 2 is automatically applied.
     *
     * @param string $filepath Filename
     */
    public function loadXmi($filepath)
    {
        if (empty($this->xmiDocument)) {
            $this->xmiDocument = new PHP_UML_Output_XmiDocument();
        }
        $this->xmiDocument->load($filepath);
    }
}
?>
