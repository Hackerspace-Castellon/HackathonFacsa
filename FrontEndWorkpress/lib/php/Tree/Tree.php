<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Wolfram Kriesing <wolfram@kriesing.de>                      |
// +----------------------------------------------------------------------+
//
//  $Id: Tree.php 320703 2011-12-08 22:08:40Z danielc $

/**
*   the DB interface to the tree class
*
*   @access     public
*   @author     Wolfram Kriesing <wolfram@kriesing.de>
*   @version    2001/06/27
*   @package    Tree
*/
class Tree
{
    // {{{ setupMemory()

    /**
     * setup an object which works on trees that are temporarily saved in
     * memory dont use with huge trees, suggested is a maximum size of tree
     * of 1000-5000 elements since the entire tree is read at once
     * from the data source. Use this to instanciate a class of a tree, i.e:
     * - need the entire tree at once
     * - want to work on the tree w/o db-access for every call
     * since this set of classes loads the entire tree into the memory, you
     * should be aware about the size of the tree you work on using this class.
     * For one you should know how efficient this kind of tree class is on
     * your data source (i.e. db) and what effect it has reading the entire
     * tree at once. On small trees, like upto about 1000 elements an instance
     * of this class will give you very powerful means to manage/modify
     * the tree no matter from which data source it comes, either
     * from a nested-DB, simple-DB, XML-File/String or whatever is implemented.
     *
     * @version    2002/02/05
     * @access public
     * @author Wolfram Kriesing <wolfram@kriesing.de>
     * @param  string  the kind of data source this class shall work on
     *                 initially, you can still switch later, by using
     *                 "setDataSource" to i.e. export data from a DB to XML,
     *                 or whatever implementation might exist some day.
     *                 currently available types are: 'DBsimple', 'XML'
     *
     * @param  mixed   the dsn, or filename, etc., empty i.e. for XML
     *                 if you use setupByRawData
     */
    function &setupMemory($type, $dsn = '', $options = array())
    {
        // if anyone knows a better name it would be great to change it.
        // since "setupMemory" kind of reflects it but i think it's not obvious
        // if you dont know what is meant
        include_once 'Tree/Memory.php';
        $memory = &new Tree_Memory($type, $dsn, $options);
        return $memory;
    }

    // }}}
    // {{{ setupDynamic()

    /**
     * setup an object that works on trees where each element(s) are read
     * on demand from the given data source actually this was intended to serve
     * for nested trees which are read from the db up on demand, since it does
     * not make sense to read a huge tree into the memory when you only want
     * to access one level of this tree. In short: an instance returned by
     * this method works on a tree by mapping every request (such as getChild,
     * getParent ...) to the data source defined to work on.
     *
     * @version  2002/02/05
     * @access   public
     * @author   Wolfram Kriesing <wolfram@kriesing.de>
     * @param    string  the type of tree you want, currently only DBnested
     *                   is supported
     * @param    string  the connection string, for DB* its a DSN, for XML
     *                   it would be the filename
     * @param    array   the options you want to set
     */
    function &setupDynamic($type, $dsn, $options = array())
    {
        // "dynamic" stands for retreiving a tree(chunk) dynamically when needed,
        // better name would be great :-)
        include_once "Tree/Dynamic/$type.php";
        $className = 'Tree_Dynamic_'.$type;
        $obj = & new $className($dsn, $options);
        return $obj;
    }

    // }}}
    // {{{ setup()

    /**
     * this is just a wrapper around the two setup methods above
     * some example calls:
     * <code>
     * $tree = Tree::setup(
     *              'Dynamic_DBnested',
     *              'mysql://root@localhost/test',
     *              array('table'=>'nestedTree')
     *          );
     * $tree = Tree::setup(
     *              'Memory_DBsimple',
     *              'mysql://root@localhost/test',
     *              array('table'=>'simpleTree')
     *          );
     * $tree = Tree::setup(
     *                  'Memory_XML',
     *                  '/path/to/some/xml/file.xml'
     *          );
     * </code>
     *
     * you can call the following too, but the functions/classes are not
     * implemented yet or not finished.
     * <code>
     * $tree = Tree::setup(
     *              'Memory_DBnested',
     *              'mysql://root@localhost/test',
     *              array('table'=>'nestedTree')
     *          );
     * $tree = Tree::setup('Dynamic_XML', '/path/to/some/xml/file.xml');
     * </code>
     *
     * and those would be really cool to have one day:
     * LDAP, Filesystem, WSDL, ...
     *
     * @access    private
     * @version   2002/03/07
     * @author    Wolfram Kriesing <wolfram@kriesing.de>
     * @param string    the type of tree you want, currently only Memory|
     *                  Dynamic_DBnested|XML|... is supported
     * @param string    the connection string, for DB* its a DSN,
     *                  for XML it would be the filename
     * @param array     the options you want to set
     */
    function setup($type, $dsn = '', $options = array())
    {
        $type = explode('_', $type);
        $method = 'setup'.$type[0];
        return Tree::$method($type[1], $dsn, $options);
    }

    // }}}
    // {{{ isError()

    /**
      * Tell whether a result code from a DB method is an error
      *
      * @param int  result code
      *
      * @return bool whether $value is an error
      *
      * @access public
      */
    function isError($value)
    {
        return (is_object($value) &&
                (is_a($value, 'tree_error') ||
                 is_subclass_of($value, 'tree_error')));
    }

    // }}}
}