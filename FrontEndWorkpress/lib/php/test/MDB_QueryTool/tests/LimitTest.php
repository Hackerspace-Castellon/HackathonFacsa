<?php
//
//  $Id: LimitTest.php 322087 2012-01-11 18:32:28Z danielc $
//

require_once dirname(__FILE__) . '/TestCase.php';

class tests_LimitTest extends tests_TestCase
{
    // test if setLimit works
    function test_setLimit()
    {
        $user = new tests_Common(TABLE_USER);
        $user->setLimit(0, 10);
        $this->assertEquals(array(0, 10), $user->getLimit());
    }

    // test if setLimit works
    function test_setLimit1()
    {
        $user = new tests_Common(TABLE_USER);

        $user->add(array('login' => 1));
        $user->add(array('login' => 2));
        $user->add(array('login' => 3));
        $user->add(array('login' => 4));

        $user->setLimit(0, 2);
        $this->assertEquals(2, sizeof($user->getAll()));

        $user->setLimit(0, 3);
        $this->assertEquals(3, sizeof($user->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getAll()
    {
        $user = new tests_Common(TABLE_USER);
        $user->setLimit(0, 10);
        $user->add(array('login' => 1));
        $user->add(array('login' => 2));
        $user->add(array('login' => 3));
        $user->add(array('login' => 4));
        $this->assertEquals(1, sizeof($user->getAll(0, 1)));
        $user->setLimit(0, 3);
        $this->assertEquals(2, sizeof($user->getAll(0, 2)));

        $this->assertEquals(3, sizeof($user->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getCol()
    {
        $user = new tests_Common(TABLE_USER);
        $user->setLimit(0, 10);
        $user->add(array('login' => 1));
        $user->add(array('login' => 2));
        $user->add(array('login' => 3));
        $user->add(array('login' => 4));
        $this->assertEquals(1, sizeof($user->getCol('id', 0, 1)));
        $user->setLimit(0, 3);
        $this->assertEquals(2, sizeof($user->getCol('id', 0, 2)));

        $this->assertEquals(3, sizeof($user->getCol('id')));
    }
}

?>
