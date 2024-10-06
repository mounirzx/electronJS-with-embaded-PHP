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
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2011-09-19 00:03:11 +0200 (lun., 19 sept. 2011) $
 */

/**
 * An XMI implementation of an ImporterFileScanner.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage XMI
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * 
 */
class PHP_UML_Input_XMI_FileScanner extends PHP_UML_Input_ImporterFileScanner
{
    public function import()
    {
        parent::scan();
        
        $this->model->addInternalPhpTypes();
        $resolver = PHP_UML_Input_XMI_Builder::getResolver();
        $resolver->resolve($this->model->packages, array($this->model->packages));
    }
    
    /**
     * Implementation of tickFile(): we update the model with the information
     * found in the XMI file. 
     * 
     * @param string $fileBase Directory path
     * @param string $filePath File name
     *
     * @see PHP_UML/UML/FileScanner#tickFile()
     */
    public function tickFile($fileBase, $filePath)
    {
        $filename = $fileBase.$filePath;
        
        if (!(file_exists($filename) && is_readable($filename))) {
            throw new PHP_UML_Exception('Could not read '.$filename.'.');
        }
        $doc = new DOMDocument();
        if (!$doc->load($filename))
            return;
        
        if (PHP_UML_Output_XmiDocument::isVersion1($doc)) {
            $xmi = PHP_UML_Output_ExporterXSL::convertTo2($doc->saveXML());
            $doc->loadXML($xmi);
        }
        
        $builder = new PHP_UML_Input_XMI_Builder();
        $builder->setModel($this->model);
        $builder->buildDom($doc);
        $this->model = $builder->getModel();
    }
}
?>
