# 
# /*
#  * *********** WARNING **************
#  * This file generated by My::WrapXS/2.13
#  * Any changes made here will be lost
#  * ***********************************
#  * 1. /opt/lampp/lib/perl5/site_perl/5.32.1/ExtUtils/XSBuilder/WrapXS.pm:52
#  * 2. /opt/lampp/lib/perl5/site_perl/5.32.1/ExtUtils/XSBuilder/WrapXS.pm:2068
#  * 3. Makefile.PL:193
#  */
# 


package APR::Request::Error;
require DynaLoader ;

use strict;
use warnings FATAL => 'all';

use vars qw{$VERSION @ISA} ;

push @ISA, 'DynaLoader' ;
$VERSION = '2.13';
bootstrap APR::Request::Error $VERSION ;

use APR::Request;
use APR::Error;
our @ISA = qw/APR::Error APR::Request/;



1;
__END__
