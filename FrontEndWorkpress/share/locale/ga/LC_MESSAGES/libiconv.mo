??    /      ?  C           C     9   ]  o   ?  B     m   J  ?   ?  \   ?  ;   U  P   ?  [   ?     >  @   A  N   ?  J   ?  D     d   a  ?   ?  :   a	     ?	     ?	     ?	  0   ?	     ?	  5   
  	   ;
     E
  ?   Z
  )   "  "   L  1   o  +   ?  &   ?  A   ?  ;   6     r  /   ?  7   ?  3   ?  :     ;   Y  $   ?     ?     ?     ?       2     "  G  E   j  D   ?  m   ?  G   c  n   ?  C     ]   ^  M   ?  Z   
  Z   e     ?  B   ?  R     R   Y  E   ?  m   ?  ?  `  4   V     ?     ?     ?  .   ?     ?  '   ?     "     /  ?   F  ,   /  +   \  5   ?  <   ?  ;   ?  F   7  9   ~     ?  8   ?  J   ?  B   D  L   ?  M   ?  &   "      I      j     ?     ?  X   ?           -                	   .          '                 
   !   ,   +                   )          &               #                         "           /                            %                           $      *   (                 --byte-subst=FORMATSTRING   substitution for unconvertible bytes
   --help                      display this help and exit
   --unicode-subst=FORMATSTRING
                              substitution for unconvertible Unicode characters
   --version                   output version information and exit
   --widechar-subst=FORMATSTRING
                              substitution for unconvertible wide characters
   -c                          discard unconvertible characters
   -f ENCODING, --from-code=ENCODING
                              the encoding of the input
   -l, --list                  list the supported encodings
   -s, --silent                suppress error messages about conversion problems
   -t ENCODING, --to-code=ENCODING
                              the encoding of the output
 %s %s argument: A format directive with a size is not allowed here. %s argument: A format directive with a variable precision is not allowed here. %s argument: A format directive with a variable width is not allowed here. %s argument: The character '%c' is not a valid conversion specifier. %s argument: The character that terminates the format directive is not a valid conversion specifier. %s argument: The format string consumes more than one argument: %u argument. %s argument: The format string consumes more than one argument: %u arguments. %s argument: The string ends in the middle of a directive. %s: I/O error %s:%u:%u %s:%u:%u: cannot convert %s:%u:%u: incomplete character or shift sequence (stdin) Converts text from one encoding to another encoding.
 I/O error Informative output:
 License GPLv3+: GNU GPL version 3 or later <http://gnu.org/licenses/gpl.html>
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.
 Options controlling conversion problems:
 Options controlling error output:
 Options controlling the input and output format:
 Report bugs to <bug-gnu-libiconv@gnu.org>.
 Try '%s --help' for more information.
 Usage: %s [OPTION...] [-f ENCODING] [-t ENCODING] [INPUTFILE...]
 Usage: iconv [-c] [-s] [-f fromcode] [-t tocode] [file ...] Written by %s.
 cannot convert byte substitution to Unicode: %s cannot convert byte substitution to target encoding: %s cannot convert byte substitution to wide string: %s cannot convert unicode substitution to target encoding: %s cannot convert widechar substitution to target encoding: %s conversion from %s to %s unsupported conversion from %s unsupported conversion to %s unsupported or:    %s -l
 or:    iconv -l try '%s -l' to get the list of supported encodings Project-Id-Version: libiconv 1.15-pre1
Report-Msgid-Bugs-To: bug-gnu-libiconv@gnu.org
POT-Creation-Date: 2016-12-04 17:16+0100
PO-Revision-Date: 2017-01-08 14:58-0500
Last-Translator: Kevin Scannell <kscanne@gmail.com>
Language-Team: Irish <gaeilge-gnulinux@lists.sourceforge.net>
Language: ga
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Bugs: Report translation errors to the Language-Team address.
Plural-Forms: nplurals=5; plural=n==1 ? 0 : n==2 ? 1 : (n>2 && n<7) ? 2 : (n>6 && n <11) ? 3 : 4;
   --byte-subst=TEAGHRÁN       ionadaíocht do bhearta dothiontaithe
   --help                      taispeáin an chabhair seo agus scoir
   --unicode-subst=TEAGHRÁN
                              ionadaíocht do charachtair dhothiontaithe Unicode
   --version                   taispeáin eolas faoin leagan agus scoir
   --widechar-subst=TEAGHRÁN
                             ionadaíocht do charachtair leathana dhothiontaithe
   -c                        ná coinnigh carachtair dhothiontaithe
   -f IONCHÓDÚ, --from-code=IONCHÓDÚ
                              ionchódú an ionchuir
   -l, --list                  taispeáin na hionchóduithe a dtacaítear leo
   -s, --silent                ná taispeáin teachtaireachtaí faoi fhadhbanna tiontaithe
   -t IONCHÓDÚ, --to-code=IONCHÓDÚ
                              ionchódú an aschuir
 %s argóint %s: Ní cheadaítear treoir fhormáidithe le méid anseo. argóint %s: Ní cheadaítear treoir fhormáidithe le beachtas athraitheach anseo. argóint %s: Ní cheadaítear treoir fhormáidithe le leithead athraitheach anseo. argóint %s: Níl carachtar '%c' bailí mar shonraitheoir tiontaithe. argóint %s: An carachtar ag deireadh na treorach formáidithe, níl sé bailí mar shonraitheoir tiontaithe. argóint %s: Úsáideann an teaghrán formáidithe níos mó ná aon argóint amháin: %u argóint. argóint %s: Úsáideann an teaghrán formáidithe níos mó ná aon argóint amháin: %u argóint. argóint %s: Úsáideann an teaghrán formáidithe níos mó ná aon argóint amháin: %u argóint. argóint %s: Úsáideann an teaghrán formáidithe níos mó ná aon argóint amháin: %u n-argóint. argóint %s: Úsáideann an teaghrán formáidithe níos mó ná aon argóint amháin: %u argóint. argóint %s: Deireadh an teaghráin i lár treorach. %s: Earráid I/A %s:%u:%u %s:%u:%u: ní féidir tiontú %s:%u:%u: carachtar nó seicheamh neamhiomlán (stdin) Tiontaigh ó ionchódú go ceann eile.
 Earráid I/A Aschur faisnéiseach:
 Ceadúnas GPLv3+: GNU GPL leagan 3 nó níos déanaí <http://gnu.org/licenses/gpl.html>
Is saorbhogearra é seo: ceadaítear duit é a athrú agus a athdháileadh.
Níl baránta AR BITH ann, an oiread atá ceadaithe de réir dlí.
 Roghanna a rialaíonn fadhbanna tiontaithe:
 Roghanna a rialaíonn aschur d'earráidí:
 Roghanna a rialaíonn formáid ionchurtha/aschurtha:
 Seol tuairiscí fabhtanna chuig <bug-gnu-libiconv@gnu.org>.
 Bain triail as '%s --help' chun tuilleadh eolais a fháil.
 Úsáid: %s [ROGHA...] [-f IONCHÓDÚ] [-t IONCHÓDÚ] [INCHOMHAD...]
 Úsáid: iconv [-c] [-s] [-f cód] [-t cód] [comhad ...] Le %s.
 ní féidir ionadaíocht bhirt a thiontú go Unicode: %s ní féidir ionadaíocht bhirt a thiontú go dtí an sprioc-ionchódú: %s ní féidir ionadaíocht bhirt a thiontú go teaghrán leathan: %s ní féidir ionadaíocht unicode a thiontú go dtí an sprioc-ionchódú: %s ní féidir ionadaíocht widechar a thiontú go dtí an sprioc-ionchódú: %s ní thacaítear le tiontú ó %s go %s ní thacaítear le tiontú ó %s ní thacaítear le tiontú go %s nó:    %s -l
 nó:    iconv -l bain triail as '%s -l' chun liosta de na hionchóduithe a dtacaítear leo a thaispeáint 