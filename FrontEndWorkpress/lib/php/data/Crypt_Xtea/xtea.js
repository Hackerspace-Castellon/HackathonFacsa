/* ---------------------------------------------------------------------------------------- *
 * Initial code from http://www.simonshepherd.supanet.com/jstea.htm
 * Fixed to work as part of PEAR Crypt_Xtea package by Jeroen Derks <jeroen@derks.it>
 * ---------------------------------------------------------------------------------------- */
/* GLOBAL VARIABLE
------------------- */
 
/* CONVERSION FUNCTION
------------------- */
function DecToHex(x) {
	var s = '', x_ = !isNaN(Number(x)) ? Number(x) : 0;
	while( Boolean( x_ ) ) { s = '0123456789ABCDEF'.charAt( x_ & 0xf ) + s; x_ >>>= 4; }
	while ( s.length & 0x7 ) { s = '0' + s; } 
	return ( s );
}

/* ---------------------------------------------------------------------------------------- */

/* RUNDOWN ON THE TINY ENCRYPTION ALGORITHM
------------------- */
/* Tiny Encryption Algorithm (TEA) 

	- http://vader.brad.ac.uk/tea/tea.shtml

	The Tiny Encryption Algorithm (TEA) by David Wheeler and Roger Needham 
		of the Cambridge Computer Laboratory.

	Placed in the Public Domain by David Wheeler and Roger Needham.


	TEA is a Feistel cipher with XOR and AND addition as the non-linear mixing functions. 

	TEA takes 64 bits of data in v[0] and v[1], ( 2 x 4 bytes -> 8 ascii chars )
		and 128 bits of key in k[0] - k[3]. ( 4 x 4 bytes -> 16 bytes )

	The result is returned in w[0] and w[1]. ( 2 x 4 bytes -> 8 ascii chars )

	Returning the result separately makes implementation of cipher modes
		other than Electronic Code Book a little bit easier.

	TEA can be operated in any of the modes of DES.

	n is the number of iterations. 32 is ample, 16 is sufficient,
		as few as eight should be OK for most applications,
		especially ones where the data age quickly (real-time video, for example).

	The algorithm achieves good dispersion after six iterations.
	The iteration count can be made variable if required.

	delta is chosen to be the Golden ratio ((5/4)1/2 - 1/2 ~ 0.618034) multiplied by 2^32

	Which way round you call the functions is arbitrary:

	DK(EK(P)) = EK(DK(P)) where EK and DK are encryption and decryption under key K respectively


	This implementation follows the new variant developed in response 
		to limitations pointed out by David Wagner 1997

*/


function Encipher(p1, p2, k)
{
	var temp = new Array();
	temp[0] = 1; // an error flag
	temp[1] = new Number(p1);
	temp[2] = new Number(p2);

	var sum = 0;
	var delta = 0x9E3779B9;
	var n = 32;

	while ( n-- > 0 )
	{
		temp[1] = (temp[1] + ( ( temp[2] << 4 ^ ((temp[2] >> 5) & 0x07ffffff) ) + temp[2] ^ sum + k[ ( sum & 3 ) ] )) & 0xffffffff;
		sum = (sum + delta) & 0xffffffff;
		temp[2] = (temp[2] + ( ( temp[1] << 4 ^ ((temp[1] >> 5) & 0x07ffffff) ) + temp[1] ^ sum + k[ ( ((sum >> 11) & 0x001fffff) & 3 ) ] )) & 0xffffffff;
	}

	// for the error flag maybe check for negative numbers

	return( temp );

}


