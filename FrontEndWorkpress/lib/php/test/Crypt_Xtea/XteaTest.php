<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jeroen Derks <jeroen@derks.it>                              |
// +----------------------------------------------------------------------+
//
// $Id: XteaTest.php,v 1.12 2004/10/04 19:51:56 jeroend Exp $

/** Xtea class */
require_once 'Crypt/Xtea.php';
/** PHPUnit class */
require_once 'PHPUnit.php';


/**
 *  Tester class for Xtea class.
 *  Tester class for Xtea class.
 *
 *  @package    Crypt_Xtea_Test
 *  @access     public
 *
 *  @version    $Revision: 1.12 $
 *  @since      2002/Aug/28
 *  @author     Jeroen Derks <jeroen@derks.it>
 */
class Crypt_XteaTest extends PHPUnit_TestCase
{
    var $obj;
    var $data;
    var $key;

    function Crypt_XteaTest($method) {
        global $profiling;

        $this->profiling = $profiling;
        $this->PHPUnit_TestCase($method);
    }

    function setUp() {
        $this->obj = new Crypt_Xtea();
        $this->key = '0123456789abcdeffedcba9876543210';

        if (!$this->profiling) $this->startTimer('data');
        //$this->data = '1'; return;
        //$this->data = '01234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; return;
        $this->data = '';
        for ($i = 0; $i < 256; ++$i) {
            $this->data .= chr($i & 0xff);
        }
        if (!$this->profiling) $this->endTimer('data');
    }

    function testIter() {
        $this->obj->setIter(36);
        $this->assertEquals(36, $this->obj->getIter());
    }

    function testCrypt() {
        $start = IsSet($this->profiling) && $this->profiling ? strlen($this->data) - 3 : 0;
        if (0 == $start) {
            $walker = '-\|/';
            echo "Testing... ";
            flush();
        }

        for ($i = $start; $i < strlen($this->data); ++$i)
        {
            if (0 == $start) {
                echo $walker{$i % 4} . sprintf(" %4u", $i) . str_repeat("", 6);
                flush();
            }

            if (!$this->profiling) $this->startTimer('data');
            $data = substr($this->data, 0, $i);
            if (!$this->profiling) $this->endTimer('data');

            if (!$this->profiling) $this->startTimer('encrypt');
            $encrypted  = $this->obj->encrypt($data, $this->key);
            if (!$this->profiling) $this->endTimer('encrypt');
            if (!$this->profiling) $this->startTimer('decrypt');
            $decrypted  = $this->obj->decrypt($encrypted, $this->key);
            if (!$this->profiling) $this->endTimer('decrypt');

            if (!$this->profiling) $this->startTimer('assert');
            $this->assertEquals(strlen($data), strlen($decrypted));
            $this->assertEquals($data, $decrypted, "run $i failed: expected '***' (".strlen($data)."), actual '***' (".strlen($decrypted).")");
            if (!$this->profiling) $this->endTimer('assert');
        }

        if (0 == $start) {
            echo "                        ";
            flush();
        }
    }

    function _testHuge() {
        set_time_limit(99999);

        if (!$this->profiling) $this->startTimer('data');
        $data = '';
        for($i = 0; $i < 1024 * 1024; ++$i)
            $data .= chr($i & 0xff);
        if (!$this->profiling) $this->endTimer('data');

        if (!$this->profiling) $this->startTimer('encrypt');
        $encrypted = $this->obj->encrypt($data, $this->key);
        if (!$this->profiling) $this->endTimer('encrypt');
        if (!$this->profiling) $this->startTimer('decrypt');
        $decrypted = $this->obj->decrypt($encrypted, $this->key);
        if (!$this->profiling) $this->endTimer('decrypt');

        if (!$this->profiling) $this->startTimer('assert');
        $this->assertEquals(strlen($data), strlen($decrypted));
        $this->assertEquals($data, $decrypted, "run $i failed: expected '***' (".strlen($data)."), actual '***' (".strlen($decrypted).")");
        if (!$this->profiling) $this->endTimer('assert');
    }

