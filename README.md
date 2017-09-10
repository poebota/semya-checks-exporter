Semya Retail Store Checks Exporter
==================================

Command to extract checks data from Semya store

Requirements
============

PHP 7.0+

Installation
============

1. Get [composer](https://getcomposer.org/)
2. `$ git clone https://github.com/poebota/semya-checks-exporter.git`
3. `$ cd semya-checks-exporter`
4. `$ composer install`

Configuration
=============

App reads configuration from `~/.config/semya.ini` by default. Config file location can be overriden by `-c` option. Config has init format:
```
; required, bonus card holder first name
name = John

; required, bonus card id, printed on card
card_id = 100500

; required, app id, can be obtained from https://pastebin.com/yRJeUbTn
secret = abc-def-ghi

; required, 8-byte hex representation android id-like string
udid = aa11bb22cc33dd44
```

Be carefull! If no `udid` is present in config a new one can be obtained. Also Semya API has a limits on `udid`'s used at the same time.

Usage
=====

Remove all previous card registrations in mobile app before use.

Register
--------

Register new token:
```
$ bin/semya-checks-exporter register
```
```
Usage:
  register [options]

Options:
  -c, --config=CONFIG   path to config file [default: "~/.config/semya.ini"]
```

Export
------
Extract checks from last year to now to `last_year_checks.json
```
$ bin/semya-checks-exporter --fromDate '-1 year' last_year_checks.json
```
```
Usage:
  export [options] [--] <output>

Arguments:
  output                     output file

Options:
  -c, --config=CONFIG        path to config file [default: "~/.config/semya.ini"]
  -s, --startDate=STARTDATE  begin date [default: "2001-01-01"]
  -e, --endDate=ENDDATE      end date [default: "+1 day"]
```

Unregister
----------

Unregister existing token:
```
$ bin/semya-checks-exporter unregister
```
```
Usage:
  unregister [options]

Options:
  -c, --config=CONFIG   path to config file [default: "~/.config/semya.ini"]
```