function Decipher(p1, p2, k)
{
	var temp = new Array();
	temp[0] = 1; // an error flag
	temp[1] = new Number(p1);	
	temp[2] = new Number(p2);

	// sum = delta << 5, in general sum = delta * n

	var sum   = 0xC6EF3720;
	var delta = 0x9E3779B9;
	var n     = 32;

	while ( n-- > 0 )
	{
		temp[2] = (temp[2] - ( ( temp[1] << 4 ^ ((temp[1] >> 5) & 0x07ffffff) ) + temp[1] ^ sum + k[ ( ((sum >> 11) & 0x001fffff) & 3 )] )) & 0xffffffff;
		sum = (sum - delta) & 0xffffffff;
		temp[1] = (temp[1] - ( ( temp[2] << 4 ^ ((temp[2] >> 5) & 0x07ffffff) ) + temp[2] ^ sum + k[ ( sum & 3 ) ] )) & 0xffffffff;

	}

	// for the error flag maybe check for negative numbers

	return( temp );

}


/* ---------------------------------------------------------------------------------------- */
/* WORKHORSE FUNCTION #1
------------------- */

function EncipherText(inString, key)
{
	// init local variables
	var p1D			= 0;
	var p2D			= 0;	
	var res			= null;
	var outString	= '';
	var tmp;
	var i;
	
	// initialize an error flag of 64 bits
	// include the newlines so it's even easier to spot
	// alternatively issue a report to the status field
	var errormark = "!!!!!!!\x0d\x0d!!!!!!!";

	// prefix the length of the string
	tmp = ''
		  + String.fromCharCode(( inString.length / 16777216 ) & 0xFF)
		  + String.fromCharCode(( inString.length / 65536 ) & 0xFF)
		  + String.fromCharCode(( inString.length / 256 ) & 0xFF)
		  + String.fromCharCode(inString.length & 0xFF)
		  ;
	inString = tmp + inString;

	// pad the input so that it's a multiple of 8
	while ( inString.length & 0x7 ) { inString += '\0'; }

	// pad the key so that it's a multiple of 16
	i = 0;
	while ( key.length & 0x15 ) { key += key.charAt(i++); }

	// create array from key
	tmp = key;
	key = new Array(key.length / 4);
	i	= 0;
	j	= 0;
	while ( i < tmp.length )
		key[j++] = (((tmp.charCodeAt(i++) & 0xFF) << 24) |
				    ((tmp.charCodeAt(i++) & 0xFF) << 16) |
				    ((tmp.charCodeAt(i++) & 0xFF) << 8) |
				    ((tmp.charCodeAt(i++) & 0xFF))) & 0xFFFFFFFF;

	i = 0;
	j = 0;
	k = new Array(4);
	while ( i < inString.length ) {

		// determine key selection
		if (j + 4 <= key.length) {
			k[0] = key[j];
			k[1] = key[j + 1];
			k[2] = key[j + 2];
			k[3] = key[j + 3];
		} else {
			k[0] = key[j % key.length];
			k[1] = key[(j + 1) % key.length];
			k[2] = key[(j + 2) % key.length];
			k[3] = key[(j + 3) % key.length];
		}
		j = (j + 4) % key.length;

		// slam 4 bytes into a dword 
		p1D  = inString.charCodeAt(i++) << 24;
		p1D |= inString.charCodeAt(i++) << 16;
		p1D |= inString.charCodeAt(i++) << 8;
		p1D |= inString.charCodeAt(i++);

		// mask off 32 bits to be safe
		// javascript numbers are 64 bit IEEE double doubles
		p1D &= 0xFFFFFFFF;

		// slam 4 bytes into a dword
		p2D  = inString.charCodeAt(i++) << 24;
		p2D |= inString.charCodeAt(i++) << 16;
		p2D |= inString.charCodeAt(i++) << 8;
		p2D |= inString.charCodeAt(i++);

		// mask off 32 bits to be safe
		// javascript numbers are 64 bit IEEE double doubles
		p2D &= 0xFFFFFFFF;

		// send dwords to be enciphered
		res = Encipher(p1D, p2D, k);

		// check the validity flag
		// convert the results to hex to facilitate deciphering - 16 chars generated per turn
		// append the hex values to the output buffer 
		// do not include any new lines - the form is set to wrap

		// the validity flag defaults to true because I'm not certain what to check for ;-)

		outString += ( res[0] ? '' + DecToHex(res[1]) + DecToHex(res[2]) : errormark );

		// later perhaps the outString should be chunked up
		// along the lines of B64 email attachments
		// although 0-9 and A-F are 6 and 7 bit values respectively
		// it's really a question of post limitations on the http server

		// clear the temporary variables
		p1D = 0; p2D = 0; res = null;

	}

	return outString;

}

