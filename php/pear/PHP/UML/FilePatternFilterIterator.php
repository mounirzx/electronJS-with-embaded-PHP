<?php
/**
 * PHP Parser and UML/XMI generator. Reverse-engineering tool.
 *
 * A package to scan PHP files and directories, and get an UML/XMI representation
 * of the parsed classes/packages.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 118 $
 * @link     http://pear.php.net/package/PHP_UML
 * @link     http://www.baptisteautin.com/projects/PHP_UML/
 * @since    $Date: 2009-07-11 20:48:51 +0200 (sam., 11 juil. 2009) $
 */

/**
 * Specialized iterator for the filesystem scan.
 * It can accept/stop the recursive scan, according to allow/ignore file patterns
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link     http://pear.php.net/package/PHP_UML
 */
class PHP_UML_FilePatternFilterIterator extends RecursiveFilterIterator
{
    protected $ignored;
    protected $allowed;
    
    /**
     * Constructor
     *
     * @param Iterator $iterator Iterator object
     * @param array    $ignored  Ignore pattern(s)
     * @param array    $allowed  Match pattern(s)
     */
    public function __construct(Iterator $iterator, $ignored, $allowed)
    {
        parent::__construct($iterator);
        $this->ignored = $ignored;
        $this->allowed = $allowed;
    }
    
    /**
     * Filter files and folders.
     * If the element is a file or a folder, it must not be among the ingored elts.
     * If it a file, it must match the file patterns.
     * 
     * @return boolean True if the element is acceptable
     */
    public function accept()
    {
        $ptr      = $this->getInnerIterator()->current();
        $filename = $ptr->getFilename();
        $pathname = $ptr->getPathname();

        // it must not be ignored:
        foreach ($this->ignored as $pattern) {
            $pregPattern = '/'.
                str_replace(array('\*', '\?'), array('.*', '.'), preg_quote(DIRECTORY_SEPARATOR.$pattern, '/')).
                '$/i';
            if (preg_match($pregPattern, $pathname)>0) {
                return false;
            }
        }
        // and it must match:
        if ($ptr->isFile()) {
            foreach ($this->allowed as $pattern) {
                $pregPattern = '/^'.
                    str_replace(array('\*', '\?'), array('.*', '.'), preg_quote($pattern, '/')).
                    '$/i';
                if (preg_match($pregPattern, $filename)>0) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }
    
    /**
     * We must override that method to pass the ignored/allowed arrays
     *
     * @return PHP_UML_FilterIterator
     */
    public function getChildren()
    {
        return new self($this->getInnerIterator()->getChildren(), $this->ignored, $this->allowed);
    } 
}
?>
