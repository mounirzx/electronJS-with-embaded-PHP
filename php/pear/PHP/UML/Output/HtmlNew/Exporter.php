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
 * This class generates an HTML website from a UML model (a PHP_UML_Metamodel)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage HtmlNew
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_HtmlNew_Exporter extends PHP_UML_Output_ExporterAPI
{
    private $docClass;
    private $docInterface;
    private $docDatatype;
    private $docPackage;
    private $docMenu;
    private $docIndex;
    private $docResources;

    public function export($outDir)
    {
        $userCurrentDir = getcwd();
        
        if (substr($outDir, -1) != DIRECTORY_SEPARATOR)
            $outDir .= DIRECTORY_SEPARATOR;
        parent::export($outDir);
        
        $this->ctx = new PHP_UML_Output_ApiContextPackage();

        $this->docClass     = new PHP_UML_Output_HtmlNew_DocClass($this);
        $this->docInterface = new PHP_UML_Output_HtmlNew_DocInterface($this);
        $this->docDatatype  = new PHP_UML_Output_HtmlNew_DocDatatype($this);
        $this->docPackage   = new PHP_UML_Output_HtmlNew_DocPackage($this);
        $this->docMenu      = new PHP_UML_Output_HtmlNew_DocMenu($this);
        $this->docIndex     = new PHP_UML_Output_HtmlNew_DocIndex($this);
        $this->docResources = new PHP_UML_Output_HtmlNew_DocResources($this);
        
        chdir($outDir);
        
        $this->docResources->render($this->structure->packages);
        $this->docMenu->render($this->structure->packages);
        $this->docIndex->render($this->structure->packages);
        // we analyse the inheritance/impl relations beforehand:
        $this->setAllSuperClassifiers($this->structure->packages);
        
        $this->treatPackage($this->structure->packages);
        
        chdir($userCurrentDir);
    }

    /**
     * Recurses into the packages, and generates the detailed file (one file
     * per class/interface/package)
     * 
     * @param PHP_UML_Metamodel_Package $pkg Starting package
     * @param string                    $dir Filepath leading to the current package
     * @param string                    $rpt Relative filepath to the top package
     */
    private function treatPackage(PHP_UML_Metamodel_Package $pkg, $dir='', $rpt='')
    {
        $this->initContextPackage($pkg, $dir, $rpt);

        $this->docPackage->render($pkg, $this->ctx);
        
        $nbc = count($this->ctx->classes);
        $nbi = count($this->ctx->interfaces);
        $nbd = count($this->ctx->datatypes);

        for ($i=0; $i<$nbc; $i++)
            $this->docClass->render($i);
 
        for ($i=0; $i<$nbi; $i++)
            $this->docInterface->render($i);

        for ($i=0; $i<$nbd; $i++)
            $this->docDatatype->render($i);

        foreach ($pkg->nestedPackage as $np) {
            $npDir = $dir.$np->name;
            if (!file_exists($npDir))
                mkdir($npDir);
            $this->treatPackage($np, $npDir.DIRECTORY_SEPARATOR, '../'.$rpt);
        }
    }
}
?>
