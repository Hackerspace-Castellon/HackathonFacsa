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
// $Id: tester.php,v 1.6 2004/10/04 19:51:56 jeroend Exp $

/**
 *	Module test using PHPUnit.
 *	Module test using PHPUnit.
 *
 *	@package		Crypt_Xtea_Test
 *	@modulegroup	Crypt_Xtea_Test
 *	@module			tester
 *	@access			public
 *
 *	@version		$Revision: 1.6 $
 *	@since			2002/Aug/28
 *	@author			Jeroen Derks <jeroen@derks.it>
 */

// check parameter
if (IsSet($_SERVER['argc']) && 1 < $_SERVER['argc'] && $_SERVER['argv'][1])
{
	// check for xdebug presence to enable profiling
	if (extension_loaded('xdebug'))
	{
		xdebug_start_profiling();
		$profiling = true;
		echo "Profiling enabled.\n";
		flush();
	}
}

/** XteaTest class */
require_once 'XteaTest.php';

 
$suite = new PHPUnit_TestSuite('Crypt_XteaTest');
$result = PHPUnit::run($suite);
echo $result->toString();

// check for profiling to show results
if ($profiling)
	xdebug_dump_function_profile(XDEBUG_PROFILER_FS_SUM);
else
	Crypt_XteaTest::getTimings();
?>
