# Changelog

### 0.8.0
🔻 Breaking changes ahead:

 - Dropped support for PHP < 8.0
 - Removed deprecated method `JSONPath->data()`

### 0.7.6
🔻 Breaking changes ahead:

- Removed support for PHP >= 8.0 (use version 0.8.0+ instead)
- Switched `roave/security-advisories` from `dev-master` to `dev-latest`

This is probably the last version for PHP 7.1 - 7.4.

### 0.7.5
 - Added support for $.length
 - Added trim to explode to support both 1,2,3 and 1, 2, 3 inputs
 - Dropped in_array strict equality check to be in line with the other standard equality checks such as (== and !=)

### 0.7.4
 - Removed PHPUnit from conflicting packages

### 0.7.3
 - Fixed PHP 7.4+ compatibility issues

### 0.7.2
 - Fixed query/selector "Array Slice With Start Large Negative Number And Open End On Short Array" (#7)
 - Fixed query/selector "Union With Keys" (#22)
 - Fixed query/selector "Dot Notation After Union With Keys" (#15)
 - Fixed query/selector "Union With Keys After Array Slice" (#23)
 - Fixed query/selector "Union With Keys After Bracket Notation" (#24)
 - Fixed query/selector "Union With Keys On Object Without Key" (#25)

### 0.7.1
 - Fixed issues with empty tokens (`['']` and `[""]`)
 - Fixed TypeError in AccessHelper::keyExists 
 - Improved QueryTest

### 0.7.0
🔻 Breaking changes ahead:

 - Made JSONPath::__construct final
 - Added missing type hints
 - Partially reduced complexity
 - Performed some code optimizations
 - Updated composer.json for proper PHPUnit/PHP usage
 - Added support for regular expression operator (`=~`)
 - Added QueryTest to perform tests against all queries from https://cburgmer.github.io/json-path-comparison/
 - Switched Code Style from PSR-2 to PSR-12

### 0.6.4
 - Removed unnecessary type casting, that caused problems under certain circumstances
 - Added support for `nin` operator
 - Added support for greater than or equal operator (`>=`)
 - Added support for less or equal operator (`<=`)

### 0.6.3
 - Added support for `in` operator
 - Fixed evaluation on indexed object

### 0.6.x
 - Dropped support for PHP < 7.1
 - Switched from (broken) PSR-0 to PSR-4
 - Updated PHPUnit to 8.5 / 9.4
 - Updated tests
 - Added missing PHPDoc blocks
 - Added return type hints
 - Moved from Travis to GitHub actions
 - Set `strict_types=1`

### 0.5.0
 - Fixed the slice notation (e.g. [0:2:5] etc.). **Breaks code relying on the broken implementation**

### 0.3.0
 - Added JSONPathToken class as value object
 - Lexer clean up and refactor
 - Updated the lexing and filtering of the recursive token ("..") to allow for a combination of recursion
   and filters, e.g. $..[?(@.type == 'suburb')].name

### 0.2.1 - 0.2.5
 - Various bug fixes and clean up

### 0.2.0
 - Added a heap of array access features for more creative iterating and chaining possibilities

### 0.1.x
 - Init
