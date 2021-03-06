<?php
// $Id: mdb_querytool_test_base.php 180765 2005-02-25 14:15:59Z quipo $

require_once 'simple_include.php';
require_once dirname(__FILE__).'/mdb_querytool_include.php';

class TestOfMDB_QueryTool extends UnitTestCase
{
    var $tableSpec = array(
        array(
            'name'      => TABLE_QUESTION,
            'shortName' => TABLE_QUESTION
        ),
        array(
            'name'      => TABLE_ANSWER,
            'shortName' => TABLE_ANSWER
        ),
    );

    var $options = array();

    var $qt;

    function TestOfMDB_QueryTool($name = __CLASS__) {
        $this->UnitTestCase('Test of '.str_replace('TestOf', 'Test Of ', $name));
    }
    function setUp() {
        $this->qt =& new MDB_QueryTool(DB_DSN, $GLOBALS['DB_OPTIONS']);
        if (PEAR::isError($this->qt)) {
            $this->assertTrue(false, $this->qt->getUserInfo());
        }
        if (PEAR::isError($this->qt->db)) {
            $this->assertTrue(false, $this->qt->db->getUserInfo());
        }
        $this->qt->removeAll();
    }
    function tearDown() {
        $this->qt->removeAll();
        unset($this->qt);
    }
    function _getSampleData($row, $id = null) {
        $ret = array(
            'id'          => $id,
            'login'       => 'test'.$row,
            'qt_password' => 'none',
            'name'        => 'user_'.$row,
            'address_id'  => ((int)$row % 2),
            'company_id'  => ((int)$row % 3),
        );
        if (is_null($id)) {
            unset($ret['id']);
        }
        return $ret;
        //return array_values($ret);
    }
}
?>