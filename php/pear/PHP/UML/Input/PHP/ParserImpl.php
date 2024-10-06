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
 * An implementation of PHP parser.
 * 
 * It relies on the PHP instruction token_get_all().
 * Most navigabilities between associated elements are bidirectional
 * (the packages know their owned elements, and the classes know their
 * nesting package)
 * In a first step, relations (extends, implements) use strings. It means that
 * the namespace of a class is memorized through a string.
 * Once the parsing is done, a Resolver implementation must be used,
 * so that all the named references be replaced by PHP references.
 *
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage PHP
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
class PHP_UML_Input_PHP_ParserImpl extends PHP_UML_Input_PHP_Parser
{
    /**
     * Reference to a PHP_UML_Metamodel_Superstructure
     * (where the parser stores all the program elements it finds)
     * @var PHP_UML_Metamodel_Superstructure
     */
    private $structure;

    /**
     * Current PHP_UML_Metamodel_Artifact
     * @var PHP_UML_Metamodel_Artifact
     */
    private $file;

    /**
     * If true, all docblocks are interpreted, especially @package and the types of
     * the properties/function parameters (if given).
     * @var bool
     */
    private $docblocks;

    /**
     * If true, the elements (class, function) are included in the API only if their
     * comments contain explicitly a docblock "@api"
     * @var bool
     */
    private $onlyApi;
    
    /**
     * If true, the symbol $ is kept along with the variable names
     * @var bool
     */
    private $keepDollar;

    /**
     * If true, elements marked with @internal are skipped
     * @var bool
     */
    private $skipInternal;

    /**
     * Current package index (which does not necessary match the last one put
     * over $packages stack). This index refers to the array $structure->packages
     * @var PHP_UML_Metamodel_Package
     */
    private $currentPackage;

    /**
     * Current docblock comment
     * @var string
     */
    private $curDocComment;

    /**
     * Current class features (abstract, final)
     * @var array Array of tokens
     */
    private $classFeatures = array();

    /**
     * Current element (property or function) features (abstract, public, static...)
     * @var array Array of tokens
     */
    private $classElementFeatures = array();

    /**
     * Current namespace, as defined by the PHP "namespace" instruction
     * @var string
     */
    private $currentQn = '';

    /**
     * PHP namespace aliases ("use <value> as <key>")
     * @var array Associative array alias => namespace
     */
    private $aliases = array();

    /**
     * Strict object oriented mode
     * @var bool
     */
    private $pureObj = false;
    

    public function __construct(PHP_UML_Metamodel_Superstructure &$struct, PHP_UML_Input_PHP_ParserOptions $options=null)
    {
        $this->structure = $struct;

        if (!isset($options))
            $options = new PHP_UML_Input_PHP_ParserOptions();
        
        $this->docblocks    = $options->keepDocblocks;
        $this->keepDollar   = $options->keepDollar;
        $this->skipInternal = $options->skipInternal;
        $this->onlyApi      = $options->onlyApi;
        $this->pureObj      = $options->strict;
 
        if (!defined('T_NAMESPACE'))
            define('T_NAMESPACE', -1);
        if (!defined('T_NS_SEPARATOR'))
            define('T_NS_SEPARATOR', -2);
    }

    public function setModel(PHP_UML_Metamodel_Superstructure $model)
    {
        $this->structure = $model;
    }
    
    public function getModel()
    {
        return $this->structure;
    }
    
    /**
     * Parse a PHP file
     * 
     * @param string $fileBase Full path, or base directory
     * @param string $filePath Pathfile (relative to $fileBase)
     */
    public function parse($fileBase, $filePath = null)
    {
        if (!isset($filePath)) {
            $filePath = basename($fileBase);
            $fileBase = dirname($fileBase).DIRECTORY_SEPARATOR;
        }
        $filename = $fileBase.$filePath;

        if (!(file_exists($filename) && is_readable($filename))) {
            throw new PHP_UML_Exception('Could not read '.$filename.'.');
        }

        $this->file       = new PHP_UML_Metamodel_Artifact;
        $this->file->id   = self::getUID();
        $this->file->name = basename($filePath);        
        
        $dirname = dirname($filePath);

        if ($dirname!='.' && $dirname!=DIRECTORY_SEPARATOR) {
            $fp = $this->addDeploymentPackage(dirname($filePath));
        } else {
            $fp = $this->structure->deploymentPackages;    // global
        }

        $fp->ownedType[]     = $this->file;
        $this->file->package = $fp;
       
        $this->parsePhp(file_get_contents($filename));
    }
    
