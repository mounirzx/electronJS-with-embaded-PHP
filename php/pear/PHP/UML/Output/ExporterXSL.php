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
 * An exportation class, that relies on XSL transformations.
 * 
 * It does its export by transforming the XMI data contained in the object
 * $xmiDocument.
 * Various transformations are available: they are all stored in separate subfolders
 * (php, html). To get an ExporterXSL for a given format, call the constructor
 * with the name of the format as first parameter:
 * new PHP_UML_Output_ExporterXSL("php")
 * This format name must be equal to the folder name where the desired XSL files are.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Output
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link       http://pear.php.net/package/PHP_UML
 */
abstract class PHP_UML_Output_ExporterXSL extends PHP_UML_Output_Exporter
{
    const RESOURCES_FOLDER = 'resources';
    const STARTING_TPL     = 'main.xsl';
   
    protected $saveToFileCallback;
    
    protected $createFolderCallback;
    
    /**
     * XmiDocument object containing the XMI to transform
     * 
     * @var PHP_UML_Output_XmiDocument
     */
    protected $xmiDocument;
    
   
    public function __construct()
    {
        parent::__construct();
        $this->saveToFileCallback   = __CLASS__.'::saveToFile';
        $this->createFolderCallback = __CLASS__.'::createFolder';
    }
    
    public function setSaveToFileCallback($function)
    {
        $this->saveToFileCallback = $function;
    }
    
    public function setCreateFolderCallback($function)
    {
        $this->createFolderCallback = $function;
    }
    
    /**
     * Generates output data by applying a transformation on a given XMI file
     *
     * @param string $outputDir Output directory
     * @param string $xslFile   XSL file (template file)
     * @param string $xmlFile   XMI file  
     */
    protected function exportFromFile($outputDir, $xslFile, $xmlFile)
    {
        if (is_file($xmlFile) && is_readable($xmlFile)) {
            $xmlDom = new DomDocument;
            $xmlDom->load($xmlFile);
            return $this->transform($outputDir, $xslFile, $xmlDom);
        } else {
            throw new PHP_UML_Exception(
                'Could not read file '.$xmlFile
            );
        }
    }
    
    /**
     * Generates ouput data by applying a transformation on some provided XMI
     *
     * @param string $outputDir Output directory
     * @param string $xslFile   XSL file (template file)
     * @param string $xmlData   The XMI data
     */
    protected function exportFromXml($outputDir, $xslFile, $xmlData)
    {
        if (empty($xmlData))
            throw new PHP_UML_Exception(
                'No XMI data was available for transformation.'
            );
        $xmlDom = new DomDocument;
        $xmlDom->loadXML($xmlData);
        return $this->transform($outputDir, $xslFile, $xmlDom);
    }
    
    /**
     * Return the absolute location of the XSL file
     * 
     * @return string Filepath
     */
    abstract function getXslFilepath();
    
    /**
     * Generates output data by applying a transformation on the XMI stored in the
     * property $xmi
     *
     * @param string $outputDir Output folder
     * 
     * @return DOMDocument A DOM document containing the result of the XSLT
     */
    public function export($outputDir)
    {
        $xslFile = $this->getXslFilepath();

        if (empty($this->xmiDocument)) {
            
            $temp = new PHP_UML_Output_Xmi_Exporter();
            /*
             * Component views and deployment views don't mean anything
             * for output formats other than XMI. Yet, since ExporterXSL is
             * a subclass of ExportXMI, we must force these options to
             * false, otherwise they will appear in the output produced by 
             * the XSL transformation:
             */
            $temp->setComponentView(false);
            $temp->setDeploymentView(false);
            $temp->setModel($this->structure);
            $temp->generateXmi();
            $this->xmiDocument = $temp->getXmiDocument();
        }
             
        if (file_exists($xslFile))
            return $this->exportFromXml($outputDir, $xslFile, $this->xmiDocument->dump());
        else {
            throw new PHP_UML_Exception(
                'Could not find the XSL template file. It must be named '.
                self::STARTING_TPL.', under Format/YourTemplate/'
            );
        }
    }
    
