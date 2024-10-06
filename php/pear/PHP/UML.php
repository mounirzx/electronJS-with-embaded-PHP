<?php
/**
 * PHP Parser and UML/XMI generator. Reverse-engineering tool.
 *
 * A package to scan PHP files and directories, and get an UML/XMI representation
 * of the parsed classes/packages.
 * The XMI code can then be imported into a UML designer tool, like Rational Rose
 * or ArgoUML.
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

require_once 'PEAR/Exception.php';

spl_autoload_register(array('PHP_UML', 'autoload'));


/**
 * Facade to use, through its methods:
 * - the setInput() method to set the files and/or directories to parse
 * - the parse('name') method to start parsing, and building the model
 * - the helper method export('format', 'location') to export the model
 * 
 * For example:
 * <code>
 * $t = new PHP_UML();
 * $t->setInput('PHP_UML/');
 * $t->export('xmi', '/home/wwww/');
 * </code>
 * 
 * If you want to produce XMI without using the PHP parser, please refer to
 * the file /examples/test_with_api.php; it will show how you can build a
 * model by yourself, with the PHP_UML_Metamodel package.
 * 
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @see      PHP_UML_Metamodel_Superstructure
 * 
 */
class PHP_UML
{
    /**
     * Character used to separate the patterns passed to setIgnorePattern() and
     * setMatchPattern().
     * @var string
     */
    const PATTERN_SEPARATOR = ',';

    /**
     * If true, a UML logical view is created.
     * @var boolean
     */
    public $logicalView = true;

    /**
     * If true, a UML deployment view is created.
     * Each file produces an artifact.
     * @var boolean
     */
    public $deploymentView = true;

    /**
     * If true, a component view is created.
     * file system. Each file produces an component
     * @var boolean
     */
    public $componentView = false;

    /**
     * If true, the docblocks content is parsed.
     * All possible information is retrieved : general comments, @package, @param...
     * @var boolean
     */
    public $docblocks = true;

    /**
     * If true, the elements (class, function) are included in the API only if their
     * comments contain explicitly a docblock "@api"
     * @var boolean
     */
    public $onlyApi = false;

    /**
     * If true, only classes and namespaces are retrieved. If false, procedural
     * functions and constants are also included
     */
    public $pureObject = false;
    
    /**
     * If true, the empty namespaces (inc. no classes nor interfaces) are ignored
     * @var boolean
     */
    public $removeEmptyNamespaces = true;

    /**
     * If true, the elements marked with @internal are included in the API.
     * @var boolean
     */
    public $showInternal = false;

    /**
     * If true, the PHP variable prefix $ is kept
     * @var boolean
     */
    public $dollar = true;

    /**
     * A reference to a UML model
     * @var PHP_UML::Metamodel::PHP_UML_Metamodel_Superstructure
     */
    private $model;

    /**
     * List of directories to scan
     * @var array
     */
    private $directories = array();
    
    /**
     * List of files to scan
     * @var array
     */
    private $files = array();

    /**
     * Allowed filenames (possible wildcards are ? and *)
     * 
     * @var array
     */
    private $matchPatterns = array('*.php');

    /**
     * Ignored directories (possible wildcards are ? and *)
     *
     * @var array();
     */
    private $ignorePatterns = array();

    /**
     * Current exporter object.
     * 
     * @var PHP_UML_Output_Exporter
     */
    private $exporter;
    
    /**
     * Current importer object.
     * 
     * @var PHP_UML_Input_ImporterFileScanner
     */
    private $importer;
    
    /**
     * Constructor.
     * 
     * Creates an empty model and holds a reference to it.
     *
     */
    public function __construct()
    {
        $this->model    = new PHP_UML_Metamodel_Superstructure;
        $this->importer = new PHP_UML_Input_PHP_FileScanner($this->model);
        //$this->importer->setModel($this->model);
    }

    /**
     * Parse a PHP file, and return a PHP_UML_Metamodel_Superstructure object
     * (= a UML model) corresponding to what has been found in the file.
     *
     * @param mixed  $files File(s) to parse. Can be a single file,
     *                      or an array of files.
     * @param string $name  A name for the model to generate
     * 
     * @deprecated Use setInput() instead
     * 
     * @return PHP_UML_Metamodel_Superstructure The resulting UML model
     */
    public function parseFile($files, $name = 'default')
    {
        $this->setInput($files);
        return $this->parse($name);
    }


    /**
     * Set the input elements (files and/or directories) to parse
     *
     * @param mixed $pathes Array, or string of comma-separated-values
     */
    public function setInput($pathes)
    {
        if (!is_array($pathes)) {
            $pathes = explode(self::PATTERN_SEPARATOR, $pathes);
            $pathes = array_map('trim', $pathes);
        }

        foreach ($pathes as $path) {
            if (is_file($path)) {
                $this->files[] = $path;
            }
            elseif (is_dir($path))
                $this->directories[] = $path;
            else
                throw new PHP_UML_Exception($path.': unknown file or folder');
        }    
    }

    /**
     * Setter for the FileScanner used for the parsing. Automatically
     * sets the importer's model with the model owned by PHP_UML 
     * 
     * @param PHP_UML_Input_ImporterFileScanner $importer FileScanner to be used
     */
    public function setImporter(PHP_UML_Input_ImporterFileScanner $importer)
    {
        $this->importer = $importer;
        $this->importer->setModel($this->model);
    }
    
    
    /**
     * Setter for the filename patterns.
     * Usage: $phpuml->setFilePatterns(array('*.php', '*.php5'));
     * Or:    $phpuml->setFilePatterns('*.php, *.php5');
     *
     * @param mixed $patterns List of patterns (string or array)
     */
    public function setMatchPatterns($patterns)
    {
        if (is_array($patterns)) {
            $this->matchPatterns = $patterns;
        } else {
            $this->matchPatterns = explode(self::PATTERN_SEPARATOR, $patterns);
            $this->matchPatterns = array_map('trim', $this->matchPatterns);
        }
    }

