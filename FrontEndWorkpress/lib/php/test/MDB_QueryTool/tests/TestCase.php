<?php
//
//  $Id: TestCase.php 322098 2012-01-11 21:20:03Z danielc $
//

require_once 'PHPUnit/Autoload.php';
require_once 'MDB/QueryTool.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/Common.php';

abstract class tests_TestCase extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require dirname(__FILE__) . '/sql.php';
        $querytool = new tests_Common();
        if (empty($dbStructure[$querytool->db->phptype])) {
            die("sql.php lacks queries for $querytool->db->phptype\n");
        }
        foreach ($dbStructure[$querytool->db->phptype]['setUp'] as $aQuery) {
            if (MDB2::isError($ret=$querytool->db->query($aQuery))) {
                $this->markTestSkipped($ret->getUserInfo());
            }
        }
    }

    protected function setUp()
    {
        foreach ($GLOBALS['allTables'] as $aTable) {
            $tableObj = new tests_Common($aTable);
            $tableObj->removeAll();
        }
    }

    public static function tearDownAfterClass()
    {
        require dirname(__FILE__) . '/sql.php';
        $querytool = new tests_Common();
        foreach ($dbStructure[$querytool->db->phptype]['tearDown'] as $aQuery) {
            $querytool->db->query($aQuery);
        }
    }

    protected function assertStringEquals($expected,$actual,$msg='')
    {
        $expected = '~^\s*'.preg_replace('~\s+~','\s*',trim(preg_quote($expected))).'\s*$~i';
        $this->assertRegExp($expected,$actual,$msg);
    }

}
