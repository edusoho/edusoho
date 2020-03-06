# zend-escaper

[![Build Status](https://secure.travis-ci.org/zendframework/zend-escaper.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-escaper)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-escaper/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-escaper?branch=master)

The OWASP Top 10 web security risks study lists Cross-Site Scripting (XSS) in
second place. PHP’s sole functionality against XSS is limited to two functions
of which one is commonly misapplied. Thus, the zend-escaper component was written.
It offers developers a way to escape output and defend from XSS and related
vulnerabilities by introducing contextual escaping based on peer-reviewed rules.

## Installation

Run the following to install this library:

```bash
$ composer require zendframework/zend-escaper
```

## Documentation

Browse the documentation online at https://docs.zendframework.com/zend-escaper/

## Support

* [Issues](https://github.com/zendframework/zend-escaper/issues/)
* [Chat](https://zendframework-slack.herokuapp.com/)
* [Forum](https://discourse.zendframework.com/)
