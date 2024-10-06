<?php
/**
 * This Person class encapsulates a couple of properties which
 * a person might have: their name and age.
 * We also give the Person the opportunity to introduce themselves.
 *
 * @link http://cowburn.info/php/php5-method-chaining/
 */
class Person
{
    var $m_szName;
    var $m_iAge;

    function setName($szName)
    {
        $this->m_szName = $szName;
        return $this; // We now return $this (the Person)
    }

    function setAge($iAge)
    {
        $this->m_iAge = $iAge;
        return $this; // Again, return our Person
    }

    function introduce()
    {
        printf('Hello my name is %s and I am %d years old.',
            $this->m_szName,
            $this->m_iAge);
    }
}

// We'll be creating me, digitally.
$peter = new Person();
// Let's set some attributes and let me introduce myself,
// all in one line of code.
$peter->setName('Peter')->setAge(23)->introduce();
?>