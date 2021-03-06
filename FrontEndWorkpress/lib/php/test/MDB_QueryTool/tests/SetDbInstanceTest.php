<?php
//
// $Id: SetDbInstanceTest.php 322098 2012-01-11 21:20:03Z danielc $
//

require_once dirname(__FILE__) . '/TestCase.php';

/**
* This class just checks if the query is returned, not if
* the query was properly rendered. This should be subject to
* some other tests!
*
* @package tests
*/
class tests_SetDbInstanceTest extends tests_TestCase
{
    /**
     * Check if the two instances are the same by comparing
     * the fetchMode, since this is the easiest to compare if
     * two objects are the same in PHP4.
     * We can do that since the querytool sets the fetch mode to
     * MDB_FETCHMODE_ASSOC.
     * Not very nice but it works.
     *
     */
    function test_default()
    {
        $db = MDB2::connect(unserialize(MDB_QUERYTOOL_TEST_DSN));

        $qt = new MDB_QueryTool(null, array(), 2);
        $qt->setDbInstance($db);
        $dbActual = $qt->getDbInstance();
        $this->assertEquals($db->fetchmode, $dbActual->fetchmode);
    }

    /**
     * Make sure the way we did it before works too.
     * Passing the DB_DSN to the constructor should also work.
     * And retreiving the db instance should result in a sub class
     * of MDB_common.
     */
    function test_oldWay()
    {
        $qt = new MDB_QueryTool(unserialize(MDB_QUERYTOOL_TEST_DSN), array(), 2);
        $db = $qt->getDbInstance();
        $this->assertInstanceOf('MDB2_Driver_Common', $db);
    }

}

?>
