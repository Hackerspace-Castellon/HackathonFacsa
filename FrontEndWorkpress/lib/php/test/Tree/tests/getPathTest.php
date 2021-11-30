<?php
//
//  $Id: getPathTest.php 320812 2011-12-09 23:49:34Z danielc $
//

require_once dirname(__FILE__) . '/TreeHelper.php';

class tests_getPathTest extends TreeHelper
{
    // check if we get the right ID, for the given path
    function test_MemoryDBnested()
    {
        $tree =& $this->getMemoryDBnested();        
        $this->_testPath($tree);
    }

    function test_MemoryMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree =& $this->getMemoryMDBnested();        
        $this->_testPath($tree);
    }

    // do this for XML
            
    // do this for Filesystem

    // do this for DBsimple
    
    // do this for DynamicDBnested
    function test_DynamicDBnested()
    {
        $tree =& $this->getDynamicDBnested();
        $this->_testPath($tree);
    }

    function test_DynamicMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree =& $this->getDynamicMDBnested();
        $this->_testPath($tree);
    }
    
    function _testPath(&$tree)
    {
//        $id = $tree->getIdByPath('/Root/child 2/child 2_2');
        $id = 5;
        $path = $tree->getPath($id);
        
        $this->assertEquals(3, sizeof($path));
        $this->assertEquals('Root', $path[0]['name']);
        $this->assertEquals('child 2', $path[1]['name']);
        $this->assertEquals('child 2_2', $path[2]['name']);
    }


}

?>
