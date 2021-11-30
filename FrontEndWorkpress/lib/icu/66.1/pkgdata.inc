GENCCODE_ASSEMBLY_TYPE=-a gcc
SO=so
SOBJ=so
A=a
LIBPREFIX=lib
LIB_EXT_ORDER=.66.1
COMPILE=gcc -I/opt/lampp/include/c-client -I/opt/lampp/include/libpng -I/opt/lampp/include/freetype2 -O3 -L/opt/lampp/lib -I/opt/lampp/include -I/opt/lampp/include/ncurses -D_REENTRANT  -DU_HAVE_ELF_H=1 -DU_HAVE_STRTOD_L=1 -DU_HAVE_XLOCALE_H=1  -DU_ATTRIBUTE_DEPRECATED= -O3 -I/opt/lampp/include/c-client -I/opt/lampp/include/libpng -I/opt/lampp/include/freetype2 -O3 -L/opt/lampp/lib -I/opt/lampp/include -I/opt/lampp/include/ncurses -std=c11 -Wall -pedantic -Wshadow -Wpointer-arith -Wmissing-prototypes -Wwrite-strings   -c
LIBFLAGS=-I/opt/lampp/include -DPIC -fPIC
GENLIB=gcc -O3 -I/opt/lampp/include/c-client -I/opt/lampp/include/libpng -I/opt/lampp/include/freetype2 -O3 -L/opt/lampp/lib -I/opt/lampp/include -I/opt/lampp/include/ncurses -std=c11 -Wall -pedantic -Wshadow -Wpointer-arith -Wmissing-prototypes -Wwrite-strings   -Wl,--rpath -Wl,/opt/lampp/lib -L/opt/lampp/lib -I/opt/lampp/include -L/opt/lampp/lib -L/opt/lampp -Wl,-zorigin,-rpath,'$'ORIGIN -Wl,-rpath,/opt/lampp/lib -shared -Wl,-Bsymbolic
LDICUDTFLAGS=-nodefaultlibs -nostdlib
LD_SONAME=-Wl,-soname -Wl,
RPATH_FLAGS=
BIR_LDFLAGS=-Wl,-Bsymbolic
AR=ar
ARFLAGS=r
RANLIB=ranlib
INSTALL_CMD=/bin/install -c
