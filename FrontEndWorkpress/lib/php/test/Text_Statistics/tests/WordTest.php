<?php
// Call Text_WordTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Text_WordTest::main');
}

require_once 'PHPUnit/Framework.php';

chdir(dirname(__FILE__) . '/../');
require_once 'Text/Word.php';

/**
 * Test class for Text_Word.
 * Generated by PHPUnit on 2008-01-31 at 21:26:35.
 */
class Text_WordTest extends PHPUnit_Framework_TestCase
{
    protected static $known_words = array(
        'the'        => 1,
        'late'       => 1,
        'hello'      => 2,
        'frantic'    => 2,
        'programmer' => 3
    );

    protected static $special_words = array(
        'absolutely' => 4,
        'alien'      => 3,
        'ion'        => 2,
        'tortion'    => 2,
        'gracious'   => 2,
        'lien'       => 1,
        'syllable'   => 3
    );

    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Text_WordTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }



    /**
     * @todo Implement test_mungeWord().
     */
    public function test_mungeWord() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement test_countSpecialSyllables().
     */
    public function test_countSpecialSyllables() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }



    public function testNumSyllablesKnownWords()
    {
        foreach (self::$known_words as $word => $syllables) {
            $obj = new Text_Word($word);
            $this->assertEquals(
                $syllables, $obj->numSyllables(),
                "$word has incorrect syllable count"
            );
        }
    }



    public function testNumSyllablesSpecialWords()
    {
        foreach (self::$special_words as $word => $syllables) {
            $obj = new Text_Word($word);
            $this->assertEquals(
                $syllables, $obj->numSyllables(),
                "$word has incorrect syllable count"
            );
        }
    }
}

// Call Text_WordTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Text_WordTest::main') {
    Text_WordTest::main();
}
?>
