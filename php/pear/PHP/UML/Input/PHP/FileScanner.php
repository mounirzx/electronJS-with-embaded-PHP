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
 * A PHP implementation of a FileScanner.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage PHP
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * 
 */
class PHP_UML_Input_PHP_FileScanner extends PHP_UML_Input_ImporterFileScanner
{
    /**
     * Parser to use for the next parsing
     *
     * @var PHP_UML_Input_PHP_Parser
     */
    private $parser;
    
    /**
     * ParserOptions to use for the next parsing
     *
     * @var PHP_UML_Input_PHP_ParserOptions
     */
    private $parserOptions;
        
        
    public function setParserOptions(PHP_UML_Input_PHP_ParserOptions $options)
    {
        $this->parserOptions = $options;
    }
    
    public function getParserOptions()
    {
        return $this->parserOptions;
    }
    
    public function import()
    {
        if (empty($this->parser)) {
            $this->parser = new PHP_UML_Input_PHP_ParserImpl($this->model, $this->parserOptions);
        }
        parent::scan();
        
        $resolver = PHP_UML_Input_PHP_ParserImpl::getResolver();
        $resolver->resolve($this->model->packages, array($this->model->packages));
    }
    
    /**
     * Implementation of tickFile() : we parse every file met.
     * 
     * @param string $basedir  Directory path
     * @param string $filename File name
     *
     * @see PHP_UML/UML/FileScanner#tickFile()
     */
    public function tickFile($basedir, $filename)
    {
        $this->parser->parse($basedir, $filename);
    }
}
?>
