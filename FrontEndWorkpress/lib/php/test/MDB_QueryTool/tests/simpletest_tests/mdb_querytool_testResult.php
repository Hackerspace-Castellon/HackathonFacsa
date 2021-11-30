<?php
// $Id: mdb_querytool_testResult.php 234854 2007-05-03 17:18:23Z hugoki $

require_once dirname(__FILE__).'/mdb_querytool_test_base.php';
require_once 'MDB/QueryTool/Result/Object.php';

class MDB_QueryTool_Result_Result_Row_Ext extends MDB_QueryTool_Result_Row
{
}

class MDB_QueryTool_Result_Result_Row_Ext2 extends MDB_QueryTool_Result_Result_Row_Ext
{
}


class TestOfMDB_QueryTool_Result extends TestOfMDB_QueryTool
{

    function TestOfMDB_QueryTool_Result($name = __CLASS__) {
        $this->UnitTestCase($name);
    }

    function test_Get_Result_Array() {
        $this->qt = new MDB_QT(TABLE_USER);
        $newData = $this->_getSampleData(1);
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $this->qt->useResult('array');
        $res = $this->qt->getAll();
        $retrieved = $res->fetchRow();

        $this->assertEqual($newData['name'], $retrieved['name']);
        $this->assertEqual($newData['address_id'], $retrieved['address_id']);
    }

    function test_Get_Result_Multiple() {
        $this->qt  = new MDB_QT(TABLE_USER);
        $newData1  = $this->_getSampleData(1);
        $newData2  = $this->_getSampleData(2);
        $ids       = $this->qt->addMultiple(array($newData1,$newData2));

        $this->assertEqual(array(1, 2), $ids);
        
        $retrieved = $this->qt->getMultiple($ids);
        
        $this->assertEqual($newData1['name'], $retrieved[0]['name']);
        $this->assertEqual($newData1['address_id'], $retrieved[0]['address_id']);
        $this->assertEqual($newData2['name'], $retrieved[1]['name']);
        $this->assertEqual($newData2['address_id'], $retrieved[1]['address_id']);
    }

    function test_Get_Result_Custom_Object() {
        $this->qt = new MDB_QT(TABLE_USER);
        $newData  = $this->_getSampleData(1);
        $id       = $this->qt->add($newData);
        $this->assertTrue($id != false);

        //test one-level inheritance
        $classname  = 'MDB_QueryTool_Result_Result_Row_Ext';
        $parentname = 'MDB_QueryTool_Result_Row';
        $this->qt->useResult('object');
        $this->assertTrue($this->qt->setReturnClass($classname));
        $res       = $this->qt->getAll();
        $retrieved = $res->fetchRow();
        $this->assertEqual($classname, get_class($retrieved));
        $this->assertTrue(is_a($retrieved, $parentname));
        
        //test deep inheritance
        $classname  = 'MDB_QueryTool_Result_Result_Row_Ext2';
        $this->qt->useResult('object');
        $this->assertTrue($this->qt->setReturnClass($classname));
        $res       = $this->qt->getAll();
        $retrieved = $res->fetchRow();
        $this->assertEqual($classname, get_class($retrieved));
        $this->assertTrue(is_a($retrieved, $parentname));

        //test failure on invalid class
        $classname  = 'MDB_QueryTool_Result';
        $this->qt->useResult('object');
        $this->assertFalse($this->qt->setReturnClass($classname));
    }
    