    /**
     * Parse PHP content
     * 
     * @param string $phpContent PHP code provided as a string
     */
    public function parsePhp($phpContent)
    {       
        $this->aliases   = array();
        $this->currentQn = '';
        
        $this->currentPackage = $this->structure->packages;
        
        $tokens = token_get_all($phpContent);
        
        if ($this->docblocks) {
            // || $this->structureFromDocblocks
            // First, let's have a look at the file docblock :
            $dc = $this->tNextDocComment($tokens);
            reset($tokens);
            if ($dc!='' && self::findPackageInDocblock($dc, $set)) {
                $this->currentPackage = $this->addPackage($set[1]);
            }
        }
        $this->tBody($tokens);
    }

    /**
     * Retrieve an implementation of TypeResolver
     *
     * @return PHP_UML_Metamodel_TypeResolver
     */
    public static function getResolver()
    {
        return new PHP_UML_Metamodel_TypeResolverByName();
    }
    
    /**
     * Template matching T_CLASS
     *
     * @param array &$tokens Tokens
     */
    private function tClass(&$tokens)
    {
        $c = null;
        while (list($l, $v) = each($tokens)) {
            switch($v[0]) {
            case T_STRING:
                list($classPkg, $className) = $this->getCurrentPackage($v[1]);

                $c       = new PHP_UML_Metamodel_Class;
                $c->name = $className;
                $c->file = $this->file;
                $c->id   = self::getUID();
                $this->setClassifierFeatures($c);
                break;
            case T_IMPLEMENTS:
                $c->implements = $this->tStringCommaList($tokens);
                break;
            case T_EXTENDS:
                $c->superClass = $this->tStringCommaList($tokens);
                break;
            case '{':
                if (!is_null($c)) {
                    $c = $this->tClassifierBody($tokens, $c);
                    if (!is_null($c))
                        $this->setNestingPackageOfType($c, $classPkg);
                }
                return;
            }
        }
    }
    
