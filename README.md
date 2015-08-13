Systemlib Extractor
===================

Parts of HHVM are implemented in Hack, and are embedded in the HHVM binary
as additional ELF PROGDATA sections. This library extracts them from the
binary.

There are two kinds of sections that this library extracts:

 - `systemlib`: Pure Hack code
 - `ext`: built-in extensions. These are a mix of normal Hack code, and
   functions annotated with the `<<__Native>>` user attribute, which are
   implemented in the C++ runtime

Sections with different names or aren't PROGDATA can not be examined with this
library.

Requirements
------------

This package requires the `readelf` binary; on Debian-like systems, this is in
the `elfutils` package.

Installation
------------

Assuming your project uses Composer:

    composer require hhvm/systemlib-extractor

Usage
-----

```
hphpd> require('vendor/autoload.php')
require('vendor/autoload.php')
hphpd> $extractor = new HHVM\SystemlibExtractor\SystemlibExtractor()
$extractor = new HHVM\SystemlibExtractor\SystemlibExtractor()
hphpd> =$extractor->getSectionNames()
=$extractor->getSectionNames()
HH\Vector Object
(
    [0] => "ext.6eedc03a68a6"
    [1] => "ext.a0e7b2a56511"
    [2] => "ext.7c82e855b041"
...
    [119] => "ext.a6be8a33b7c9"
    [120] => "systemlib"
)

hphpd> =$extractor->getSectionContents('systemlib')
=$extractor->getSectionContents('systemlib')
"<?hh\n// {@}generated\n\nnamespace {\n\n// default base\nclass stdClass {\n}\n\n// used in unserialize() for unknown classes\nclass __PHP_Incomplete_Class {\n  public \$__PHP_Incomplete_Class_Name;\n
There are more characters. Continue? [y/N]n
n
hphpd>
```

The constructor accepts two optional parameters:

 - a path to an HHVM binary
 - a path to READELF


License
-------

Systemlib-Extractor is BSD-licensed. We also provide an additional patent grant.

Contributing
------------

Please see CONTRIBUTING.md