/* ---------------------------------------------------------------------------------------- */
/* WORKHORSE FUNCTION #2
------------------- */

function DecipherText(inString, key)
{
	// init local variables
	var p3H = ''; var p4H = '';
	var p3D = 0;  var p4D = 0;
	var res = null; var outString = '';
	var i;
	var j;
	var tmp;

	// pad the key so that it's a multiple of 16
	i = 0;
	while ( key.length & 0x15 ) { key += key.charAt(i++); }

	// create array from key
	tmp = key;
	key = new Array(key.length / 4);
	i	= 0;
	j	= 0;
	while ( i < tmp.length )
		key[j++] = (((tmp.charCodeAt(i++) & 0xFF) << 24) |
				    ((tmp.charCodeAt(i++) & 0xFF) << 16) |
				    ((tmp.charCodeAt(i++) & 0xFF) << 8) |
				    ((tmp.charCodeAt(i++) & 0xFF))) & 0xFFFFFFFF;

	// loop through input string
	i = 0; 
	while ( i < inString.length ) {

		// should check for our errormarker too!

		// 8 hex chars make a dword 
		// - unloop de loop - it's faster
		p3H += inString.charAt(i++); // 1
		p3H += inString.charAt(i++); // 2
		p3H += inString.charAt(i++); // 3
		p3H += inString.charAt(i++); // 4
		p3H += inString.charAt(i++); // 5
		p3H += inString.charAt(i++); // 6
		p3H += inString.charAt(i++); // 7
		p3H += inString.charAt(i++); // 8

		// 8 hex chars make a dword 
		// - unloop de loop - it's faster
		p4H += inString.charAt(i++); // 1
		p4H += inString.charAt(i++); // 2
		p4H += inString.charAt(i++); // 3
		p4H += inString.charAt(i++); // 4
		p4H += inString.charAt(i++); // 5
		p4H += inString.charAt(i++); // 6
		p4H += inString.charAt(i++); // 7
		p4H += inString.charAt(i++); // 8

		// convert hex strings to dwords
		p3D = parseInt(p3H,16);
		p4D = parseInt(p4H,16);

		// pass dwords to decipher routine
		res = Decipher(p3D, p4D, key);

		// transform results back into alphanumic characters
		// check validity flag - always defaults true ...
		if ( res[0] ) {

			// unpack first dword
			outString += String.fromCharCode( ( res[1] & 0xFF000000 ) >> 24 );
			outString += String.fromCharCode( ( res[1] & 0x00FF0000 ) >> 16 );
			outString += String.fromCharCode( ( res[1] & 0x0000FF00 ) >>  8 );
			outString += String.fromCharCode( ( res[1] & 0x000000FF ) );

			// unpack second dword
			outString += String.fromCharCode( ( res[2] & 0xFF000000 ) >> 24 );
			outString += String.fromCharCode( ( res[2] & 0x00FF0000 ) >> 16 );
			outString += String.fromCharCode( ( res[2] & 0x0000FF00 ) >>  8 );
			outString += String.fromCharCode( ( res[2] & 0x000000FF ) );

		}
		
		// reset temporary variables
		p3H = ''; p4H = '';
		p3D = 0;  p4D = 0;
		res = null;
	}

	// get length
	tmp = (((outString.charCodeAt(0) & 0xFF) << 24) |
		   ((outString.charCodeAt(1) & 0xFF) << 16) |
		   ((outString.charCodeAt(2) & 0xFF) << 8) |
		   ((outString.charCodeAt(3) & 0xFF))) & 0xffffffff;

	return outString.substring(4, 4 + tmp);
}