    function test_Get_Result_Object() {
        $this->qt = new MDB_QT(TABLE_USER);
        $newData = $this->_getSampleData(1);
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $this->qt->useResult('object');
        $res = $this->qt->getAll();
        $retrieved = $res->fetchRow();
        $this->assertEqual($newData['name'], $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);

        //test multiple rows
        $newData2 = $this->_getSampleData(2);
        $id2      = $this->qt->add($newData2);
        $this->assertTrue($id2 != false);
        $newData2['id'] = $id2;
        $this->qt->useResult('object');
        $this->qt->setOrder('ID');
        $res = $this->qt->getAll();
        $retrieved = $res->getFirst();
        $this->assertEqual($newData['name'],       $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);
        $this->assertTrue($res->hasMore());
        $retrieved = $res->getNext();
        $this->assertEqual($newData2['name'],       $retrieved->name);
        $this->assertEqual($newData2['address_id'], $retrieved->address_id);
        //go backwards
        $retrieved = $res->getFirst();
        $this->assertEqual($newData['name'],       $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);
        $this->assertTrue($this->qt->remove($id2));


        //test getCol => scalar values instead of arrays
        $this->qt->useResult('object');
        $res = $this->qt->getCol('id');

        $retrieved = $res->fetchRow();
        $this->assertEqual($newData['id'], $retrieved);

        //test get() + fetchRow()
        $result = $this->qt->get($newData['id']);
        $this->assertTrue(is_a($result, 'MDB_QueryTool_Result_Object'));
        $retrieved = $result->fetchRow();
        $this->assertEqual($newData['id'], $retrieved->id);
        $this->assertEqual($newData['name'], $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);
        
        //test getNext()
        $result = $this->qt->get($newData['id']);
        $this->assertTrue(is_a($result, 'MDB_QueryTool_Result_Object'));
        $retrieved = $result->getNext();
        $this->assertEqual($newData['id'], $retrieved->id);
        $this->assertEqual($newData['name'], $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);
        
        //test hasMore()
        $this->assertFalse($result->hasMore());
        

        //test save()
        $appended_string = '_appended';
        $retrieved->name .= $appended_string;
        $this->assertTrue($retrieved->save());
        
        $result = $this->qt->get($newData['id']);
        $this->assertTrue(is_a($result, 'MDB_QueryTool_Result_Object'));
        $retrieved = $result->fetchRow();
        $this->assertTrue(is_a($retrieved, 'MDB_QueryTool_Result_Row'));
        $this->assertEqual($newData['id'], $retrieved->id);
        $this->assertEqual($newData['name'].$appended_string, $retrieved->name);
        $this->assertEqual($newData['address_id'], $retrieved->address_id);
    }

    function test_Get_New_Entity() {
        $this->qt = new MDB_QT(TABLE_USER);
        $this->qt->useResult('object');
        $entity  = $this->qt->newEntity();
        $newData = $this->_getSampleData(1);
        //Checking the class
        $this->assertTrue(is_a($entity, 'MDB_QueryTool_Result_Row'));
        //Checking variables of entity (Should have the same columns as keys in newData)
        $properties = get_object_vars($entity);
        foreach ($newData as $key=>$val) {
            $this->assertTrue(array_key_exists($key, $properties));
        }
        //Checking that there is no id
        $this->assertEqual('', $entity->id);
        //Setting some data and saving the entity
        foreach ($newData as $key=>$val) {
            $entity->$key = $val;
        }
        $entity->save();
        //Checking if it has now an id
        $this->assertTrue($entity->id!='');
    }

    function test_Remove_Entity() {
        $this->qt = new MDB_QT(TABLE_USER);
        $this->qt->useResult('object');
        $entity  = $this->qt->newEntity();
        $newData = $this->_getSampleData(1);
        //Creating an entity and saving it
        foreach ($newData as $key=>$val) {
            $entity->$key = $val;
        }
        $entity->save();
        //Checking if it has now an id
        $this->assertTrue($entity->id!='');
        $id = $entity->id;
        //Removing the entity
        $this->assertTrue($entity->remove());
        //Try to get removed entity
        $res = $this->qt->get($id);
        $this->assertFalse($res->getNext());
    }
    
    function test_Remove_Entity_With_Empty_Cols() {
        $this->qt = new MDB_QT(TABLE_USER);
        $this->qt->useResult('object');
        $entity  = $this->qt->newEntity();
        $newData = $this->_getSampleData(1);
        //Creating an entity and saving it
        $keys = array_keys($newData);
        $entity->$keys[0] = $newData[$keys[0]];
        $entity->$keys[1] = $newData[$keys[1]];
        $entity->save();
        //Checking if it has now an id
        $this->assertTrue($entity->id!='');
        $id = $entity->id;
        //Removing the entity
        $this->assertTrue($entity->remove());
        print_r($entity);
        //Try to get removed entity
        $res = $this->qt->get($id);
        $this->assertFalse($res->getNext());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfMDB_QueryTool_Result();
    $test->run(new HtmlReporter());
}
?>