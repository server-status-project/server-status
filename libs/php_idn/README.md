PHP IDN Punycode
================

Encode and decode IDN Punycode if not exists internal php functions `idn_to_ascii` and `idn_to_utf8`.
Functions use algorithm by rfc 3492.

[![Build Status](https://travis-ci.org/IgorVBelousov/php_idn.svg?branch=master)](https://travis-ci.org/IgorVBelousov/php_idn)

**function EncodePunycodeIDN( $value )** string 

Encode UTF-8 domain name to IDN Punycode

Parameters: 

string **$value** Domain name

Returns: 

Encoded Domain name

**function DecodePunycodeIDN( $value )** string 

Decode IDN Punycode to UTF-8 domain name

Parameters: 

string **$value** Punycode

Returns: 

Domain name in UTF-8 charset