# JSONPath for PHP 5.4 - 7.0 [![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE)

This is a [JSONPath](http://goessner.net/articles/JsonPath/) implementation for PHP based on Stefan Goessner's JSONPath script.

JSONPath is an XPath-like expression language for filtering, flattening and extracting data.

This project aims to be a clean and simple implementation with the following goals:

 - Object-oriented code (should be easier to manage or extend in future)
 - Expressions are parsed into tokens using code inspired by the Doctrine Lexer. The tokens are cached internally to avoid re-parsing the expressions.
 - There is no `eval()` in use
 - Any combination of objects/arrays/ArrayAccess-objects can be used as the data input which is great if you're de-serializing JSON in to objects or if you want to process your own data structures.
 
🔻 **Please note, that this branch (based on JSONPath 0.7.2) is protected. There are no intentions to make any updates here. Please consider upgrading to PHP 7.1 or newer.**

## Installation

**PHP >= 7.1+**
```bash
composer require softcreatr/jsonpath
```

**PHP < 7.1**

1. Add to the `require`-section of your composer.json: `"softcreatr/jsonpath": "dev-oldphp"`
2. Execute `composer install`

## JSONPath Examples

JSONPath                  | Result
--------------------------|-------------------------------------
`$.store.books[*].author` | the authors of all books in the store
`$..author`               | all authors
`$.store..price`          | the price of everything in the store.
`$..books[2]`             | the third book
`$..books[(@.length-1)]`  | the last book in order.
`$..books[-1:]`           | the last book in order.
`$..books[0,1]`           | the first two books
`$..books[:2]`            | the first two books
`$..books[::2]`           | every second book starting from first one
`$..books[1:6:3]`         | every third book starting from 1 till 6
`$..books[?(@.isbn)]`     | filter all books with isbn number
`$..books[?(@.price<10)]` | filter all books cheaper than 10
`$..*`                    | all elements in the data (recursively extracted)


Expression syntax
---

Symbol                | Description
----------------------|-------------------------
`$`                   | The root object/element (not strictly necessary)
`@`                   | The current object/element
`.` or `[]`           | Child operator
`..`                  | Recursive descent
`*`                   | Wildcard. All child elements regardless their index.
`[,]`                 | Array indices as a set
`[start:end:step]`    | Array slice operator borrowed from ES4/Python.
`?()`                 | Filters a result set by a script expression
`()`                  | Uses the result of a script expression as the index

## PHP Usage

```php
use Flow\JSONPath\JSONPath;

$data = ['people' => [
    ['name' => 'Sascha'],
    ['name' => 'Bianca'],
    ['name' => 'Alexander'],
    ['name' => 'Maximilian'],
]];

print_r((new JSONPath($data))->find('$.people.*.name'), true);
// $result[0] === 'Sascha'
// $result[1] === 'Bianca'
// $result[2] === 'Alexander'
// $result[3] === 'Maximilian'
```

More examples can be found in the [Wiki](https://github.com/SoftCreatR/JSONPath/wiki/Queries)

### Magic method access

The options flag `JSONPath::ALLOW_MAGIC` will instruct JSONPath when retrieving a value to first check if an object
has a magic `__get()` method and will call this method if available. This feature is *iffy* and
not very predictable as:

-  wildcard and recursive features will only look at public properties and can't smell which properties are magically accessible
-  there is no `property_exists` check for magic methods so an object with a magic `__get()` will always return `true` when checking
   if the property exists
-   any errors thrown or unpredictable behaviour caused by fetching via `__get()` is your own problem to deal with

```php
use Flow\JSONPath\JSONPath;

$myObject = json_decode('{"name":"Sascha Greuel","birthdate":"1987-12-16","city":"Gladbeck","country":"Germany"}', false);
$jsonPath = new JSONPath($myObject, JSONPath::ALLOW_MAGIC);
```

For more examples, check the JSONPathTest.php tests file.

## Script expressions

Script expressions are not supported as the original author intended because:

-   This would only be achievable through `eval` (boo).
-   Using the script engine from different languages defeats the purpose of having a single expression evaluate the same way in different
    languages which seems like a bit of a flaw if you're creating an abstract expression syntax.

So here are the types of query expressions that are supported:

	[?(@._KEY_ _OPERATOR_ _VALUE_)] // <, >, <=, >=, !=, ==, =~, in and nin
	e.g.
	[?(@.title == "A string")] //
	[?(@.title = "A string")]
	// A single equals is not an assignment but the SQL-style of '=='
	[?(@.title =~ /^a(nother)? string$/i)]
	[?(@.title in ["A string", "Another string"])]
	[?(@.title nin ["A string", "Another string"])]
	
## Known issues

- This project has not implemented multiple string indexes e.g. `$[name,year]` or `$["name","year"]`. I have no ETA on that feature, and it would require some re-writing of the parser that uses a very basic regex implementation.

## Similar projects

[FlowCommunications/JSONPath](https://github.com/FlowCommunications/JSONPath) is the predecessor of this library by Stephen Frank

Other / Similar implementations can be found in the [Wiki](https://github.com/SoftCreatR/JSONPath/wiki/Other-Implementations).

## License

[MIT](LICENSE)
