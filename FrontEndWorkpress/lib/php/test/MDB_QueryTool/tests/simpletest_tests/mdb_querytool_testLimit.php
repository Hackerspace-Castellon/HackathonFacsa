<?php
// $Id: mdb_querytool_testLimit.php 180765 2005-02-25 14:15:59Z quipo $

require_once dirname(__FILE__).'/mdb_querytool_test_base.php';

class TestOfMDB_QueryTool_Limit extends TestOfMDB_QueryTool
{
    function TestOfMDB_QueryTool_Limit($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    // test if setLimit works
    function test_setLimit() {
        $this->qt =& new MDB_QT(TABLE_USER);
        $this->qt->setLimit(0, 10);
        $this->assertEqual(array(0, 10), $this->qt->getLimit());
    }

    // test if setLimit works
    function test_setLimit1() {
        $this->qt =& new MDB_QT(TABLE_USER);

        $this->qt->add($this->_getSampleData(1));
        $this->qt->add($this->_getSampleData(2));
        $this->qt->add($this->_getSampleData(3));
        $this->qt->add($this->_getSampleData(4));

        $this->qt->setLimit(0, 2);
        $this->assertEqual(2, sizeof($this->qt->getAll()));

        $this->qt->setLimit(0, 3);
        $this->assertEqual(3, sizeof($this->qt->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getAll()
    {
        $this->qt =& new MDB_QT(TABLE_USER);
        $this->qt->setLimit(0, 10);
        
        $this->qt->add($this->_getSampleData(1));
        $this->qt->add($this->_getSampleData(2));
        $this->qt->add($this->_getSampleData(3));
        $this->qt->add($this->_getSampleData(4));
        
        $this->assertEqual(1, sizeof($this->qt->getAll(0, 1)));
        $this->qt->setLimit(0, 3);
        $this->assertEqual(2, sizeof($this->qt->getAll(0, 2)));

        $this->assertEqual(3, sizeof($this->qt->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getCol()
    {
        $this->qt =& new MDB_QT(TABLE_USER);
        $this->qt->setLimit(0, 10);
        
        $this->qt->add($this->_getSampleData(1));
        $this->qt->add($this->_getSampleData(2));
        $this->qt->add($this->_getSampleData(3));
        $this->qt->add($this->_getSampleData(4));
        
        $this->assertEqual(1, sizeof($this->qt->getCol('id', 0, 1)));
        $this->qt->setLimit(0, 3);
        $this->assertEqual(2, sizeof($this->qt->getCol('id', 0, 2)));

        $this->assertEqual(3, sizeof($this->qt->getCol('id')));
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfMDB_QueryTool_Limit();
    $test->run(new HtmlReporter());
}
?>