## 2.1.2 (2015-11-10)

* Mark symfony/yaml 3 as supported to be compatible with Symfony 3
* Dropped support for symfony/yaml 2.2 and older (which are long unmaintained)
* Added testing on PHP 7

## 2.1.1 (2015-06-03)

* Removed usage of a deprecated way to use the Yaml parser
* Added a more detailed exception message when the top-level key is missing

## 2.1.0 (2013-12-07)

* Move most of the logic to a ``Processor`` class which does not depend on the composer event and package. Ref #30
* Add the support of existing empty file for Capifony compatibility
* Add the support of multiple managed files
* Preserve other top-level keys than the configured one in the file
* Add a rename map used to rename parameters when updating the parameters file
* Add the possibility to use another top-level key than ``parameters``

## 2.0.0 (2013-04-06)

* BC BREAK the env map has been changed, inverting the keys and the values. Refs #14

## 1.0.0 (2013-04-06)

Initial release of the library.