    /**
     * Generates output data by applying a transformation on a Dom Document
     *
     * @param string      $outputDir Output folder
     * @param string      $xslFile   XSL file (template file)
     * @param DomDocument $xmlDom    XMI data (Dom)
     *
     * @return string (possible) messages raised during XSLT
     */
    private function transform($outputDir, $xslFile, DomDocument $xmlDom)
    {
        clearstatcache();

        $userCurrentDir = getcwd();    // we memorize the current working dir
        chdir(dirname(__FILE__));      // ... swap to the /Output dir  
        if ($xslFile=='')
            $xslFile = 'html/'.self::STARTING_TPL;
        
        if (!(file_exists($xslFile) && is_readable($xslFile)))
            throw new PHP_UML_Exception(
                'Could not read file '.$xslFile
            );    
        $xslDom = new DomDocument;
        $xslDom->load($xslFile);       // ... load the XSLT starting file
        
        chdir($userCurrentDir);        // ... and change back to the user dir
 
        // so that we can check the output dir (counted from the user dir)
        if ($outputDir=='')
            throw new PHP_UML_Exception(
                'No output directory was given.'
            );
        if (!file_exists($outputDir))
            throw new PHP_UML_Exception(
                'Directory '.$outputDir.' does not exist.'
            );
        chdir($outputDir);

        self::copyResources(dirname($xslFile));

        $xslProc = new XSLTProcessor;
        $xslProc->registerPHPFunctions();
        $xslProc->importStylesheet($xslDom);

        $xslProc->setParameter('', 'phpSaveToFile', $this->saveToFileCallback);
        $xslProc->setParameter('', 'phpCreateFolder', $this->createFolderCallback);
        $xslProc->setParameter('', 'appName', self::APP_NAME);
        $xslProc->setParameter('', 'genDate', date('r'));

        $out = $xslProc->transformToDoc($xmlDom);

        chdir($userCurrentDir); // ... and back again to the original user dir
        
        return $out;
    }

    const XMLNS_UML1 = 'http://www.omg.org/spec/UML/1.4';

    /**
     * XMI converter
     * 
     * @param string $xmi XMI (in version 1)
     * 
     * @return string XMI (in version 2)
     */
    static public function convertTo2($xmi)
    {
        return self::simpleTransform('xmi1to2.xsl', $xmi);
    }
    
    /**
     * Applies a simple transformation on XML data
     * Used by the UML 1->2 conversion
     *
     * @param string $xsl XSL file
     * @param string $xmi XML data
     *
     * @return string XML
     */
    static private function simpleTransform($xsl, $xmi)
    {   
        //we must set the xmlns:UML to the same value as the XSLT stylesheet
        //(otherwise transfomations don't work)
        $xmi = preg_replace('/xmlns:(UML)\s*=\s*["\'](.*)["\']/i', 'xmlns:$1="'.self::XMLNS_UML1.'"', $xmi, 1);

        $xslDom = new DomDocument;
        $xslDom->load(dirname(__FILE__).DIRECTORY_SEPARATOR.$xsl);

        $xmlDom = new DomDocument;
        $xmlDom->loadXML($xmi);
 
        /* $xmiTag = &$xmlDom->getElementsByTagName('XMI')->item(0);
        if ($xmiTag->getAttribute('xmlns:UML') != '') {
            $xmlDom->getElementsByTagName('XMI')->item(0)->setAttribute('verified', 'http://www.omg.org/spec/UML/1.4');
        } */

        $xslProc = new XSLTProcessor;
        $xslProc->importStylesheet($xslDom);

        $xslProc->setParameter('', 'appName', self::APP_NAME);

        return $xslProc->transformToXML($xmlDom);
    }
    
    /**
     * Copy the "resources" folder
     *
     * @param string $path Path to the folder that contains the XSL templates
     */
    static private function copyResources($path)
    {
        $dir = $path.DIRECTORY_SEPARATOR.self::RESOURCES_FOLDER;

        if (file_exists($dir)) {
            $iterator = new DirectoryIterator($dir);
            foreach ($iterator as $file) {
                if($file->isFile())
                    copy($file->getPathname(), $file->getFilename());
            }
        }
    }


    /**
     * Create a folder. This is a callback function for the XSL templates
     * (that's why it is public), and you should not have to use it.
     *
     * @param string $path Folder name
     */
    static public function createFolder($path)
    {   
        if (substr(getcwd(), -1)==DIRECTORY_SEPARATOR)
            $k = getcwd().utf8_decode($path);
        else
            $k = getcwd().DIRECTORY_SEPARATOR.utf8_decode($path);
        if (!file_exists($k)) {
            mkdir($k);
        }
        chdir($k);
    }
   
    /**
     * Save a content to a file. This is a callback function for the XSL templates
     * (that's why it is public), and you should not have to use it.
     *
     * @param string $name    File name
     * @param mixed  $content Content (can be either a string, or a node-set)
     */
    static public function saveToFile($name, $content)
    {
        $file = fopen(utf8_decode($name), 'w');
 
        if (is_string($content)) {
            fwrite($file, utf8_decode($content));
        } else {
            $dom  = new DomDocument();
            $node = $dom->importNode($content[0], true);
            $dom->appendChild($node);
    
            fwrite($file, $dom->saveHTML());
        }
        fclose($file);
    }
}

?>
