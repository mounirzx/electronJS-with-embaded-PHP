<?php
/**
 * Another example of PHP method chaining
 *
 * @link http://sebastian-bergmann.de/archives/738-Support-for-BDD-and-Stories-in-PHPUnit-3.3.html
 */

require_once 'PHPUnit/Extensions/Story/TestCase.php';

require_once 'BowlingGame.php';

class BowlingGameSpec extends PHPUnit_Extensions_Story_TestCase
{
    /**
     * @scenario
     */
    function scoreForOneSpareIs16()
    {
        $this->given('New game')
             ->when('Player rolls', 5)
             ->and('Player rolls', 5)
             ->and('Player rolls', 3)
             ->then('Score should be', 16);
    }
}
?>