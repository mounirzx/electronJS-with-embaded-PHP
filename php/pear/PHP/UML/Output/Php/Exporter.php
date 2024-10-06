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
 * @version  SVN: $Revision: 167 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2011-09-08 02:23:25 +0200 (jeu., 08 sept. 2011) $
 */

/**
 * This class generates a HTML website from a UML model (a PHP_UML_Metamodel)
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @subpackage Php
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Output_Php_Exporter extends PHP_UML_Output_ExporterAPI
{
    private $docClass;
    private $docInterface;

    private $docblocks = true;
    
    /**
     * Option to add time/id information as comments
     * @param boolean $value
     */
    /*public function setStamp($value)
    {
        $this->stamp = $value;
    }
    
    public function getStamp()
    {
        return $this->stamp;
    }*/
    
    /**
     * Option to add docblocks
     * 
     * @param boolean $value Set to true to include docblocks
     */
    public function setDocblocks($value)
    {
        $this->docblocks = $value;
    }
    
    public function getDocblocks()
    {
        return $this->docblocks;
    }
    
    public function export($outDir)
    {
        $userCurrentDir = getcwd();
        
        if (substr($outDir, -1) != DIRECTORY_SEPARATOR)
            $outDir .= DIRECTORY_SEPARATOR;
        parent::export($outDir);
        
        $this->ctx = new PHP_UML_Output_ApiContextPackage();

        $this->docClass     = new PHP_UML_Output_Php_DocClass($this);
        $this->docInterface = new PHP_UML_Output_Php_DocInterface($this);

        chdir($outDir);
        
        // we analyse the inheritance/impl relations beforehand:
        $this->setAllSuperClassifiers($this->structure->packages);
        $this->treatPackage($this->structure->packages);
        
        chdir($userCurrentDir);
    }
    
    /**
     * Recurses into the packages, and generates the detailed files (one file
     * per class/interface/package)
     * 
     * @param PHP_UML_Metamodel_Package $pkg Starting package
     * @param string                    $dir Filepath leading to the current package
     * @param string                    $rpt Relative filepath to the top package
     */
    private function treatPackage(PHP_UML_Metamodel_Package $pkg, $dir='', $rpt='')
    {
        $this->initContextPackage($pkg, $dir, $rpt);
 
        $nbc = count($this->ctx->classes);
        $nbi = count($this->ctx->interfaces);

        for ($i=0; $i<$nbc; $i++)
            $this->docClass->render($i);
 
        for ($i=0; $i<$nbi; $i++)
            $this->docInterface->render($i);

        foreach ($pkg->nestedPackage as $np) {
            $npDir = $dir.$np->name;
            if (!file_exists($npDir))
                mkdir($npDir);
            $this->treatPackage($np, $npDir.DIRECTORY_SEPARATOR, '../'.$rpt);
        }
    }
}
?>
