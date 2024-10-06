<?php
/**
 * Test tokens that appeared in PHP 5
 *    T_ABSTRACT
 *    T_CATCH
 *    T_FINAL
 *    T_INSTANCEOF
 *    T_PRIVATE
 *    T_PROTECTED
 *    T_PUBLIC
 *    T_THROW
 *    T_TRY
 *    T_CLONE
 *    T_INTERFACE
 *    T_IMPLEMENTS
 *
 * PHP versions 4 and 5
 *
 * @category PHP
 * @package  PHP_CompatInfo
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD
 * @version  CVS: $Id: checkPHP5.php,v 1.7 2008/07/22 21:13:14 farell Exp $
 * @link     http://pear.php.net/package/PHP_CompatInfo
 * @ignore
 */

require_once 'PHP/CompatInfo.php';

/**
 * @ignore
 */
abstract class AbstractClass
{
    abstract protected function getValue();
}
/**
 * @ignore
 */
interface ITemplate
{
    public function setVariable($name, $var);
    public function getHtml($template);
}
/**
 * @ignore
 */
class Template implements ITemplate
{
    private $vars = array();

    public function setVariable($name, $var)
    {
        $this->vars[$name] = $var;
    }

    public function getHtml($template)
    {
        foreach ($this->vars as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }
        return $template;
    }
}
/**
 * @ignore
 */
class BaseClass
{
    public $objet1;
    public $objet2;

    public function __construct()
    {
    }

    public function __clone()
    {
        $this->object1 = clone($this->object1);
    }

    private function foo()
    {
    }

    protected function bar()
    {
        if ($this->object1 instanceof BaseClass) {
            return;
        }

        try {
            $error = 'my error';
            throw new Exception($error);

        } catch(Exception $__bar_exception) {

        }
    }

    final public function moreTesting()
    {
        echo "BaseClass::moreTesting() called \n";
    }
}

$info = new PHP_CompatInfo();

$file    = __FILE__;
$options = array('debug' => true);

$r = $info->parseFile($file, $options);
/*
   To keep backward compatibility, result is also return (here in $r)
   but you don't need to print it, it's the default behavior of API 1.8.0
 */
//var_export($r);
?>