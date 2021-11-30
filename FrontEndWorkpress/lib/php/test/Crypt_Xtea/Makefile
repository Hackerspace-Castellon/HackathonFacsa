PHP=php
PHP_CMD=$(PHP) $(PHP_FLAGS)
SCRIPTS=tester.php XteaTest.php
KEY=123456789
MSG=This is a test to test Xtea class
REMOTES=gw:1 indy:1

all:
	@echo No default target...

test:			test.dummy		
		

test.dummy:		$(SCRIPTS)
				nice -20 $(PHP_CMD) tester.php > test.out

profile:		profiled.dummy

profiled.dummy:	$(SCRIPTS)
				nice -20 $(PHP_CMD) tester.php 1 > profiled.out

encrypt:		xteacmd.php
				@echo -n "Give text to encrypt: "; read line; $(PHP_CMD) xteacmd.php -e $(KEY) "$$line"; echo

decrypt:		xteacmd.php
				@echo -n "Give text to decrypt: "; read line; $(PHP_CMD) xteacmd.php -d $(KEY) "$$line"; echo

simple:			xteacmd.php
				enc=`$(PHP_CMD) xteacmd.php -e $(KEY) "$(MSG)"`; echo encrypted="$$enc";	\
				dec=`$(PHP_CMD) xteacmd.php -d $(KEY) "$$enc"`; echo "decrypted=$$dec"

full:
				$(PHP_CMD) full.php > x
				for i in $(REMOTES); do	\
					echo "doing '$$i' ... ";	\
					scp full.php $$i &&	\
					export host=`echo $$i | cut -d: -f 1` &&	\
					export path=`echo $$i | cut -d: -f 2-` &&	\
					echo "ssh $$host \"cd $$path && $(PHP_CMD) full.php > x\"" &&	\
					ssh $$host "cd $$path && $(PHP_CMD) full.php > x" &&	\
					echo scp $$i/x x.$$host &&	\
					scp $$i/x x.$$host && \
					if diff x x.$$host > /dev/null; then echo $$host ok; else echo different output from $$host; fi;	\
				done
