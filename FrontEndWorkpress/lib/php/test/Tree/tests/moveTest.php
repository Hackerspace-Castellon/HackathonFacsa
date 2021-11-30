<?php
//
//  $Id: moveTest.php 320812 2011-12-09 23:49:34Z danielc $
//

require_once dirname(__FILE__) . '/TreeHelper.php';

class tests_moveTest extends TreeHelper
{
    // check if we get the right ID, for the given path
    function test_MemoryDBnested()
    {
        $tree = $this->getMemoryDBnested();        
        $ret = $tree->move(5, 1);
        $tree->setup();

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        // and check if the move succeeded, by checking the new parentId
        $this->assertEquals(1,$tree->getParentId(5));
    }

    function test_MemoryMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree = $this->getMemoryMDBnested();        
        $ret = $tree->move(5, 1);
        $tree->setup();

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        // and check if the move succeeded, by checking the new parentId
        $this->assertEquals(1, $tree->getParentId(5));
    }

    function test_MemoryDBnestedNoAction()
    {
        $tree = $this->getMemoryDBnested();        
//        $id = $tree->getIdByPath('/Root/child 2/child 2_2');
        $parentId = $tree->getParentId(5);
        $ret = $tree->move(5, 5);
        $tree->setup();

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        $this->assertEquals($parentId, $tree->getParentId(5));
    }

    function test_MemoryMDBnestedNoAction()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree = $this->getMemoryMDBnested();        
//        $id = $tree->getIdByPath('/Root/child 2/child 2_2');
        $parentId = $tree->getParentId(5);
        $ret = $tree->move(5, 5);
        $tree->setup();

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        $this->assertEquals($parentId, $tree->getParentId(5));
    }

    // do this for XML
            
    // do this for Filesystem

    // do this for DBsimple
    
    // do this for DynamicDBnested
    function test_DynamicDBnested()
    {
        $tree =& $this->getDynamicDBnested();
        $ret = $tree->move(5, 1);

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        // and check if the move succeeded, by checking the new parentId
        $this->assertEquals(1, $tree->getParentId(5));
    }

    function test_DynamicMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree =& $this->getDynamicDBnested();
        $ret = $tree->move(5, 1);

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        // and check if the move succeeded, by checking the new parentId
        $this->assertEquals(1, $tree->getParentId(5));
    }

    function test_DynamicDBnestedNoAction()
    {
        $tree =& $this->getDynamicDBnested();
//        $id = $tree->getIdByPath('/Root/child 2/child 2_2');
        $parentId = $tree->getParentId(5);
        $ret = $tree->move(5, 5);

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        $this->assertEquals($parentId, $tree->getParentId(5));
    }

    function test_DynamicMDBnestedNoAction()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree =& $this->getDynamicMDBnested();
//        $id = $tree->getIdByPath('/Root/child 2/child 2_2');
        $parentId = $tree->getParentId(5);
        $ret = $tree->move(5, 5);

        // Avoid PHPUnit exhausting memory if $ret is a large array or object.
        if (is_bool($ret)) {
            $this->assertTrue($ret);
        } else {
            $this->fail('Expected TRUE but got a ' . gettype($ret));
        }

        $this->assertEquals($parentId, $tree->getParentId(5));
    }  
}

?>
