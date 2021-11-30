<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The PHP Group                                     |
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
// $Id: xteacmd.php,v 1.3 2005/05/13 19:34:18 jeroend Exp $

/**
 *	Simple xtea command line utility.
 *	Simple xtea command line utility.
 *
 *	@package		Crypt_Xtea_Test
 *	@modulegroup	Crypt_Xtea_Test
 *	@module			xteacmd
 *	@access			public
 *
 *	@version		$Revision: 1.3 $
 *	@since			2004/Sep/30
 *	@author			Jeroen Derks <jeroen@derks.it>
 */

/** Crypt_Xtea class */
require_once 'Crypt/Xtea.php';

$flag_decrypt	= false;				// do encryption by default
$msg			= '';					// initialize message to encrypt/decrypt
$key			= '';					// initialize key to use

$prog = basename($_SERVER['argv'][0]);	// program name
$argc = $_SERVER['argc'];				// number of command line arguments
$argv = $_SERVER['argv'];				// command line arguments array

// process command line flags
while (1 < $argc && '-' == $argv[1]{0})
{
	$argc--;
	$arg = $argv[1];
	array_shift($argv);
	if ('--' == $arg)
		break;

	$arg = substr($arg, 1);
	while (0 < strlen($arg))
	{
		switch ($arg{0})
		{
			case 'd':	$flag_decrypt = true;	break;
			case 'e':	$flag_decrypt = false;	break;
		}
		$arg = substr($arg, 1);
	}
}

// check required parameters
if (2 >= $argc)
{
	echo "usage: $prog [-de] key msg [ msg ... ]\n" .
		 "\t-d\tdecrypt message\n" .
		 "\t-e\tencrypt message (default action)\n";
	exit(1);
}

// get key
$key = $argv[1];
$argc--;
array_shift($argv);

// get messages
while (1 < $argc)
{
	$msg	= $argv[1];
	$obj	= new Crypt_Xtea();

	if ($flag_decrypt)
	{
		$binmsg	= pack('H' . strlen($msg), $msg);
		$result	= $obj->decrypt($binmsg, $key);
	}
	else
	{
		$binmsg	= $obj->encrypt($msg, $key);
		$result = join('', unpack('H*', $binmsg));
	}

	// output result
	echo $result;

	// get next message
	$argc--;
	$arg = array_shift($argv);
}
?>