    /**
     * Set a list of files / directories to ignore during parsing
     * Usage: $phpuml->setIgnorePatterns(array('examples', '.svn'));
     * Or:    $phpuml->setIgnorePatterns('examples .svn');
     * 
     * @param mixed $patterns List of patterns (string or array)
     */
    public function setIgnorePatterns($patterns)
    {
        if (is_array($patterns)) {
            $this->ignorePatterns = $patterns;
        } else {
            $this->ignorePatterns = explode(self::PATTERN_SEPARATOR, $patterns);
        }
        $this->ignorePatterns = array_map(array('self', 'cleanPattern'), $this->ignorePatterns);
    }

    /**
     * Converts a path pattern to the format expected by FileScanner
     * (separator can only be / ; must not start by any separator)
     *
     * @param string $p Pattern
     * 
     * @return string Pattern converted
     * 
     * @see PHP_UML_FilePatternFilterIterator#accept()
     */
    private static function cleanPattern($p)
    {
        $p = str_replace('/', DIRECTORY_SEPARATOR, trim($p));
        if ($p[0]==DIRECTORY_SEPARATOR)
            $p = substr($p, 1);
        return $p;
    }

    /**
     * Set the packages to include in the XMI code
     * By default, ALL packages found will be included.
     *
     * @param mixed $packages List of packages (string or array)
     * TODO
     
    public function setPackages($packages)
    {
        if (is_array($patterns)) {
            $this->packages = $patterns;
        }
        else {
            $this->packages = explode(self::PATTERN_SEPARATOR, $patterns);
            $this->packages = array_map('trim', $this->packages);
        }  
    }
    */


    /**
     * Parse a PHP folder, and return a PHP_UML_Metamodel_Superstructure object
     * (= a UML model) corresponding to what has been parsed.
     *
     * @param mixed  $directories Directory path(es). Can be a single path,
     *                            or an array of pathes.
     * @param string $modelName   A name for the model to generate
     * 
     * @deprecated Use setInput() instead
     * 
     * @return PHP_UML_Metamodel_Superstructure The resulting UML model
     */
    public function parseDirectory($directories, $modelName = 'default')
    {
        $this->setInput($directories);
        return $this->parse($modelName);
    }

    /**
     * Parse the directories and the files (depending on what the $directories
     * and $files properties have been set to with setInput()) and return a
     * UML model.
     *
     * @param string $modelName A model name (e.g., the name of your application)
     * 
     * @return PHP_UML_Metamodel_Superstructure The resulting UML model
     */
    public function parse($modelName = 'default')
    {
        $this->model->initModel($modelName);

        if ($this->importer instanceof PHP_UML_Input_PHP_FileScanner)
            $this->setInputPhpParserOptions();
        
        $this->importer->setFiles($this->files);
        $this->importer->setDirectories($this->directories);
        $this->importer->setMatchPatterns($this->matchPatterns);
        $this->importer->setIgnorePatterns($this->ignorePatterns);

        $this->importer->import();

        if ($this->removeEmptyNamespaces)
            PHP_UML_Metamodel_Helper::deleteEmptyPackages($this->model->packages);
                
        return $this->model;
    }

    private function setInputPhpParserOptions()
    {
        $options = new PHP_UML_Input_PHP_ParserOptions();
        
        $options->keepDocblocks = $this->docblocks;
        $options->keepDollar    = $this->dollar;
        $options->skipInternal  = (!$this->showInternal);
        $options->onlyApi       = $this->onlyApi;
        $options->strict        = $this->pureObject;
        
        $this->importer->setParserOptions($options);
    }
    
    /**
     * Update an instance of Xmi_Exporter with the current output settings
     * 
     * @param PHP_UML_Output_Xmi_Exporter $e Exporter object to update
     */
    private function setOutputXmiOptions(PHP_UML_Output_Xmi_Exporter $e)
    {
        $e->setLogicalView($this->logicalView);
        $e->setComponentView($this->componentView);
        $e->setDeploymentView($this->deploymentView);
        $e->setStereotypes($this->docblocks);
    }
    
 
    /**
     * Convert the UML model (stored in the object) into some output data.
     * 
     * @param string $format    Desired format ("xmi", "html", "php"...)
     * @param string $outputDir Output directory
     */
    public function export($format='xmi', $outputDir='.')
    {
        if (empty($outputDir)) {
            throw new PHP_UML_Exception('No output folder given.');
        }

        if (empty($this->model) || empty($this->model->packages)) {
            throw new PHP_UML_Exception('No model given.');
        }
        
        $this->exporter = PHP_UML_Output_Exporter::getInstance($format);
        $this->exporter->setModel($this->model);
        
        return $this->exporter->export($outputDir);
    }
    
    /**
     * Public accessor to the metamodel.
     *
     * @return PHP_UML_Metamodel_Superstructure Model generated during PHP parsing
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the exporter to use (an Output_Xmi_Exporter is already set by default)
     *
     * @param PHP_UML_Output_Exporter $exporter The exporter object to use
     */
    public function setExporter(PHP_UML_Output_Exporter $exporter)
    {
        $this->exporter = $exporter;
        $this->exporter->setModel($this->model);
    }

    /**
     * Autoloader
     *
     * @param string $class Class name
     */
    static function autoload($class)
    {
        if (substr($class, 0, 7)=='PHP_UML') {
            $path = 'UML'.str_replace('_', '/', substr($class, 7).'.php');
            require $path;
        }
    }
}
?>