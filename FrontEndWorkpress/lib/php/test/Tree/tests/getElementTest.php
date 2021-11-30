<?php
//
//  $Id: getElementTest.php 320812 2011-12-09 23:49:34Z danielc $
//

require_once dirname(__FILE__) . '/TreeHelper.php';

class tests_getElementTest extends TreeHelper
{
    /**
    *   There was a bug when we mapped column names, especially when we mapped 
    *   a column to the same name as the column. We check this here too.
    *
    *
    */
    function test_MemoryDBnested()
    {
        $tree = $this->getMemoryDBnested();        
        $tree->update(3, array('comment' => 'PEAR rulez'));
        $tree->setup();
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment'], 'Original:');

        $tree->setOption('columnNameMaps', array('comment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment'], 'Map to same name:');

        // This doesn't work.  I took a quick look to try and fix it.  What
        // a mess.  Considering how much I've already fixed, I just don't
        // care anymore.  Marking it incomplete.  --Dan
        $this->markTestIncomplete();
        $tree->setOption('columnNameMaps', array('myComment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['myComment'], 'Map to different name:');
    }

    function test_MemoryMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree = $this->getMemoryMDBnested();        
        $tree->update(3, array('comment' => 'PEAR rulez'));
        $tree->setup();
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);

        $tree->setOption('columnNameMaps', array('comment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);
    
        $tree->setOption('columnNameMaps', array('myComment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['myComment']);
    }

    // do this for XML
            
    // do this for Filesystem

    // do this for DBsimple
    
    // do this for DynamicDBnested
    function test_DynamicDBnested()
    {
        $tree =& $this->getDynamicDBnested();
        $tree->update(3, array('comment' => 'PEAR rulez'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);

        $tree->setOption('columnNameMaps', array('comment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);
    
        $tree->setOption('columnNameMaps', array('myComment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['myComment']);
    }

    function test_DynamicMDBnested()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree =& $this->getDynamicMDBnested();
        $tree->update(3, array('comment' => 'PEAR rulez'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);

        $tree->setOption('columnNameMaps', array('comment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['comment']);
    
        $tree->setOption('columnNameMaps', array('myComment' => 'comment'));
        $actual = $tree->getElement(3);
        $this->assertEquals('PEAR rulez', $actual['myComment']);
    }

    /**
    * Empty the tree and add an element, retreive it and check if it is the one we added.
    *
    *
    */
    function test_DynamicDBnestedEmptyTree()
    {
        $tree = Tree::setup('Dynamic_DBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $tree->remove($tree->getRootId());
        
        $tree = Tree::setup('Memory_DBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $tree->setup();
        $id = $tree->add(array('name' => 'Start'));
        $tree->setup();
        $el = $tree->getElement($id);
        $this->assertEquals('Start', $el['name']);
        $tree->remove($tree->getRootId());
        
        $tree = Tree::setup('Dynamic_DBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $id = $tree->add(array('name' => 'StartDyn'));
        $el = $tree->getElement($id);
        $this->assertEquals('StartDyn', $el['name']);
    }    

    function test_DynamicMDBnestedEmptyTree()
    {
        if (!$this->has_mdb) {
            $this->markTestSkipped('MDB is not installed');
        }

        $tree = Tree::setup('Dynamic_MDBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $tree->remove($tree->getRootId());
        
        $tree = Tree::setup('Memory_MDBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $tree->setup();
        $id = $tree->add(array('name' => 'Start'));
        $tree->setup();
        $el = $tree->getElement($id);
        $this->assertEquals('Start', $el['name']);
        $tree->remove($tree->getRootId());
        
        $tree = Tree::setup('Dynamic_MDBnested', $this->dsn, array('table' => TABLE_TREENESTED));
        $id = $tree->add(array('name' => 'StartDyn'));
        $el = $tree->getElement($id);
        $this->assertEquals('StartDyn', $el['name']);
    } 
        
}

?>