    /**
     * Template matching T_INTERFACE
     *
     * @param array &$tokens Tokens
     */
    private function tInterface(&$tokens)
    {
        $i = null;
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
                list($classPkg, $className) = $this->getCurrentPackage($v[1]);

                $i       = new PHP_UML_Metamodel_Interface;
                $i->name = $className;
                $i->file = $this->file;
                $i->id   = self::getUID();
                break;
            case T_EXTENDS:
                $i->superClass = self::tStringCommaList($tokens);
                break;
            case '{':
                if (!is_null($i)) {
                    $i = $this->tClassifierBody($tokens, $i);
                    if (!is_null($i))
                        $this->setNestingPackageOfType($i, $classPkg);
                }
                return;
            }
        }
    }
    
    /**
     * Specific template for T_CLASS/T_INTERFACE
     * Normally preceded by a tClass or tInterface
     * 
     * @param array                        &$tokens Tokens
     * @param PHP_UML_Metamodel_Classifier $class   A class or interface
     * 
     * @return PHP_UML_Metamodel_Classifier The updated class or interface
     */
    private function tClassifierBody(&$tokens, $class)
    {
        $operations = array();
        $attributes = array();

        $this->addDocumentation($class);
        $skipClassifier = $this->toBeSkipped($this->curDocComment);    // should the class be ignored?

        $this->classElementFeatures = array();
        $this->curDocComment        = '';

        $curly = 1;
        while (list($c, $v) = each($tokens)) {
            $skipCurrentElement = $this->toBeSkipped($this->curDocComment);
            switch ($v[0]) {
            case T_FUNCTION:
                if ($skipCurrentElement) {
                    $this->tFunction($tokens, $class);
                } else {
                    $operations[] = $this->tFunction($tokens, $class);
                }
                $this->classElementFeatures = array();
                $this->curDocComment        = '';
                break;
            case T_STRING:
            case T_VARIABLE:
                if (!$skipCurrentElement) {
                    $attributes[] = $this->tAttribute($tokens, $v[1], $class);;
                }
                $this->classElementFeatures = array();
                $this->curDocComment        = '';
                break;

            case T_STATIC:
            case T_ABSTRACT:
            case T_PUBLIC:
            case T_PRIVATE:
            case T_PROTECTED:
            case T_CONST:
            case T_FINAL:
                $this->classElementFeatures[] = $v[0];
                break;

            case T_DOC_COMMENT:
                $this->curDocComment = self::removeDocCommentMarks($v[1]);
                break;

            case '{':
                $curly++;
                break;
            case '}':
                $curly--;
                if ($curly==0) {
                    $class->ownedOperation = $operations;
                    $class->ownedAttribute = $attributes;
                    return $skipClassifier ? null : $class;
                }
            }
        }
    }

    /**
     * Template for the attributes (properties)
     * 
     * @param array                        &$tokens Tokens
     * @param string                       $name    Property name
     * @param PHP_UML_Metamodel_Classifier $class   Parent class
     * 
     * @return PHP_UML_Metamodel_Property 
     */
    private function tAttribute(&$tokens, $name, PHP_UML_Metamodel_Classifier $class=null)
    {
        $r     = $this->tScalar($tokens);
        $token = $r[0];
        $value = $r[1];

        $a       = new PHP_UML_Metamodel_Property;
        $a->name = $this->cleanVariable($name);
        $a->id   = self::getUID();

        if (!is_null($class))
            $a->class = $class;
        else {    // isolated function
            list($classPkg, $className) = $this->getCurrentPackage($name);
            $this->setNestingPackageOfAttribute($a, $classPkg);
            $a->file = $this->file;
        }
        $a->default = $value;
        $this->setElementFeatures($a);

        $docs = self::getDocblocksInDocComment($this->curDocComment);
        //$desc = self::getDescriptionInDocComment($this->curDocComment);
        $this->addDocumentation($a);
        $dbParam = array();
        foreach ($docs as $k) {
            switch($k[1]) {
            case 'var':
                $dbParam[$name] = $k;
                break 2;
            }
        }
        $this->setTypeElement($a, '', $token, $value, $name, $dbParam);      
        return $a;      
    }
    
    /**
     * Specific template for matching a list of T_STRING, separated by a comma
     *
     * @param array &$tokens Tokens
     * 
     * @return array Array of elements found
     */
    private function tStringCommaList(&$tokens)
    {
        $values = array();
        $value  = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case ',':
                $values[] = $this->resolveQName($value);
                $value    = '';
                break;
            case T_STRING:
            case T_NS_SEPARATOR:
                $value .= $v[1];
                break;
            case ';':
            case '{':
            case T_IMPLEMENTS:
                $values[] = $this->resolveQName($value);
                prev($tokens);
                return $values;
            }
        }
    }

    /**
     * Specific template for T_FUNCTION
     *
     * @param array                        &$tokens Tokens
     * @param PHP_UML_Metamodel_Classifier $class   Owning class/interface
     * 
     * @return PHP_UML_Metamodel_Operation The operation created
     */
    private function tFunction(&$tokens, PHP_UML_Metamodel_Classifier $class=null)
    {
        $o = new PHP_UML_Metamodel_Operation;
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
                $o->name = $v[1];
                $o->id   = self::getUID();
                if (!is_null($class))
                    $o->class = $class;
                else {    // isolated function
                    list($classPkg, $className) = $this->getCurrentPackage($v[1]);
                    $this->setNestingPackageOfOperation($o, $classPkg);
                    $o->file = $this->file;
                }
                $this->setElementFeatures($o);
                break;
            case '(':
                $o->ownedParameter = $this->tParametersList($tokens, $o);
                break;
            case '{':
                $this->tBody($tokens);
                prev($tokens);
                break;
            case ';':
            case '}':
                return $o;
            }
        }
    }

    /**
     * Specific template for T_CONST outside of a class
     *
     * @param array &$tokens Tokens
     *
     */
    private function tConst(&$tokens)
    {
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
                $this->tAttribute($tokens, $v[1]);
                return;
            case ';':
            case '}':
                return;
            }
        }
    }
    
    /**
     * Specific template for matching the parameters of a function
     *
     * @param array                       &$tokens Tokens
     * @param PHP_UML_Metamodel_Operation $o       Operation
     * 
     * @return PHP_UML_Metamodel_Parameter The parameter created
     */
    private function tParametersList(&$tokens, PHP_UML_Metamodel_Operation $o) 
    {
        $bracket    = 1;
        $type       = '';
        $parameters = array();
        $dbParam    = array();
        $direction  = 'in';

        $docs = self::getDocblocksInDocComment($this->curDocComment);
        //$desc = self::getDescriptionInDocComment($this->curDocComment);
        $this->addDocumentation($o);
        
        foreach ($docs as $k) {
            switch ($k[1]) {
            case 'param':
                $dbParam[$k[3]] = $k;
                break;
            case 'return':
                $type = $this->resolveQName($k[2]);
                break 2;
            }
        }
        $pr            = new PHP_UML_Metamodel_Parameter;
        $pr->name      = 'return';
        $pr->id        = self::getUID();
        $pr->operation = &$o;
        $pr->direction = 'return';
        $pr->type      = ($type!='' ? $type : 'void');
        $parameters[]  = $pr;
        
        $type = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
            case T_ARRAY:
            case T_NS_SEPARATOR:
                $type .= $v[1];
                break;
            case T_VARIABLE:
                $r     = $this->tScalar($tokens);
                $token = $r[0];
                $value = $r[1];

                $p            = new PHP_UML_Metamodel_Parameter;
                $p->name      = $this->cleanVariable($v[1]);
                $p->id        = self::getUID();
                $p->operation = &$o;
                $p->default   = $value;
                $p->direction = $direction;
                $this->setTypeElement($p, $type, $token, $value, $v[1], $dbParam);                              
                $parameters[] = $p;

                prev($tokens);
                break;
            case '&':
                $direction = 'inout';
                break;
            case ',':
                $type      = '';
                $direction = 'in';
                break;
            case '(':
                $bracket++;
                break;
            case ')':
                $bracket--;
                if ($bracket==0) {
                    return $parameters;
                }
            }
        }
    }

    /**
     * Template for matching a scalar/static data 
     * (eg: $a = -14.5)
     * Stopping characters: , ; )
     * 
     * @param array &$tokens Tokens
     * 
     * @return array An array((string) type, (string) default value)
     */
    private function tScalar(&$tokens)
    {
        $bracket = 0;
        $type    = 0;
        $value   = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
            case T_VARIABLE:
            case T_LNUMBER:
            case T_DNUMBER:
            case T_CONSTANT_ENCAPSED_STRING:
                $value .= $v[1];
                $type   = $v[0];
                break;
            case T_ARRAY:
                $vtmp   = $this->tScalar($tokens);
                $value .= $v[1].$vtmp[1];
                prev($tokens);
                $type = $v[0];
                break;
            case '-':
                $value .= $v[0];
                break;
            case T_DOUBLE_ARROW:
                $value .= $v[1];
                break;
            case '(':
                $value .= $v[0];
                $bracket++;
                break;
            case ')':
                if ($bracket>0) {
                    $value .= $v[0];
                    $bracket--;
                    break;
                } else {
                    break 2;
                }
            case ',':
            case ';':
                if ($bracket>0) {
                    $value .= $v[0];
                    break;
                } else {
                    break 2;
                }
            }
        }
        return array($type, $value);
    }

    /**
     * Template for matching T_NAMESPACE
     *
     * @param array &$tokens Tokens
     */
    private function tNamespace(&$tokens)
    {
        $curly = 0;
        $value = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
            case T_NS_SEPARATOR:
                $value .= $v[1];
                break;
            case ';':
                $this->currentPackage = $this->addPackage($value);
                $this->currentQn      = $value;
                if ($this->curDocComment!='') {
                    $this->addDocumentation($this->currentPackage);
                }
                return;
            case '{':
                $curly++;
                $this->currentPackage = $this->addPackage($value);
                $this->currentQn      = $value;
                if ($this->curDocComment!='') {
                    $this->addDocumentation($this->currentPackage);
                }
                $this->curDocComment = '';
                $this->tBody($tokens);
                prev($tokens);
                break;
            case '}':
                $this->aliases   = array();
                $this->currentQn = '';
                $curly--;
                if ($curly==0)
                    return;
            }
        }
    }

    /**
     * Specific template for matching the first T_STRING
     *
     * @param array &$tokens Tokens
     * 
     * @return string The value
     */
    static private function tString(&$tokens)
    {
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
                return $v[1];
            }
        }
    }

    /**
     * Specific template for matching a qualified name, after a namespace instr
     *
     * @param array &$tokens Tokens
     * 
     * @return string The value
     */
    static private function tQualifiedName(&$tokens)
    {
        $value = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_STRING:
            case T_NS_SEPARATOR:
                $value .= $v[1];
                break;
            case T_AS:
            case ';':
            case ',':
            case ')':
                prev($tokens);
                return $value;
            }
        }
    }

    /**
     * Template for matching T_USE
     *
     * @param array &$tokens Tokens
     */
    private function tUse(&$tokens)
    {
        $qname = self::tQualifiedName($tokens);
        $alias = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_AS:
                $alias = self::tString($tokens);
                break;
            case ',':
                $this->tUse($tokens);    // no additional break please
            case ';':
                $this->addUse($qname, $alias);
                return;
            }
        }
    }

    /**
     * Base template
     * It loops over PHP code, either in the global space, either inside
     * functions, or class functions
     * 
     * @param array &$tokens Tokens
     */
    private function tBody(&$tokens)
    {
        $curly = 1;
        $this->classFeatures = array();
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_CLASS:
                $this->tClass($tokens);
                $this->classFeatures = array();
                $this->curDocComment = '';
                break;
            case T_INTERFACE:
                $this->tInterface($tokens);
                $this->classFeatures = array();
                $this->curDocComment = '';
                break;
            case T_NAMESPACE:
                list($c, $v) = current($tokens);
                if ($c!=T_NS_SEPARATOR) {
                    prev($tokens);
                    $this->tNamespace($tokens);
                }
                $this->curDocComment = '';
                break;
            case T_FUNCTION:
                if (!$this->pureObj) {
                    $this->classElementFeatures = array();
                    $this->tFunction($tokens);
                }
                $this->curDocComment = '';
                break;
            case T_CONST:
                if (!$this->pureObj) {
                    $this->classElementFeatures = array();
                    $this->tConst($tokens);
                }
                $this->curDocComment = '';
                break;
            case T_STRING:
                if ((!$this->pureObj) && $v[1]=='define') { 
                    $this->tDefine($tokens);
                }
                $this->curDocComment = '';
                break;
            case T_DOC_COMMENT:
                $this->curDocComment = self::removeDocCommentMarks($v[1]);
                $this->tDocComment($tokens);
                break;
            case T_USE:
                $this->tUse($tokens);
                break;
            case T_ABSTRACT:
            case T_FINAL:
                $this->classFeatures[] = $v[0];
                break;
            case T_CURLY_OPEN:
            case T_DOLLAR_OPEN_CURLY_BRACES:
            case '{':
                $curly++;
                break;
            case '}':
                $curly--;
                if ($curly==0) {
                    return;
                }
            }
        }
    }

    /**
     * Template for matching doc-comment (the docblocks)
     * We do some forward-tracking to see the instruction that comes next.
     * We must "remember" the comment only for certain instructions (class, const,
     * function, etc). If we don't get one of these instructions, we forget it.
     *
     * @param array &$tokens Tokens
     */
    private function tDocComment(&$tokens)
    {
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) {
            case T_WHITESPACE:
            case '@':
                break;
            default:
                if (!($v[0]==T_STRING && $v[1]=='define')) { 
                    $this->curDocComment = '';
                }
            case T_DOC_COMMENT:
            case T_ABSTRACT:
            case T_FINAL:
            case T_CLASS:
            case T_INTERFACE:
            case T_FUNCTION:
            case T_CONST:
            case T_NAMESPACE:
                prev($tokens);
                return;
            }
        }
    }
    
    /**
     * Specific template for T_DOC_COMMENT.
     * Returns at the first T_DOC_COMMENT found.
     * Used to get the file-level doc-comment
     *
     * @param array &$tokens Tokens
     *
     * @return string The doc-comment, as a string
     */
    private function tNextDocComment(&$tokens)
    {
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) { 
            case T_DOC_COMMENT:
                return self::removeDocCommentMarks($v[1]);
            case T_OPEN_TAG:
            case T_WHITESPACE:
            case T_COMMENT:
                break;
            }
        }
        return '';
    }

    /**
     * Specific template for dealing with the "define" instruction
     * 
     * @param array &$tokens Tokens
     * 
     * @return string The value
     */
    private function tDefine(&$tokens)
    {
        $name = '';
        while (list($c, $v) = each($tokens)) {
            switch ($v[0]) { 
            case T_WHITESPACE:
            case T_COMMENT:
            case '(':
                break;
            case T_CONSTANT_ENCAPSED_STRING:
                $name = str_replace('"', '', str_replace("'", '', $v[1]));
                break;
            default:
                return;
            case ',':
                if ($name!='') {
                    $this->tAttribute($tokens, $name);
                    return;
                }
                else
                    return;
            }
        }
        return;
    }

    /**
     * Adds a package to the metamodel ($packages)
     *
     * @param string                    $name       Name of the package to create
     * @param PHP_UML_Metamodel_Package $nestingPkg Enclosing package. If not given,
     *                                              the package is created at root
     *
     * @return PHP_UML_Metamodel_Package The package created (or the existing one)
     */
    private function addPackage($name, PHP_UML_Metamodel_Package $nestingPkg = null)
    {
        if ($name=='')
            return $this->structure->packages;
 
        if (is_null($nestingPkg)) {
            // root package (global namespace)
            $nestingPkg = $this->structure->packages;
        }
   
        list($pos, $name, $following) = PHP_UML_Metamodel_Helper::getPackagePathParts($name);
 
        // let's check if it does not already exist:
        $p = PHP_UML_Metamodel_Helper::findSubpackageByName($nestingPkg, $name);
        
        // ok, pkg does not exist, let's add it:
        if ($p===false) {
            $p                 = new PHP_UML_Metamodel_Package;
            $p->id             = self::getUID();
            $p->name           = $name;
            $p->nestingPackage = $nestingPkg;
            $p->nestedPackage  = array();

            $nestingPkg->nestedPackage[] = $p;
        }

        if (!($pos===false)) {
            $p = $this->addPackage($following, $p);
        }
        return $p;
    }

    /**
     * Adds a deployment package to the metamodel
     *
     * @param string                    $name       Name of the package to create
     * @param PHP_UML_Metamodel_Package $nestingPkg Enclosing package
     *
     * @return PHP_UML_Metamodel_Package The package created (or the existing one)
     */
    private function addDeploymentPackage($name, PHP_UML_Metamodel_Package $nestingPkg = null)
    {
        if ($name=='')
            return $this->structure->deploymentPackages;
 
        if (is_null($nestingPkg)) {
            // root package (global namespace)
            $nestingPkg = $this->structure->deploymentPackages;
        }
   
        list($pos, $name, $following) = PHP_UML_Metamodel_Helper::getPackagePathParts($name, true, '/');

        // let's check if it does not already exist:
        $p = PHP_UML_Metamodel_Helper::findSubpackageByName($nestingPkg, $name);
        
        // ok, pkg does not exist, let's add it:
        if ($p===false) {
            $p                 = new PHP_UML_Metamodel_Package;
            $p->id             = self::getUID();
            $p->name           = $name;
            $p->nestingPackage = $nestingPkg;
            $p->nestedPackage  = array();

            $nestingPkg->nestedPackage[] = $p;
        }

        if (!($pos===false)) {
            $p = $this->addDeploymentPackage($following, $p);
        }
        return $p;
    }
    
    /**
     * Return the index of the current package, depending on the @package in the
     * last parsed docblock, and/or the potential presence of a package path in the
     * class name.
     * Adds new packages to the $packages stack if necessary.
     * Normally used at each new class/interface insertion.
     *
     * @param string $class Name of the classifier
     *
     * @return array Result array[current package, class name]
     */
    private function getCurrentPackage($class)
    { 
        // Where's the class-level package ?  
        // Is there a namespace along with the class name?
        list($pos, $pkg, $name) = PHP_UML_Metamodel_Helper::getPackagePathParts($class, false);
        if (!($pos===false)) {
            return array($this->addPackage($pkg), $name);
        }

        if ($this->docblocks) { // || $this->structureFromDocblocks
            // Is there a @package in the class docblock ?
            $r = self::findPackageInDocblock($this->curDocComment, $set);
            if ($r>0) {
                return array($this->addPackage($set[1]), $class);
            }
        }
        // No ? Then we return the current known package:
        return array($this->currentPackage, $class);
    }


    /**
     * Adds an alias to the list of namespace aliases
     *
     * @param string $namespace An imported namespace
     * @param string $alias     Alias ("use ... as ...")
     */
    private function addUse($namespace, $alias='')
    {
        if ($alias=='') {
            list($pos, $first, $alias) = PHP_UML_Metamodel_Helper::getPackagePathParts($namespace, false);
        }
        if (!$alias)
            $alias = $first;

        if (substr($namespace, 0, 1)==self::T_NS_SEPARATOR)
            $this->aliases[$alias] = substr($namespace, 1);
        else
            $this->aliases[$alias] = $namespace;
    }
    
    /**
     * Resolve a class path into a qualified name
     * - first by searching/replacing aliases (previously set by "use ... as...")
     * - then by prefixing with the current namespace, if the path is not absolute
     * (and if it contains a separator)
     * 
     * @param string $path A qualified name (a class name like "A\B\C")
     * 
     * @return string The full qualified name
     */
    private function resolveQName($path)
    {
        // if the path does not start by \, we try to replace the alias, if it
        // contains one
        if (count($this->aliases)>0 && $path[0]!=self::T_NS_SEPARATOR) {
            foreach ($this->aliases as $a=>$qn) {
                if (substr($path.self::T_NS_SEPARATOR, 0, strlen($a)+1)==$a.self::T_NS_SEPARATOR) {
                    $x = self::T_NS_SEPARATOR.$qn;
                    $r = substr($path, strlen($a)+1);
                    if ($r!='')
                        $x .= self::T_NS_SEPARATOR.$r;
                    return $x;
                }
            }
        }
        // if there is a \ in the path, but it is not absolute, then it's relative 
        if ((strpos($path, self::T_NS_SEPARATOR)!==false) && $path[0]!=self::T_NS_SEPARATOR) {
            return $this->currentQn.self::T_NS_SEPARATOR.$path;
        }
        return $path;
    }
    
    /**
     * Docblock helpers
     */

    /**
     * Find a @package declaration in a (filtered) doc-comment
     *
     * @param string $text Text to search in
     * @param array  &$set Results (preg)
     * 
     * @return int Result (preg)
     */
    static private function findPackageInDocblock($text, &$set)
    {
        $p = preg_match('/^[ \t]*\*[ \t]+@package[ \t]+(\S*)/mi', $text, $set);
        $s = preg_match_all('/^[ \t]*\*[ \t]+@subpackage[ \t]+(\S*)/mi', $text, $sub, PREG_SET_ORDER);
        if ($p>0 && $s>0) {
            foreach ($sub as $subItem) {
                $set[1] .= self::T_NS_SEPARATOR.$subItem[1];
            }
        }
        return $p; 
    }

    /**
     * Return the docblocks of a doc-comment
     * 
     * @param string $text Doc comment
     * 
     * @return array Preg results
     */
    static private function getDocblocksInDocComment($text)
    {
        $r = preg_match_all(
            '/^[ \t]*\*[ \t]*@([\w]+)[ \t]*([^\s]*)[ \t]*([^\s]*)[ \t]*(.*)/m',
            $text, $set, PREG_SET_ORDER
        );
        return $set;
    }

    /**
     * Return the description part in a doc-comment
     * It is found before the first docblock.
     * 
     * @param string $text Doc comment
     * 
     * @return string The description
     */
    static private function getDescriptionInDocComment($text)
    {
        // we first detect when the docblocs start:
        if (preg_match('/^[ \t]*\*[ \t]*@/m', $text, $set, PREG_OFFSET_CAPTURE))
            $text = substr($text, 0, $set[0][1]);

        // and we take and filter everything before:
        $r   = preg_match_all('/^[ \t]*\*(.*)/m', $text, $set, PREG_SET_ORDER);
        $str = '';
        foreach ($set as $item) {
            $line = trim($item[1]);
            $last = substr($line, -1, 1);
            if ($last=='.' || $last==':' || $line=='')
                $str .= $line.PHP_EOL;
            else
                $str .= $line.' ';
        }
        return trim($str);
    }
    
    /**
     * Stores the description/comments (in $this->curDocComment) about an element 
     * It is directly added as an object Stereotype to the $structure->stereotypes array
     * Note that the link between a stereotype and its element is bidirectional
     * (element->description and stereotype->element)
     *
     * @param PHP_UML_Metamodel_NamedElement &$element The element
     */
    private function addDocumentation(PHP_UML_Metamodel_NamedElement &$element)
    {
        $desc = self::getDescriptionInDocComment($this->curDocComment);
        $docs = self::getDocblocksInDocComment($this->curDocComment);

        $s = $this->structure;
        $s->addDocTags($element, $desc, $docs);
    }

    /**
     * Removes the leading/trailing comment marks
     *
     * @param string $text Text to filter
     * 
     * @return string Filtered text
     */
    static private function removeDocCommentMarks($text)
    {
        return substr(substr($text, 0, -2), 2);
    }
    
    /**
     * Returns a unique ID
     * 
     * @return string
     */
    static private function getUID()
    {
        return PHP_UML_SimpleUID::getUID();
    }

    /**
     * Returns TRUE if the current element must be ignored (because the docblock
     * contains @internal, or because onlyAPI is set)
     * 
     * @param string $text Docblocks to look into
     * 
     * @return bool
     */
    private function toBeSkipped($text)
    {
        return ($this->skipInternal && !(stristr($text, '@internal')===false))
            || ($this->onlyApi && stristr($text, '@api')===false);
    }

    /**
     * Filter a variable names
     * (removes $ according to _keepDollar property)
     *
     * @param string $str Text to filter
     * 
     * @return string
     */
    private function cleanVariable($str)
    {
        if ($this->keepDollar) {
            return $str;
        } else {
            return str_replace('$', '', $str);
        }
    }
 
    /**
     * Sets the nesting package of a type
     *
     * @param PHP_UML_Metamodel_Classifier &$c         A classifier
     * @param PHP_UML_Metamodel_Package    $nestingPkg The enclosing package
     */
    private function setNestingPackageOfType(PHP_UML_Metamodel_Classifier &$c, PHP_UML_Metamodel_Package $nestingPkg)
    {
        $c->package = $nestingPkg;
        if (PHP_UML_Metamodel_Helper::searchTypeIntoPackage($c->package, $c->name)===false) {
            $nestingPkg->ownedType[]  = &$c;
            $this->file->manifested[] = &$c;
        } else {
            PHP_UML_Warning::add(
                'Class '.$c->name.' already defined, in '.$this->file->name
            );
        }
    }

    /**
     * Sets the nesting package of an isolated operation
     *
     * @param PHP_UML_Metamodel_Operation &$o         A function
     * @param PHP_UML_Metamodel_Package   $nestingPkg The enclosing package
     */
    private function setNestingPackageOfOperation(PHP_UML_Metamodel_Operation &$o, PHP_UML_Metamodel_Package $nestingPkg)
    {
        $o->package = $nestingPkg;
        if (PHP_UML_Metamodel_Helper::searchOperationIntoPackage($o->package, $o->name)===false) {
            $nestingPkg->ownedOperation[] = &$o;
            $this->file->manifested[]     = &$o;
        } else {
            PHP_UML_Warning::add(
                'Function '.$o->name.' already defined, in '.$this->file->name
            );
        }
    }

    /**
     * Sets the nesting package of an isolated attribute (the PHP "const")
     *
     * @param PHP_UML_Metamodel_Property &$a         A property
     * @param PHP_UML_Metamodel_Package  $nestingPkg The enclosing package
     */
    private function setNestingPackageOfAttribute(PHP_UML_Metamodel_Property &$a, PHP_UML_Metamodel_Package $nestingPkg)
    {
        $a->package = $nestingPkg;
        if (PHP_UML_Metamodel_Helper::searchAttributeIntoPackage($a->package, $a->name)===false) {
            $nestingPkg->ownedAttribute[] = &$a;
            $this->file->manifested[] = &$a;
        } else {
            PHP_UML_Warning::add(
                'Constant '.$a->name.' already defined, in '.$this->file->name
            );
        }
    }

    /**
     * Sets the features (abstract, final) to a classifier
     *
     * @param PHP_UML_Metamodel_Interface &$c Class or interface object
     */
    private function setClassifierFeatures(PHP_UML_Metamodel_Classifier &$c)
    {
        foreach ($this->classFeatures as $token) {
            switch ($token) {
            case T_ABSTRACT:
                $c->isAbstract = true;
                break;
            case T_FINAL:
                $c->isReadOnly = true;
                break;
            }      
        }
    }

    /**
     * Set the features (static, private...) in a given class property/function
     *
     * @param PHP_UML_Metamodel_NamedElement &$c Element
     */
    private function setElementFeatures(PHP_UML_Metamodel_NamedElement &$c)
    {
        $c->isInstantiable = true;
        $c->visibility     = 'public';
        foreach ($this->classElementFeatures as $token) {
            switch ($token) {
            case T_STATIC:
                $c->isInstantiable = false;
                break;
            case T_ABSTRACT:
                $c->isAbstract = true;
                break;
            case T_PRIVATE:
                $c->visibility = 'private';
                break;
            case T_PROTECTED:
                $c->visibility = 'protected';
                break;
            case T_CONST:
                $c->isInstantiable = false;
            case T_FINAL:
                $c->isReadOnly = true;
                break;
            }      
        }
    }

    /**
     * Set the type of a given element (class property or function)
     *
     * @param PHP_UML_Metamodel_NamedElement &$p      The element
     * @param string                         $type    Explicit type (type hint)
     * @param string                         $token   The token
     * @param string                         $default The (eventual) default value
     * @param string                         $varName The variable name
     * @param array                          $dbParam The preg-array of docblocks
     */
    private function setTypeElement(PHP_UML_Metamodel_NamedElement &$p, $type, $token, $default, $varName, array $dbParam)
    {
        if ($type!='') {
            $p->type = $this->resolveQName($type);
        } else {
            $type = self::getTypeFromToken($token, $default);
            if ($type!='') {
                $p->type = $type;
            } else {
                $type = $this->getTypeFromDocblocks($dbParam, $varName);
                if ($type!='')
                    $p->type = $type;
                else
                    $p->type = 'mixed';
            }
        }
    }
    
    /**
     * Return a type (as a string) according to a given token/default value
     *
     * @param string $token        Token
     * @param string $defaultValue Value
     * 
     * @return string The type
     */
    static private function getTypeFromToken($token, $defaultValue)
    {
        switch($token) {
        case T_ARRAY:
            return 'array';
        case T_CONSTANT_ENCAPSED_STRING:
            return 'string';
        case T_DNUMBER:
            return 'float';
        case T_LNUMBER:
            return 'int';
        case T_STRING:
            if ($defaultValue=='true' || $defaultValue=='false')
                return 'bool';
            if ($defaultValue=='void')
                return 'void';
        }            
        return '';
    }
    
    /**
     * Return a type (as a string), from the docblocks of a doc comment
     *
     * @param array  $dbParam  The array of params (obtained through a preg)
     * @param string $variable The name of the parameter you want to know the type
     *
     * @return string The type
     */
    private function getTypeFromDocblocks(array $dbParam, $variable)
    {
        if (isset($dbParam[$variable]) && $this->docblocks)
            $param = $dbParam[$variable];
        else
            return '';
        if (isset($param[2]))
            return $dbParam[$variable][2];
        else
            return '';
    }
    
}
?>
