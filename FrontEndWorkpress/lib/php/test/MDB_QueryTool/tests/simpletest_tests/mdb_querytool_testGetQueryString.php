<?php
// $Id: mdb_querytool_testGetQueryString.php 257971 2008-04-20 15:27:22Z quipo $

require_once dirname(__FILE__).'/mdb_querytool_test_base.php';


class TestOfMDB_QueryTool_GetQueryString extends TestOfMDB_QueryTool
{
    function TestOfMDB_QueryTool_GetQueryString($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function test_selectAll() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question.id AS id,question.question AS question FROM question';
        } else {
            $expected = 'SELECT question.'.$this->qt->db->quoteIdentifier('id').' AS '.$this->qt->db->quoteIdentifier('id')
                       .',question.'.$this->qt->db->quoteIdentifier('question').' AS '.$this->qt->db->quoteIdentifier('question')
                       .' FROM '.$this->qt->db->quoteIdentifier('question');
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectWithWhere() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $this->qt->setWhere('id=1');
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question.id AS id,question.question AS question FROM question WHERE id=1';
        } else {
            $expected = 'SELECT question.'.$this->qt->db->quoteIdentifier('id').' AS '.$this->qt->db->quoteIdentifier('id')
                       .',question.'.$this->qt->db->quoteIdentifier('question').' AS '.$this->qt->db->quoteIdentifier('question')
                       .' FROM '.$this->qt->db->quoteIdentifier('question')
                       .' WHERE id=1';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectWithJoin() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $joinOn = TABLE_QUESTION.'.id='.TABLE_ANSWER.'.question_id';
        $this->qt->setJoin(TABLE_ANSWER, $joinOn, 'left');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT answer.id AS t_answer_id,answer.answer AS t_answer_answer,answer.question_id AS t_answer_question_id,question.id AS id,question.question AS question FROM question LEFT JOIN answer ON question.id=answer.question_id';
        } else {
            $expected = 'SELECT answer.'.$this->qt->db->quoteIdentifier('id').' AS '.$this->qt->db->quoteIdentifier('_answer_id')
                       .',answer.'.$this->qt->db->quoteIdentifier('answer').' AS '.$this->qt->db->quoteIdentifier('_answer_answer')
                       .',answer.'.$this->qt->db->quoteIdentifier('question_id').' AS '.$this->qt->db->quoteIdentifier('_answer_question_id')
                       .',question.'.$this->qt->db->quoteIdentifier('id').' AS '.$this->qt->db->quoteIdentifier('id')
                       .',question.'.$this->qt->db->quoteIdentifier('question').' AS '.$this->qt->db->quoteIdentifier('question')
                       .' FROM '.$this->qt->db->quoteIdentifier('question')
                       .' LEFT JOIN answer ON question.id=answer.question_id';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectOneColumn() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $this->qt->setWhere('id=1');
        $this->qt->setSelect('id');
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT id FROM '.$this->qt->db->quoteIdentifier('question')
                .' WHERE id=1';
        } else {
            $expected = 'SELECT '.$this->qt->db->quoteIdentifier('id')
                .' FROM '.$this->qt->db->quoteIdentifier('question')
                .' WHERE id=1';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectTwoColumns() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $this->qt->setWhere('id=1');
        $this->qt->setSelect('id,answer');
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT id,answer FROM question WHERE id=1';
        } else {
            $expected = 'SELECT '.$this->qt->db->quoteIdentifier('id')
                        .','.$this->qt->db->quoteIdentifier('answer')
                        .' FROM '.$this->qt->db->quoteIdentifier('question')
                        .' WHERE id=1';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_prependTableName() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $table = TABLE_QUESTION;

        $fieldlist = 'question';
        $fieldlist = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $this->assertEqual($fieldlist, TABLE_QUESTION.'.question');

        $fieldlist = 'fieldname1,question';
        $fieldlist = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $this->assertEqual($fieldlist, 'fieldname1,'.TABLE_QUESTION.'.question');
        
        $fieldlist = 'fieldname1,'.TABLE_QUESTION.'.question,fieldname2';
        $fieldlist = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $this->assertEqual($fieldlist, 'fieldname1,'.TABLE_QUESTION.'.question,fieldname2');
    }
    function test_quoteIdentifierWithFunctions() {
        $this->qt =& new MDB_QT(TABLE_QUESTION, $GLOBALS['DB_OPTIONS']);
        $table = TABLE_QUESTION;

        $this->qt->setSelect('question, COUNT(DISTINCT id) AS num_questions');
        $this->qt->setGroup('question');
        
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question,COUNT(DISTINCT id) AS num_questions FROM question  GROUP BY question.question';
        } else {
            $expected = 'SELECT '.$this->qt->db->quoteIdentifier('question')
                       .',COUNT(DISTINCT id) AS '.$this->qt->db->quoteIdentifier('num_questions')
                       .' FROM '.$this->qt->db->quoteIdentifier('question')
                       .'  GROUP BY question.question';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_rawMode_bug10160() {
        //This test is based on Bug 10160
        include_once 'MDB2.php';
        $db_options = $GLOBALS['DB_OPTIONS'];
        if (array_key_exists('DatabasePath', $db_options)) {
            //ibase
            $db_options['database_path']      = $db_options['DatabasePath'];
            $db_options['database_extension'] = $db_options['DatabaseExtension'];
            $db_options['portability']        = MDB2_PORTABILITY_ALL;
            unset($db_options['DatabasePath']);
            unset($db_options['DatabaseExtension']);
            unset($db_options['optimize']);
        }
        $query = new MDB_QueryTool(DB_DSN, $db_options, 2);
        $query->setOption('raw',true);
        $query->setSelect("t.playerid, concat(namelast,', ',namefirst) as Name, sum(h)/sum(ab) as AVG");
        $query->setTable("batting t");
        $query->setLeftJoin("master m","m.playerid=t.playerid");
        $query->setWhere("yearid=2006 and teamid='NYN'");
        $query->setGroup("playerid");
        $expected = "SELECT t.playerid,concat(namelast,',',namefirst) AS Name,sum(h)/sum(ab) AS AVG FROM batting t LEFT JOIN master m ON m.playerid=t.playerid WHERE yearid=2006 and teamid='NYN' GROUP BY playerid";
        $this->assertEqual($expected, $query->getQueryString());
    }
    function test_bug12353() {
        $this->qt =& new MDB_QT(TABLE_QUESTION);
        $table = TABLE_QUESTION;

        $this->qt->setSelect('_spruch, if(length(_spruch) > 50, concat(left(_spruch, 50), "..."), _spruch) as _kurztext');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT _spruch,if(length(_spruch) > 50,concat(left(_spruch,50),"..."),_spruch) AS _kurztext FROM '.TABLE_QUESTION;
        } else {
            $expected = 'SELECT '.$this->qt->_quoteIdentifier('_spruch')
                       .',if(length(_spruch) > 50,concat(left(_spruch,50),"..."),_spruch) AS '
                       .$this->qt->_quoteIdentifier('_kurztext')
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION);
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_bug13694() {
        $this->qt =& new MDB_QT(TABLE_QUESTION);
        $table = TABLE_QUESTION;
        $this->qt->setSelect('sum( a ) AS suma, count( CAST ( b AS DATE ) ) AS dateb, CAST( b AS DATE) AS datebgb');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT sum( a ) AS suma,count( CAST ( b AS DATE ) ) AS dateb,CAST( b AS DATE) AS datebgb FROM '.TABLE_QUESTION;
        } else {
            $expected = 'SELECT sum( a ) AS '.$this->qt->_quoteIdentifier('suma')
                       .',count( CAST ( b AS DATE ) ) AS '.$this->qt->_quoteIdentifier('dateb')
                       .',CAST( b AS DATE) AS '.$this->qt->_quoteIdentifier('datebgb')
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION);
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfMDB_QueryTool_GetQueryString();
    $test->run(new HtmlReporter());
}
?>