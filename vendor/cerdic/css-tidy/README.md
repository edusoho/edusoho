# CSSTidy [![Build Status](https://travis-ci.org/Cerdic/CSSTidy.svg?branch=master)](https://travis-ci.org/Cerdic/CSSTidy)

CSSTidy is a CSS minifier 

* css_optimiser.php is the web-interface
* class.csstidy.php is the parser
* bin/pcsstidy is the standalone command line executable

This class represents a CSS parser which reads CSS code and saves it in an array.
In opposite to most other CSS parsers, it does not use regular expressions and
thus has full CSS3 support and a higher reliability. The downside of not using regular expressions
is a lower speed though.
Additional to that it applies some optimisations and fixes to the CSS code.


## Usage

```
include('class.csstidy.php');
$csstidy = new csstidy();

// Set some options :
$csstidy->set_cfg('optimise_shorthands', 2);
$csstidy->set_cfg('template', 'high');

// Parse the CSS
$csstidy->parse($css_code);

// Get back the optimized CSS Code
$css_code_opt = $csstidy->print->plain();
```


## Changelog
* v1.7.1 :
  - fix deprecated with PHP 7.4
* v1.7.0 :
  - provide bin/pcsstidy for command line usage
  - support nested @media and @supports rules
* v1.6.5 :
  - fix warnings with PHP 7.3
* v1.6.4 :
  - preserve important comments (starting with !) in the minification /*! Credits/Licence */
* v1.6.3 :
  - border-radius shorthands optimisation, reverse_left_and_right option
* v1.5.7 :
  - PHP 7 compatibility, composer update, Travis CI integration
* v1.5.6 :
  - fixes minor bugs, mainly on CSS3 properties/units
* v1.5.2 :
  - is PHP 5.4+ compliant, removes use of GLOBALS, fixes some bugs, integrates CSS3 units
  - and now available on https://packagist.org/packages/cerdic/css-tidy
* v1.4 :<br/>
Is the new version coming from master branch (corresponds to the initial trunk of svn repository) after beeing stabilized
* v1.3 branch corresponds to the last stable relase published by the author.<br/>
It integrates some bugfixes and a 1.3.1 version has been taged
Since the original project (http://csstidy.sourceforge.net/index.php) has been suspended
here is the import of https://csstidy.svn.sourceforge.net/svnroot/csstidy on 2010-11-14

Only PHP version is here maintained

## Licence

	Copyright 2005-2007 Florian Schmitz
	Copyright 2010-2019 Cedric Morin

	CSSTidy is free software; you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation; either version 2.1 of the License, or
	(at your option) any later version.
  
	CSSTidy is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


## History

Original Tracker : 
http://sourceforge.net/tracker/?group_id=148404&atid=771415
