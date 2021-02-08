## Valitron: Easy Validation That Doesn't Suck

Valitron is a simple, minimal and elegant stand-alone validation library
with NO dependencies. Valitron uses simple, straightforward validation
methods with a focus on readable and concise syntax. Valitron is the
simple and pragmatic validation library you've been looking for.

[![Build
Status](https://travis-ci.org/vlucas/valitron.png?branch=master)](https://travis-ci.org/vlucas/valitron)
[![Latest Stable Version](https://poser.pugx.org/vlucas/valitron/v/stable.png)](https://packagist.org/packages/vlucas/valitron)
[![Total Downloads](https://poser.pugx.org/vlucas/valitron/downloads.png)](https://packagist.org/packages/vlucas/valitron)

[Get supported vlucas/valitron with the Tidelift Subscription](https://tidelift.com/subscription/pkg/packagist-vlucas-valitron?utm_source=packagist-vlucas-valitron&utm_medium=referral&utm_campaign=readme) 

## Why Valitron?

Valitron was created out of frustration with other validation libraries
that have dependencies on large components from other frameworks like
Symfony's HttpFoundation, pulling in a ton of extra files that aren't
really needed for basic validation. It also has purposefully simple
syntax used to run all validations in one call instead of individually
validating each value by instantiating new classes and validating values
one at a time like some other validation libraries require.

In short, Valitron is everything you've been looking for in a validation
library but haven't been able to find until now: simple pragmatic
syntax, lightweight code that makes sense, extensible for custom
callbacks and validations, well tested, and without dependencies. Let's
get started.

## Installation

Valitron uses [Composer](http://getcomposer.org) to install and update:

```
curl -s http://getcomposer.org/installer | php
php composer.phar require vlucas/valitron
```

The examples below use PHP 5.4 syntax, but Valitron works on PHP 5.3+.

## Usage

Usage is simple and straightforward. Just supply an array of data you
wish to validate, add some rules, and then call `validate()`. If there
are any errors, you can call `errors()` to get them.

```php
$v = new Valitron\Validator(array('name' => 'Chester Tester'));
$v->rule('required', 'name');
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}
```

Using this format, you can validate `$_POST` data directly and easily,
and can even apply a rule like `required` to an array of fields:

```php
$v = new Valitron\Validator($_POST);
$v->rule('required', ['name', 'email']);
$v->rule('email', 'email');
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}
```

You may use dot syntax to access members of multi-dimensional arrays,
and an asterisk to validate each member of an array:

```php
$v = new Valitron\Validator(array('settings' => array(
    array('threshold' => 50),
    array('threshold' => 90)
)));
$v->rule('max', 'settings.*.threshold', 100);
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}
```

Or use dot syntax to validate all members of a numeric array:

```php
$v = new Valitron\Validator(array('values' => array(50, 90)));
$v->rule('max', 'values.*', 100);
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}
```

You can also access nested values using dot notation:

```php
$v = new Valitron\Validator(array('user' => array('first_name' => 'Steve', 'last_name' => 'Smith', 'username' => 'Batman123')));
$v->rule('alpha', 'user.first_name')->rule('alpha', 'user.last_name')->rule('alphaNum', 'user.username');
if($v->validate()) {
    echo "Yay! We're all good!";
} else {
    // Errors
    print_r($v->errors());
}
```

Setting language and language dir globally:

```php

// boot or config file

use Valitron\Validator as V;

V::langDir(__DIR__.'/validator_lang'); // always set langDir before lang.
V::lang('ar');

```

Disabling the {field} name in the output of the error message. 

```php
use Valitron\Validator as V;

$v = new Valitron\Validator(['name' => 'John']);
$v->rule('required', ['name']);

// Disable prepending the labels
$v->setPrependLabels(false);

// Error output for the "false" condition
[
    ["name"] => [
        "is required"
    ]
]

// Error output for the default (true) condition
[
    ["name"] => [
        "name is required"
    ]
]

```

You can conditionally require values using required conditional rules. In this example, for authentication, we're requiring either a token when both the email and password are not present, or a password when the email address is present.
```php
// this rule set would work for either data set...
$data = ['email' => 'test@test.com', 'password' => 'mypassword'];
// or...
$data = ['token' => 'jashdjahs83rufh89y38h38h'];

$v = new Valitron\Validator($data);
$v->rules([
    'requiredWithout' => [
        ['token', ['email', 'password'], true]
    ],
    'requiredWith' => [
        ['password', ['email']]
    ],
    'email' => [
        ['email']
    ]
    'optional' => [
        ['email']
    ]
]);
$this->assertTrue($v->validate());
```

## Built-in Validation Rules

 * `required` - Field is required
 * `requiredWith` - Field is required if any other fields are present
 * `requiredWithout` - Field is required if any other fields are NOT present
 * `equals` - Field must match another field (email/password confirmation)
 * `different` - Field must be different than another field
 * `accepted` - Checkbox or Radio must be accepted (yes, on, 1, true)
 * `numeric` - Must be numeric
 * `integer` - Must be integer number
 * `boolean` - Must be boolean
 * `array` - Must be array
 * `length` - String must be certain length
 * `lengthBetween` - String must be between given lengths
 * `lengthMin` - String must be greater than given length
 * `lengthMax` - String must be less than given length
 * `min` - Minimum
 * `max` - Maximum
 * `listContains` - Performs in_array check on given array values (the other way round than `in`)
 * `in` - Performs in_array check on given array values
 * `notIn` - Negation of `in` rule (not in array of values)
 * `ip` - Valid IP address
 * `ipv4` - Valid IP v4 address
 * `ipv6` - Valid IP v6 address
 * `email` - Valid email address
 * `emailDNS` - Valid email address with active DNS record
 * `url` - Valid URL
 * `urlActive` - Valid URL with active DNS record
 * `alpha` - Alphabetic characters only
 * `alphaNum` - Alphabetic and numeric characters only
 * `ascii` - ASCII characters only
 * `slug` - URL slug characters (a-z, 0-9, -, \_)
 * `regex` - Field matches given regex pattern
 * `date` - Field is a valid date
 * `dateFormat` - Field is a valid date in the given format
 * `dateBefore` - Field is a valid date and is before the given date
 * `dateAfter` - Field is a valid date and is after the given date
 * `contains` - Field is a string and contains the given string
 * `subset` - Field is an array or a scalar and all elements are contained in the given array
 * `containsUnique` - Field is an array and contains unique values
 * `creditCard` - Field is a valid credit card number
 * `instanceOf` - Field contains an instance of the given class
 * `optional` - Value does not need to be included in data array. If it is however, it must pass validation.
 * `arrayHasKeys` - Field is an array and contains all specified keys.

**NOTE**: If you are comparing floating-point numbers with min/max validators, you
should install the [BCMath](http://us3.php.net/manual/en/book.bc.php)
extension for greater accuracy and reliability. The extension is not required
for Valitron to work, but Valitron will use it if available, and it is highly
recommended.

## required fields usage
the `required` rule checks if a field exists in the data array, and is not null or an empty string.
```php
$v->rule('required', 'field_name');
```

Using an extra parameter, you can make this rule more flexible, and only check if the field exists in the data array.
```php
$v->rule('required', 'field_name', true);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'spiderman', 'password' => 'Gr33nG0Blin', 'required_but_null' => null]);
$v->rules([
    'required' => [
        ['username'],
        ['password'],
        ['required_but_null', true] // boolean flag allows empty value so long as the field name is set on the data array
    ]
]);
$v->validate();
```

## requiredWith fields usage
The `requiredWith` rule checks that the field is required, not null, and not the empty string, if any other fields are present, not null, and not the empty string.
```php
// password field will be required when the username field is provided and not empty
$v->rule('requiredWith', 'password', 'username');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'spiderman', 'password' => 'Gr33nG0Blin']);
$v->rules([
    'requiredWith' => [
        ['password', 'username']
    ]
]);
$v->validate();
```

*Note* You can provide multiple values as an array. In this case if ANY of the fields are present the field will be required.
```php
// in this case the password field will be required if the username or email fields are present
$v->rule('requiredWith', 'password', ['username', 'email']);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'spiderman', 'password' => 'Gr33nG0Blin']);
$v->rules([
    'requiredWith' => [
        ['password', ['username', 'email']]
    ]
]);
$v->validate();
```

### Strict flag
The strict flag will change the `requiredWith` rule to `requiredWithAll` which will require the field only if ALL of the other fields are present, not null, and not the empty string.
```php
// in this example the suffix field is required only when both the first_name and last_name are provided
$v->rule('requiredWith', 'suffix', ['first_name', 'last_name'], true);
```
Alternate syntax.
```php
$v = new Valitron\Validator(['first_name' => 'steve', 'last_name' => 'holt', 'suffix' => 'Mr']);
$v->rules([
    'requiredWith' => [
        ['suffix', ['first_name', 'last_name'], true]
    ]
]);
$v->validate();
```

Likewise, in this case `validate()` would still return true, as the suffix field would not be required in strict mode, as not all of the fields are provided.
```php
$v = new Valitron\Validator(['first_name' => 'steve']);
$v->rules([
    'requiredWith' => [
        ['suffix', ['first_name', 'last_name'], true]
    ]
]);
$v->validate();
```

## requiredWithout fields usage
The `requiredWithout` rule checks that the field is required, not null, and not the empty string, if any other fields are NOT present.
```php
// this rule will require the username field when the first_name is not present
$v->rule('requiredWithout', 'username', 'first_name')
```

Alternate syntax.
```php
// this will return true, as the username is provided when the first_name is not provided
$v = new Valitron\Validator(['username' => 'spiderman']);
$v->rules([
    'requiredWithout' => [
        ['username', 'first_name']
    ]
]);
$v->validate();
```

*Note* You can provide multiple values as an array. In this case if ANY of the fields are NOT present the field will be required.
```php
// in this case the username field will be required if either the first_name or last_name fields are not present
$v->rule('requiredWithout', 'username', ['first_name', 'last_name']);
```

Alternate syntax.
```php
// this passes validation because although the last_name field is not present, the username is provided
$v = new Valitron\Validator(['username' => 'spiderman', 'first_name' => 'Peter']);
$v->rules([
    'requiredWithout' => [
        ['username', ['first_name', 'last_name']]
    ]
]);
$v->validate();
```

### Strict flag
The strict flag will change the `requiredWithout` rule to `requiredWithoutAll` which will require the field only if ALL of the other fields are not present.
```php
// in this example the username field is required only when both the first_name and last_name are not provided
$v->rule('requiredWithout', 'username', ['first_name', 'last_name'], true);
```
Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'BatMan']);
$v->rules([
    'requiredWithout' => [
        ['username', ['first_name', 'last_name'], true]
    ]
]);
$v->validate();
```

Likewise, in this case `validate()` would still return true, as the username field would not be required in strict mode, as all of the fields are provided.
```php
$v = new Valitron\Validator(['first_name' => 'steve', 'last_name' => 'holt']);
$v->rules([
    'requiredWithout' => [
        ['suffix', ['first_name', 'last_name'], true]
    ]
]);
$v->validate();
```

## equals fields usage
The `equals` rule checks if two fields are equals in the data array, and that the second field is not null.
```php
$v->rule('equals', 'password', 'confirmPassword');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['password' => 'youshouldnotseethis', 'confirmPassword' => 'youshouldnotseethis']);
$v->rules([
    'equals' => [
        ['password', 'confirmPassword']
    ]
]);
$v->validate();
```

## different fields usage
The `different` rule checks if two fields are not the same, or different, in the data array and that the second field is not null.
```php
$v->rule('different', 'username', 'password');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'spiderman', 'password' => 'Gr33nG0Blin']);
$v->rules([
    'different' => [
        ['username', 'password']
    ]
]);
$v->validate();
```

## accepted fields usage
The `accepted` rule checks if the field is either 'yes', 'on', 1, or true.
```php
$v->rule('accepted', 'remember_me');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['remember_me' => true]);
$v->rules([
    'accepted' => [
        ['remember_me']
    ]
]);
$v->validate();
```

## numeric fields usage
The `numeric` rule checks if the field is number. This is analogous to php's is_numeric() function.
```php
$v->rule('numeric', 'amount');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['amount' => 3.14]);
$v->rules([
    'numeric' => [
        ['amount']
    ]
]);
$v->validate();
```

## integer fields usage
The `integer` rule checks if the field is an integer number.
```php
$v->rule('integer', 'age');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['age' => '27']);
$v->rules([
    'integer' => [
        ['age']
    ]
]);
$v->validate();
```

*Note* the optional boolean flag for strict mode makes sure integers are to be supplied in a strictly numeric form. So the following rule would evaluate to true:
```php
$v = new Valitron\Validator(['negative' => '-27', 'positive'=>'27']);
$v->rule('integer', 'age', true);
$v->rule('integer', 'height', true);
$v->validate();
```

Whereas the following will evaluate to false, as the + for the positive number in this case is redundant:
```php
$v = new Valitron\Validator(['negative' => '-27', 'positive'=>'+27']);
$v->rule('integer', 'age', true);
$v->rule('integer', 'height', true);
$v->validate();
```

## boolean fields usage
The `boolean` rule checks if the field is a boolean. This is analogous to php's is_bool() function.
```php
$v->rule('boolean', 'remember_me');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['remember_me' => true]);
$v->rules([
    'boolean' => [
        ['remember_me']
    ]
]);
$v->validate();
```

## array fields usage
The `array` rule checks if the field is an array. This is analogous to php's is_array() function.
```php
$v->rule('array', 'user_notifications');
```

Alternate Syntax.
```php
$v = new Valitron\Validator(['user_notifications' => ['bulletin_notifications' => true, 'marketing_notifications' => false, 'message_notification' => true]]);
$v->rules([
    'array' => [
        ['user_notifications']
    ]
]);
$v->validate();
```

## length fields usage
The `length` rule checks if the field is exactly a given length and that the field is a valid string.
```php
$v->rule('length', 'username', 10);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'bobburgers']);
$v->rules([
    'length' => [
        ['username', 10]
    ]
]);
$v->validate();
```

## lengthBetween fields usage
The `lengthBetween` rule checks if the field is between a given length tange and that the field is a valid string.
```php
$v->rule('lengthBetween', 'username', 1, 10);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'bobburgers']);
$v->rules([
    'lengthBetween' => [
        ['username', 1, 10]
    ]
]);
$v->validate();
```

## lengthMin fields usage
The `lengthMin` rule checks if the field is at least a given length and that the field is a valid string.
```php
$v->rule('lengthMin', 'username', 5);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'martha']);
$v->rules([
    'lengthMin' => [
        ['username', 5]
    ]
]);
$v->validate();
```

## lengthMax fields usage
The `lengthMax` rule checks if the field is at most a given length and that the field is a valid string.
```php
$v->rule('lengthMax', 'username', 10);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'bruins91']);
$v->rules([
    'lengthMax' => [
        ['username', 10]
    ]
]);
$v->validate();
```

## min fields usage
The `min` rule checks if the field is at least a given value and that the provided value is numeric.
```php
$v->rule('min', 'age', 18);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['age' => 28]);
$v->rules([
    'min' => [
        ['age', 18]
    ]
]);
$v->validate();
```

## max fields usage
The `max` rule checks if the field is at most a given value and that the provided value is numeric.
```php
$v->rule('max', 'age', 12);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['age' => 10]);
$v->rules([
    'max' => [
        ['age', 12]
    ]
]);
$v->validate();
```

## listContains fields usage
The `listContains` rule checks that the field is present in a given array of values.
```php
$v->rule('listContains', 'color', 'yellow');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['color' => ['blue', 'green', 'red', 'yellow']]);
$v->rules([
    'listContains' => [
        ['color', 'yellow']
    ]
]);
$v->validate();
```

## in fields usage
The `in` rule checks that the field is present in a given array of values.
```php
$v->rule('in', 'color', ['blue', 'green', 'red', 'purple']);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['color' => 'purple']);
$v->rules([
    'in' => [
        ['color', ['blue', 'green', 'red', 'purple']]
    ]
]);
$v->validate();
```

## notIn fields usage
The `notIn` rule checks that the field is NOT present in a given array of values.
```php
$v->rule('notIn', 'color', ['blue', 'green', 'red', 'yellow']);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['color' => 'purple']);
$v->rules([
    'notIn' => [
        ['color', ['blue', 'green', 'red', 'yellow']]
    ]
]);
$v->validate();
```

## ip fields usage
The `ip` rule checks that the field is a valid ip address. This includes IPv4, IPv6, private, and reserved ranges.
```php
$v->rule('ip', 'user_ip');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['user_ip' => '127.0.0.1']);
$v->rules([
    'ip' => [
        ['user_ip']
    ]
]);
$v->validate();
```

## ipv4 fields usage
The `ipv4` rule checks that the field is a valid IPv4 address.
```php
$v->rule('ipv4', 'user_ip');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['user_ip' => '127.0.0.1']);
$v->rules([
    'ipv4' => [
        ['user_ip']
    ]
]);
$v->validate();
```

## ipv6 fields usage
The `ipv6` rule checks that the field is a valid IPv6 address.
```php
$v->rule('ipv6', 'user_ip');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['user_ip' => '0:0:0:0:0:0:0:1']);
$v->rules([
    'ipv6' => [
        ['user_ip']
    ]
]);
$v->validate();
```

## email fields usage
The `email` rule checks that the field is a valid email address.
```php
$v->rule('email', 'user_email');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['user_email' => 'someone@example.com']);
$v->rules([
    'email' => [
        ['user_email']
    ]
]);
$v->validate();
```

## emailDNS fields usage
The `emailDNS` rule validates the field is a valid email address with an active DNS record or any type.
```php
$v->rule('emailDNS', 'user_email');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['user_email' => 'some_fake_email_address@gmail.com']);
$v->rules([
    'emailDNS' => [
        ['user_email']
    ]
]);
$v->validate();
```

## url fields usage
The `url` rule checks the field is a valid url.
```php
$v->rule('url', 'website');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['website' => 'https://example.com/contact']);
$v->rules([
    'url' => [
        ['website']
    ]
]);
$v->validate();
```

## urlActive fields usage
The `urlActive` rule checks the field is a valid url with an active A, AAAA, or CNAME record.
```php
$v->rule('urlActive', 'website');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['website' => 'https://example.com/contact']);
$v->rules([
    'urlActive' => [
        ['website']
    ]
]);
$v->validate();
```

## alpha fields usage
The `alpha` rule checks the field is alphabetic characters only.
```php
$v->rule('alpha', 'username');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'batman']);
$v->rules([
    'alpha' => [
        ['username']
    ]
]);
$v->validate();
```

## alphaNum fields usage
The `alphaNum` rule checks the field contains only alphabetic or numeric characters.
```php
$v->rule('alphaNum', 'username');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'batman123']);
$v->rules([
    'alphaNum' => [
        ['username']
    ]
]);
$v->validate();
```

## ascii fields usage
The `ascii` rule checks the field contains only characters in the ascii character set.
```php
$v->rule('ascii', 'username');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'batman123']);
$v->rules([
    'ascii' => [
        ['username']
    ]
]);
$v->validate();
```

## slug fields usage
The `slug` rule checks that the field only contains URL slug characters (a-z, 0-9, -, _).
```php
$v->rule('slug', 'username');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'L337-H4ckZ0rz_123']);
$v->rules([
    'slug' => [
        ['username']
    ]
]);
$v->validate();
```

## regex fields usage
The `regex` rule ensures the field matches a given regex pattern.
(This regex checks the string is alpha numeric between 5-10 characters).
```php
$v->rule('regex', 'username', '/^[a-zA-Z0-9]{5,10}$/');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'Batman123']);
$v->rules([
    'regex' => [
        ['username', '/^[a-zA-Z0-9]{5,10}$/']
    ]
]);
$v->validate();
```

## date fields usage
The `date` rule checks if the supplied field is a valid \DateTime object or if the string can be converted to a unix timestamp via strtotime().
```php
$v->rule('date', 'created_at');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['created_at' => '2018-10-13']);
$v->rules([
    'date' => [
        ['created_at']
    ]
]);
$v->validate();
```

## dateFormat fields usage
The `dateFormat` rule checks that the supplied field is a valid date in a specified date format.
```php
$v->rule('dateFormat', 'created_at', 'Y-m-d');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['created_at' => '2018-10-13']);
$v->rules([
    'dateFormat' => [
        ['created_at', 'Y-m-d']
    ]
]);
$v->validate();
```

## dateBefore fields usage
The `dateBefore` rule checks that the supplied field is a valid date before a specified date.
```php
$v->rule('dateBefore', 'created_at', '2018-10-13');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['created_at' => '2018-09-01']);
$v->rules([
    'dateBefore' => [
        ['created_at', '2018-10-13']
    ]
]);
$v->validate();
```

## dateAfter fields usage
The `dateAfter` rule checks that the supplied field is a valid date after a specified date.
```php
$v->rule('dateAfter', 'created_at', '2018-10-13');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['created_at' => '2018-09-01']);
$v->rules([
    'dateAfter' => [
        ['created_at', '2018-01-01']
    ]
]);
$v->validate();
```

## contains fields usage
The `contains` rule checks that a given string exists within the field and checks that the field and the search value are both valid strings.
```php
$v->rule('contains', 'username', 'man');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['username' => 'Batman123']);
$v->rules([
    'contains' => [
        ['username', 'man']
    ]
]);
$v->validate();
```

*Note* You can use the optional strict flag to ensure a case-sensitive match.
The following example will return true:
```php
$v = new Valitron\Validator(['username' => 'Batman123']);
$v->rules([
    'contains' => [
        ['username', 'man']
    ]
]);
$v->validate();
```
Whereas, this would return false, as the M in the search string is not uppercase in the provided value:
```php
$v = new Valitron\Validator(['username' => 'Batman123']);
$v->rules([
    'contains' => [
        ['username', 'Man', true]
    ]
]);
$v->validate();
```

## subset fields usage
The `subset` rule checks that the field is either a scalar or array field and that all of it's values are contained within a given set of values.
```php
$v->rule('subset', 'colors', ['green', 'blue', 'orange']);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['colors' => ['green', 'blue']]);
$v->rules([
    'subset' => [
        ['colors', ['orange', 'green', 'blue', 'red']]
    ]
]);
$v->validate();
```
This example would return false, as the provided color, purple, does not exist in the array of accepted values we're providing.
```php
$v = new Valitron\Validator(['colors' => ['purple', 'blue']]);
$v->rules([
    'subset' => [
        ['colors', ['orange', 'green', 'blue', 'red']]
    ]
]);
$v->validate();
```

## containsUnique fields usage
The `containsUnique` rule checks that the provided field is an array and that all values contained within are unique, i.e. no duplicate values in the array.
```php
$v->rule('containsUnique', 'colors');
```

Alternate syntax.
```php
$v = new Valitron\Validator(['colors' => ['purple', 'blue']]);
$v->rules([
    'containsUnique' => [
        ['colors']
    ]
]);
$v->validate();
```
This example would return false, as the values in the provided array are duplicates.
```php
$v = new Valitron\Validator(['colors' => ['purple', 'purple']]);
$v->rules([
    'containsUnique' => [
        ['colors']
    ]
]);
$v->validate();
```

## Credit Card Validation usage

Credit card validation currently allows you to validate a Visa `visa`,
Mastercard `mastercard`, Dinersclub `dinersclub`, American Express `amex`
or Discover `discover`

This will check the credit card against each card type

```php
$v->rule('creditCard', 'credit_card');
```

To optionally filter card types, add the slug to an array as the next parameter:

```php
$v->rule('creditCard', 'credit_card', ['visa', 'mastercard']);
```

If you only want to validate one type of card, put it as a string:

```php
$v->rule('creditCard', 'credit_card', 'visa');
```

If the card type information is coming from the client, you might also want to
still specify an array of valid card types:

```php
$cardType = 'amex';
$v->rule('creditCard', 'credit_card', $cardType, ['visa', 'mastercard']);
$v->validate(); // false
```

## instanceOf fields usage
The `instanceOf` rule checks that the field is an instance of a given class.
```php
$v->rule('instanceOf', 'date', \DateTime);
```

Alternate syntax.
```php
$v = new Valitron\Validator(['date' => new \DateTime()]);
$v->rules([
    'instanceOf' => [
        ['date', 'DateTime']
    ]
]);
$v->validate();
```
*Note* You can also compare the value against a given object as opposed to the string class name.
This example would also return true:
```php
$v = new Valitron\Validator(['date' => new \DateTime()]);
$existingDateObject = new \DateTime();
$v->rules([
    'instanceOf' => [
        ['date', $existingDateObject]
    ]
]);
$v->validate();
```

## optional fields usage
The `optional` rule ensures that if the field is present in the data set that it passes all validation rules.
```php
$v->rule('optional', 'username');
```

Alternate syntax.
This example would return true either when the 'username' field is not present or in the case where the username is only alphabetic characters.
```php
$v = new Valitron\Validator(['username' => 'batman']);
$v->rules([
    'alpha' => [
        ['username']
    ],
    'optional' => [
        ['username']
    ]
]);
$v->validate();
```
This example would return false, as although the field is optional, since it is provided it must pass all the validation rules, which in this case it does not.
```php
$v = new Valitron\Validator(['username' => 'batman123']);
$v->rules([
    'alpha' => [
        ['username']
    ],
    'optional' => [
        ['username']
    ]
]);
$v->validate();
```

## arrayHasKeys fields usage

The `arrayHasKeys` rule ensures that the field is an array and that it contains all the specified keys.
Returns false if the field is not an array or if no required keys are specified or if some key is missing.

```php
$v = new Valitron\Validator([
    'address' => [
        'name' => 'Jane Doe',
        'street' => 'Doe Square',
        'city' => 'Doe D.C.'
    ]
]);
$v->rule('arrayHasKeys', 'address', ['name', 'street', 'city']);
$v->validate();
```

## Adding Custom Validation Rules

To add your own validation rule, use the `addRule` method with a rule
name, a custom callback or closure, and a error message to display in
case of an error. The callback provided should return boolean true or
false.

```php
Valitron\Validator::addRule('alwaysFail', function($field, $value, array $params, array $fields) {
    return false;
}, 'Everything you do is wrong. You fail.');
```

You can also use one-off rules that are only valid for the specified
fields.

```php
$v = new Valitron\Validator(array("foo" => "bar"));
$v->rule(function($field, $value, $params, $fields) {
    return true;
}, "foo")->message("{field} failed...");
```

This is useful because such rules can have access to variables
defined in the scope where the `Validator` lives. The Closure's
signature is identical to `Validator::addRule` callback's
signature.

If you wish to add your own rules that are not static (i.e.,
your rule is not static and available to call `Validator`
instances), you need to use `Validator::addInstanceRule`.
This rule will take the same parameters as
`Validator::addRule` but it has to be called on a `Validator`
instance.

## Chaining rules

You can chain multiple rules together using the following syntax.
```php
$v = new Valitron\Validator(['email_address' => 'test@test.com']);
$v->rule('required', 'email_address')->rule('email', 'email_address');
$v->validate();
```

## Alternate syntax for adding rules

As the number of rules grows, you may prefer the alternate syntax
for defining multiple rules at once.

```php
$rules = [
    'required' => 'foo',
    'accepted' => 'bar',
    'integer' =>  'bar'
];