    function testCipher() {
        if (!$this->profiling) $this->startTimer('data');
        $v = array(0x1, 0x61000000);
        $v = array(0x12345678, 0xffffffff);
        $w = array(0, 0);
        $k = array(0x01234567, 0x89abcdef, 0x01234567, 0x89abcdef);
        if (!$this->profiling) $this->endTimer('data');

        printf("original:\n");
        printf("v[0] = %10lu (0x%8lx)\n", $v[0], $v[0]);
        printf("v[1] = %10lu (0x%8lx)\n", $v[1], $v[1]);

        if (!$this->profiling) $this->startTimer('encipher');
        $this->obj->_encipherLong($v[0], $v[1], $w, $k);
        if (!$this->profiling) $this->endTimer('encipher');

        printf("encrypted:\n");
        printf("w[0] = %10lu (0x%8lx)\n", $w[0], $w[0]);
        printf("w[1] = %10lu (0x%8lx)\n", $w[1], $w[1]);

        if (!$this->profiling) $this->startTimer('decipher');
        $this->obj->_decipherLong($w[0], $w[1], $r, $k);
        if (!$this->profiling) $this->endTimer('decipher');

        printf("decrypted:\n");
        printf("v[0] = %10lu (0x%8lx)\n", $r[0], $r[0]);
        printf("v[1] = %10lu (0x%8lx)\n", $r[1], $r[1]);

        if (!$this->profiling) $this->startTimer('assert');
        $this->assertEquals((int) $v[0], $r[0], sprintf("$v[0] (%lu = 0x%lx) != $r[0] (%lu = 0x%lx)", $v[0], $v[0], $r[0], $r[0]));
        $this->assertEquals((int) $v[1], $r[1], sprintf("$v[1] (%lu = 0x%lx) != $r[1] (%lu = 0x%lx)", $v[1], $v[1], $r[1], $r[1]));
        if (!$this->profiling) $this->endTimer('assert');
    }

    function testShift() {
        $x = -12345678;
        $n = 2;
        $y = $this->obj->_rshift($x, $n);
        $z = $x << $n;
        $this->assertEquals((int) 0x3fd0e7ac, $y, "$x >> $n");
        $this->assertEquals((int) 0xfd0e7ac8, $z, "$x << $n");

        $x = 0xffffffff;
        $n = 5;
        $y = $this->obj->_rshift($x, $n);
        $z = $x << $n;
        $this->assertEquals((int) 0x7ffffff, $y, "$x >> $n");
        $this->assertEquals((int) 0xffffffe0, $z, "$x << $n");

        $x = 0x90000000;
        $n = 2;
        $y = $this->obj->_rshift($x, $n);
        $z = $x << $n;
        $this->assertEquals((int) 0x24000000, $y, "$x >> $n");
        $this->assertEquals((int) 0x40000000, $z, "$x << $n");
    }

    function testAdd() {
        $result = $this->obj->_add(-0x12345678, 0xfffffffe, 0x80000000, 0xfedcba98, 0xabcdef01);
        $this->assertEquals((int) 0x1876531f, $result, '_add(-0x12345678, 0xfffffffe, 0x80000000, 0xfedcba98, 0xabcdef01)');
    }

    function tearDown() {
        $this->obj = NULL;
    }

    function startTimer($label) {
        $time0 = strtok(microtime(), ' ');
        $time1 = strtok(' ');

        $timer              =& $this->_getTimer();
        $timer[$label][]    = (string) $time1 . substr($time0, 1);
    }

    function endTimer($label) {
        $time0 = strtok(microtime(), ' ');
        $time1 = strtok(' ');

        $timer              =& $this->_getTimer();
        $timer[$label][]    = (string) $time1 . substr($time0, 1);
    }

    /**
     *  @static
     */
    function &_getTimer() {
        static $timing = NULL;

        if (!IsSet($timing))
        {
            $time0 = strtok(microtime(), ' ');
            $time1 = strtok(' ');

            // start _global
            $timing = array('_global' => array((string) $time1 . substr($time0, 1)));
        }

        return $timing;
    }

    /**
     *  @static
     */
    function getTimings() {
        $time0 = strtok(microtime(), ' ');
        $time1 = strtok(' ');

        $timer                  =& Crypt_XteaTest::_getTimer();
        $timer['_global'][1]    = (string) $time1 . substr($time0, 1);
        $labels                 =  array_keys($timer);
        $results                =  array();

        // calculate times and calls
        sort($labels);
        foreach ($labels as $label) {
            $results[$label]['time']    = 0.0;
            $results[$label]['calls']   = 0;

            $n = count($timer[$label]);
            for ($i = 0; $i < $n; $i += 2) {
                $results[$label]['time'] += (float) $timer[$label][$i + 1] - $timer[$label][$i];
                ++$results[$label]['calls'];
            }
        }

        // calculate percentages
        foreach ($labels as $label) {
            $results[$label]['perc'] = ( $results[$label]['time'] * 100.0 ) / $results['_global']['time'];
        }

        // output results
        echo "Timing results:\n" .
            sprintf("%-20s %13s %8s %10s%%\n", 'Label', 'time', '#calls', 'perc') .
            str_repeat('-', 57) . "\n";
        foreach ($labels as $label) {
            printf("%-20s %13s %8lu %10s%%\n",
                    $label,
                    sprintf("%.8f", $results[$label]['time']),
                    $results[$label]['calls'],
                    sprintf("%.6f", $results[$label]['perc']));
        }
    }
}

?>
