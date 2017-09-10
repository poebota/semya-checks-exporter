Semya Retail Store Checks Exporter
==================================

Command to extract checks data from Semya store

Requirements
------------

PHP 7.0+

Installation
------------
Via [composer](https://getcomposer.org/):
```
composer require poebota/semya
```

Configuration
-------------

App reads configuration from `~/.config/semya.ini` by default. Config file location can be overriden by `-c` option. Config has init format:
```
; required, bonus card holder first name
name = John

; required, bonus card id, printed on card
card_id = 100500

; required, app id, can be obtained from https://pastebin.com/yRJeUbTn
secret = abc-def-ghi

; optional, 8-byte android id-like string
udid = aa11bb22cc33dd44
```

Be carefull! If no `uuid` is present in config a new one will be generated. Semya API has a limits on `uuid`'s used at the same time.

Usage
-----

```
Usage:
  export [options] [--] <output>

Arguments:
  output                     output file

Options:
  -c, --config=CONFIG        path to config file [default: "~/.config/semya.ini"]
  -s, --startDate=STARTDATE  begin date [default: "2001-01-01"]
  -e, --endDate=ENDDATE      end date [default: "+1 day"]
  -h, --help                 Display this help message
```


Examples
-------

Extract checks from last year to now to `last_year_checks.json
```
bin/semya-checks-exporter --fromDate '-1 year' last_year_checks.json
```