$v = new Valitron\Validator(array('foo' => 'bar', 'bar' => 1));
$v->rules($rules);
$v->validate();
```

If your rule requires multiple parameters or a single parameter
more complex than a string, you need to wrap the rule in an array.

```php
$rules = [
    'required' => [
        ['foo'],
        ['bar']
    ],
    'length' => [
        ['foo', 3]
    ]
];
```
You can also specify multiple rules for each rule type.

```php
$rules = [
    'length'   => [
        ['foo', 5],
        ['bar', 5]
    ]
];
```

Putting these techniques together, you can create a complete
rule definition in a relatively compact data structure.

You can continue to add individual rules with the `rule` method
even after specifying a rule definition via an array. This is
especially useful if you are defining custom validation rules.

```php
$rules = [
    'required' => 'foo',
    'accepted' => 'bar',
    'integer' =>  'bar'
];

$v = new Valitron\Validator(array('foo' => 'bar', 'bar' => 1));
$v->rules($rules);
$v->rule('min', 'bar', 0);
$v->validate();
```

You can also add rules on a per-field basis:
```php
$rules = [
    'required',
    ['lengthMin', 4]
];

$v = new Valitron\Validator(array('foo' => 'bar'));
$v->mapFieldRules('foo', $rules);
$v->validate();
```

Or for multiple fields at once:

```php
$rules = [
    'foo' => ['required', 'integer'],
    'bar'=>['email', ['lengthMin', 4]]
];

$v = new Valitron\Validator(array('foo' => 'bar', 'bar' => 'mail@example.com));
$v->mapFieldsRules($rules);
$v->validate();
```

## Adding field label to messages

You can do this in two different ways, you can add a individual label to a rule or an array of all labels for the rules.

To add individual label to rule you simply add the `label` method after the rule.

```php
$v = new Valitron\Validator(array());
$v->rule('required', 'name')->message('{field} is required')->label('Name');
$v->validate();
```

There is a edge case to this method, you wouldn't be able to use a array of field names in the rule definition, so one rule per field. So this wouldn't work:

```php
$v = new Valitron\Validator(array());
$v->rule('required', array('name', 'email'))->message('{field} is required')->label('Name');
$v->validate();
```

However we can use a array of labels to solve this issue by simply adding the `labels` method instead:

```php
$v = new Valitron\Validator(array());
$v->rule('required', array('name', 'email'))->message('{field} is required');
$v->labels(array(
    'name' => 'Name',
    'email' => 'Email address'
));
$v->validate();
```

This introduces a new set of tags to your error language file which looks like `{field}`, if you are using a rule like `equals` you can access the second value in the language file by incrementing the field with a value like `{field1}`.


## Re-use of validation rules

You can re-use your validation rules to quickly validate different data with the same rules by using the withData method:

```php
$v = new Valitron\Validator(array());
$v->rule('required', 'name')->message('{field} is required');
$v->validate(); //false

$v2 = $v->withData(array('name'=>'example'));
$v2->validate(); //true
```

## Running Tests

The test suite depends on the Composer autoloader to load and run the
Valitron files. Please ensure you have downloaded and installed Composer
before running the tests:

1. Download Composer `curl -s http://getcomposer.org/installer | php`
2. Run 'install' `php composer.phar install`
3. Run the tests `phpunit`

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Make your changes
4. Run the tests, adding new ones for your own code if necessary (`phpunit`)
5. Commit your changes (`git commit -am 'Added some feature'`)
6. Push to the branch (`git push origin my-new-feature`)
7. Create new Pull Request
8. Pat yourself on the back for being so awesome

## Security Disclosures and Contact Information

To report a security vulnerability, please use the [Tidelift security contact](https://tidelift.com/security). Tidelift will coordinate the fix and disclosure.
