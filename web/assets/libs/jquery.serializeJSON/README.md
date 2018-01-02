jquery.serializeJSON
====================

Adds the method `.serializeJSON()` to [jQuery](http://jquery.com/) (or [Zepto](http://zeptojs.com/)) that serializes a form into a JavaScript Object, using the same format as the default Ruby on Rails request params.

Install
-------

Install with [bower](http://bower.io/) `bower install jquery.serializeJSON`, or [npm](https://www.npmjs.com/) `npm install jquery-serializejson`, or just download the [jquery.serializejson.js](https://raw.githubusercontent.com/marioizquierdo/jquery.serializeJSON/master/jquery.serializejson.js) script.

And make sure it is included after jQuery (or Zepto), for example:
```html
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="jquery.serializejson.js"></script>
```

Usage Example
-------------

HTML form (input, textarea and select tags supported):

```html

<form id="my-profile">
  <!-- simple attribute -->
  <input type="text" name="fullName"              value="Mario Izquierdo" />

  <!-- nested attributes -->
  <input type="text" name="address[city]"         value="San Francisco" />
  <input type="text" name="address[state][name]"  value="California" />
  <input type="text" name="address[state][abbr]"  value="CA" />

  <!-- array -->
  <input type="text" name="jobbies[]"             value="code" />
  <input type="text" name="jobbies[]"             value="climbing" />

  <!-- textareas, checkboxes ... -->
  <textarea              name="projects[0][name]">serializeJSON</textarea>
  <textarea              name="projects[0][language]">javascript</textarea>
  <input type="hidden"   name="projects[0][popular]" value="0" />
  <input type="checkbox" name="projects[0][popular]" value="1" checked />

  <textarea              name="projects[1][name]">tinytest.js</textarea>
  <textarea              name="projects[1][language]">javascript</textarea>
  <input type="hidden"   name="projects[1][popular]" value="0" />
  <input type="checkbox" name="projects[1][popular]" value="1"/>

  <!-- select -->
  <select name="selectOne">
    <option value="paper">Paper</option>
    <option value="rock" selected>Rock</option>
    <option value="scissors">Scissors</option>
  </select>

  <!-- select multiple options, just name it as an array[] -->
  <select multiple name="selectMultiple[]">
    <option value="red"  selected>Red</option>
    <option value="blue" selected>Blue</option>
    <option value="yellow">Yellow</option>
	</select>
</form>

```

JavaScript:

```javascript
$('#my-profile').serializeJSON();

// returns =>
{
  fullName: "Mario Izquierdo",

  address: {
    city: "San Francisco",
    state: {
      name: "California",
      abbr: "CA"
    }
  },

  jobbies: ["code", "climbing"],

  projects: {
    '0': { name: "serializeJSON", language: "javascript", popular: "1" },
    '1': { name: "tinytest.js",   language: "javascript", popular: "0" }
  },

  selectOne: "rock",
  selectMultiple: ["red", "blue"]
}
```

The `serializeJSON` function returns a JavaScript object, not a JSON String. It should probably have been called `serializeObject`, or something like that, but those names were already taken.

To serialize into JSON, use the `JSON.stringify` method, that is available on all major [new browsers](http://caniuse.com/json).
To support old browsers, just include the [json2.js](https://github.com/douglascrockford/JSON-js) polyfill (as described on [stackoverfow](http://stackoverflow.com/questions/191881/serializing-to-json-in-jquery)).

```javascript
  var jsonString = JSON.stringify(obj);
```

Note that `.serializeJSON()` implememtation relies on jQuery's [.serializeArray()](https://api.jquery.com/serializeArray/) to grab the form attributes and then create the object using the names.
That means, it will serialize the inputs that are supported by `.serializeArray()`, that uses the standard W3C rules for [successful controls](http://www.w3.org/TR/html401/interact/forms.html#h-17.13.2) to determine which elements it should include. In particular, the included elements cannot be disabled and must contain a name attribute. No submit button value is serialized since the form was not submitted using a button. And data from file select elements is not serialized.



Parse values with :types
------------------------

Parsed values are **strings** by default. But you can force values to be parsed with specific types by appending the type with a colon.

```html
<form>
  <input type="text" name="deftype"          value=":string is the default (implicit) type"/>
  <input type="text" name="text:string"      value=":string type can still be used to overrid other parsing options"/>
  <input type="text" name="excluded:skip"    value="Use :skip to not include this field in the result"/>

  <input type="text" name="numbers[1]:number"           value="1"/>
  <input type="text" name="numbers[1.1]:number"         value="1.1"/>
  <input type="text" name="numbers[other stuff]:number" value="other stuff"/>

  <input type="text" name="bools[true]:boolean"      value="true"/>
  <input type="text" name="bools[false]:boolean"     value="false"/>
  <input type="text" name="bools[0]:boolean"         value="0"/>

  <input type="text" name="nulls[null]:null"            value="null"/>
  <input type="text" name="nulls[other stuff]:null"     value="other stuff"/>

  <input type="text" name="autos[string]:auto"          value="text with stuff"/>
  <input type="text" name="autos[0]:auto"               value="0"/>
  <input type="text" name="autos[1]:auto"               value="1"/>
  <input type="text" name="autos[true]:auto"            value="true"/>
  <input type="text" name="autos[false]:auto"           value="false"/>
  <input type="text" name="autos[null]:auto"            value="null"/>
  <input type="text" name="autos[list]:auto"            value="[1, 2, 3]"/>

  <input type="text" name="arrays[empty]:array"         value="[]"/>
  <input type="text" name="arrays[list]:array"          value="[1, 2, 3]"/>

  <input type="text" name="objects[empty]:object"       value="{}"/>
  <input type="text" name="objects[dict]:object"        value='{"my": "stuff"}'/>
</form>
```

```javascript
$('form').serializeJSON();

// returns =>
{
  "deftype": "default type is :string",
  "text": ":string type overrides parsing options",
  // excluded:skip is not included in the output
  "numbers": {
    "1": 1,
    "1.1": 1.1,
    "other stuff": NaN, // <-- Other stuff parses as NaN (Not a Number)
  },
  "bools": {
    "true": true,
    "false": false,
    "0": false, // <-- "false", "null", "undefined", "", "0" parse as false
  },
  "nulls": {
    "null": null, // <-- "false", "null", "undefined", "", "0" parse as null
    "other stuff": "other stuff"
  },
  "autos": { // works as the parseAll option
    "string": "text with stuff",
    "0": 0,         // <-- parsed as number
    "1": 1,         // <-- parsed as number
    "true": true,   // <-- parsed as boolean
    "false": false, // <-- parsed as boolean
    "null": null,   // <-- parsed as null
    "list": "[1, 2, 3]" // <-- array and object types are not auto-parsed
  },
  "arrays": { // <-- works using JSON.parse
    "empty": [],
    "not empty": [1,2,3]
  },
  "objects": { // <-- works using JSON.parse
    "empty": {},
    "not empty": {"my": "stuff"}
  }
}
```

Types can also be specified with the `data-value-type` attribute, instead of the :type notation:

```html
<form>
  <input type="text" name="anumb"   data-value-type="number"  value="1"/>
  <input type="text" name="abool"   data-value-type="boolean" value="true"/>
  <input type="text" name="anull"   data-value-type="null"    value="null"/>
  <input type="text" name="anauto"  data-value-type="auto"    value="0"/>
</form>
```


Options
-------

By default `.serializeJSON()`, with no options works like this:

  * Values are always **strings** (unless appending :types to the input names)
  * Unchecked checkboxes are ignored (as defined in the W3C rules for [successful controls](http://www.w3.org/TR/html401/interact/forms.html#h-17.13.2)).
  * Disabled elements are ignored (W3C rules)
  * Keys (input names) are always **strings** (nested params are objects by default)

This is because `serializeJSON` is designed to return exactly the same as a regular HTML form submission when serialized as Rack/Rails params, which ensures maximun compatibility and stability.

For convenience, you can change the default behavior with:

  * **checkboxUncheckedValue: string**, Use this value for unchecked checkboxes, instead of ignoring them. Make sure to use a String. If the value needs to be parsed (i.e. to a Boolean) use a parse option (i.e. `parseBooleans: true`).
  * **parseBooleans: true**, automatically detect and convert strings `"true"` and `"false"` to booleans `true` and `false`.
  * **parseNumbers: true**, automatically detect and convert strings like `"1"`, `"33.33"`, `"-44"` to numbers like `1`, `33.33`, `-44`.
  * **parseNulls: true**, automatically detect and convert the string `"null"` to the null value `null`.
  * **parseAll: true**, all of the above. This is the same as if the default :type was `:auto` instead of `:string`.
  * **parseWithFunction: function**, define your own parse function(inputValue, inputName) { return parsedValue }
  * **skipFalsyValuesForFields: []**, skip fields with given names and falsy values. You can use `data-skip-falsy="true"` input attribute as well. Falsy values are determined after converting to a given type, note that `"0"` as :string is truthy, but `0` as :number is falsy.
  * **skipFalsyValuesForTypes: []**, skip fields with given types and falsy values (i.e. `skipFalsyValuesForTypes: ["string", "number"]` would skip `""` for `:string` fields, and `0` for `:number` fields).
  * **customTypes: {}**, define your own :types or override the default types. Defined as an object like `{ type: function(value){...} }`
  * **defaultTypes: {defaultTypes}**, in case you want to re-define all the :types. Defined as an object like `{ type: function(value){...} }`
  * **useIntKeysAsArrayIndex: true**, when using integers as keys (i.e. `<input name="foods[0]" value="banana">`), serialize as an array (`{"foods": ["banana"]}`) instead of an object (`{"foods": {"0": "banana"}`).

More info about options usage in the sections below.


## Include unchecked checkboxes ##

In my opinion, the most confusing detail when serializing a form is the input type checkbox, that will include the value if checked, and nothing if unchecked.

To deal with this, it is a common practice to use hidden fields for the "unchecked" values:

```html
<!-- Only one booleanAttr will be serialized, being "true" or "false" depending if the checkbox is selected or not -->
<input type="hidden"   name="booleanAttr" value="false" />
<input type="checkbox" name="booleanAttr" value="true" />
```

This solution is somehow verbose, but it is unobtrusive and ensures progressive enhancement, because it is the standard HTML behavior (also works without JavaScript).

But, to make things easier, `serializeJSON` includes the option `checkboxUncheckedValue` and the possibility to add the attribute `data-unchecked-value` to the checkboxes.

For example:

```html
<form>
  <input type="checkbox" name="check1" value="true" checked/>
  <input type="checkbox" name="check2" value="true"/>
  <input type="checkbox" name="check3" value="true"/>
</form>
```

Serializes like this by default:

```javascript
$('form').serializeJSON();

// returns =>
{'check1': 'true'} // Note that check2 and check3 are not included because they are not checked
```

Which ignores any unchecked checkboxes.
To include all checkboxes, use the `checkboxUncheckedValue` option like this:

```javascript
$('form').serializeJSON({checkboxUncheckedValue: "false"});

// returns =>
{'check1': 'true', check2: 'false', check3: 'false'}
```

The "unchecked" value can also be specified via the HTML attribute `data-unchecked-value` (Note this attribute is only recognized by the plugin):

```html
<form id="checkboxes">
  <input type="checkbox" name="checked[bool]"  value="true" data-unchecked-value="false" checked/>
  <input type="checkbox" name="checked[bin]"   value="1"    data-unchecked-value="0"     checked/>
  <input type="checkbox" name="checked[cool]"  value="YUP"                               checked/>

  <input type="checkbox" name="unchecked[bool]"  value="true" data-unchecked-value="false" />
  <input type="checkbox" name="unchecked[bin]"   value="1"    data-unchecked-value="0" />
  <input type="checkbox" name="unchecked[cool]"  value="YUP" /> <!-- No unchecked value specified -->
</form>
```

Serializes like this by default:

```javascript
$('form#checkboxes').serializeJSON(); // Note no option is used

// returns =>
{
  'checked': {
    'bool':  'true',
    'bin':   '1',
    'cool':  'YUP'
  },
  'unchecked': {
    'bool': 'false',
    'bin':  '0'
    // Note that unchecked cool does not appear, because it doesn't use data-unchecked-value
  }
}
```

You can use both the option `checkboxUncheckedValue` and the attribute `data-unchecked-value` at the same time, in which case the attribute has precedence over the option.
And remember that you can combine it with other options to parse values as well.

```javascript
$('form#checkboxes').serializeJSON({checkboxUncheckedValue: 'NOPE', parseBooleans: true, parseNumbers: true});

// returns =>
{
  'checked': {
    'bool':  true,
    'bin':   1,
    'cool':  'YUP'
  },
  'unchecked': {
    'bool': false, // value from data-unchecked-value attribute, and parsed with parseBooleans
    'bin':  0,     // value from data-unchecked-value attribute, and parsed with parseNumbers
    'cool': 'NOPE' // value from checkboxUncheckedValue option
  }
}
```


## Automatically Detect Types With Parse Options ##

The default type is :string, so all values are Strings by default, even if they look like booleans, numbers or nulls. For example:

```html
<form>
  <input type="text" name="bool[true]"    value="true"/>
  <input type="text" name="bool[false]"   value="false"/>
  <input type="text" name="number[0]"     value="0"/>
  <input type="text" name="number[1]"     value="1"/>
  <input type="text" name="number[2.2]"   value="2.2"/>
  <input type="text" name="number[-2.25]" value="-2.25"/>
  <input type="text" name="null"          value="null"/>
  <input type="text" name="string"        value="text is always string"/>
  <input type="text" name="empty"         value=""/>
</form>
```

```javascript
$('form').serializeJSON();

// returns =>
{
  "bool": {
    "true": "true",
    "false": "false",
  }
  "number": {
    "0": "0",
    "1": "1",
    "2.2": "2.2",
    "-2.25": "-2.25",
  }
  "null": "null",
  "string": "text is always string",
  "empty": ""
}
```

Note that all values are **strings**.

To auto-detect types, you could use the :auto type (append :auto to input name).
Or, you could use the parse options. For example, to parse nulls and numbers:


```javascript
$('form').serializeJSON({parseNulls: true, parseNumbers: true});

// returns =>
{
  "bool": {
    "true": "true", // booleans are still strings, because parseBooleans was not set
    "false": "false",
  }
  "number": {
    "0": 0, // numbers are parsed because parseNumbers: true
    "1": 1,
    "2.2": 2.2,
    "-2.25": -2.25,
  }
  "null": null, // "null" strings are converted to null becase parseNulls: true
  "string": "text is always string",
  "empty": ""
}
```


For rare cases, a custom parser can be defined with a function:

```javascript
var emptyStringsAndZerosToNulls = function(val, inputName) {
  if (val === "") return null; // parse empty strings as nulls
  if (val === 0)  return null; // parse 0 as null
  return val;
}

$('form').serializeJSON({parseWithFunction: emptyStringsAndZerosToNulls, parseNumbers: true});

// returns =>
{
  "bool": {
    "true": "true",
    "false": "false",
  }
  "number": {
    "0": null, // <-- parsed with custom function
    "1": 1,
    "2.2": 2.2,
    "-2.25": -2.25,
  }
  "null": "null",
  "string": "text is always string",
  "empty": null // <-- parsed with custom function
}
```

## Custom Types ##

You can define your own types or override the defaults with the `customTypes` option. For example:

```html
<form>
  <input type="text" name="scary:alwaysBoo" value="not boo"/>
  <input type="text" name="str:string"      value="str"/>
  <input type="text" name="number:number"   value="5"/>
</form>
```

```javascript
$('form').serializeJSON({
  customTypes: {
    alwaysBoo: function(str) { // value is always a string
      return "boo";
    },
    string: function(str) { // all strings will now end with " override"
      return str + " override";
    }
  }
});

// returns =>
{
  "scary": "boo",        // <-- parsed with type :alwaysBoo
  "str": "str override", // <-- parsed with new type :string (instead of the default)
  "number": 5,           // <-- the default :number still works
}
```

The default types are defined in `$.serializeJSON.defaultOptions.defaultTypes`. If you want to define your own set of types, you could also re-define that option (it will not override the types, but define a new set of types).


## Ignore Empty Form Fields ##

You can use the option `.serializeJSON(skipFalsyValuesForTypes: ["string"])`, which ignores any string field with an empty value (default type is :string, and empty strings are falsy).

Another option, since `serializeJSON()` is called on a jQuery object, is to just use the proper jQuery selector to skip empty values (see [Issue #28](https://github.com/marioizquierdo/jquery.serializeJSON/issues/28) for more info):

```javascript
// Select only imputs that have a non-empty value
$('form :input[value!=""]').serializeJSON();

// Or filter them from the form
obj = $('form').find('input').not('[value=""]').serializeJSON();

// For more complicated filtering, you can use a function
obj = $form.find(':input').filter(function () {
          return $.trim(this.value).length > 0
      }).serializeJSON();
```


## Ignore Fields With Falsy Values ##

When using :types, you can also skip falsy values (`false, "", 0, null, undefined, NaN`) by using the option `skipFalsyValuesForFields: ["fullName", "address[city]"]` or `skipFalsyValuesForTypes: ["string", "null"]`.

Or setting a data attribute `data-skip-falsy="true"` on the inputs that should be ignored. Note that `data-skip-falsy` is aware of field :types, so it knows how to skip a non-empty input like this `<input name="foo" value="0" data-value-type="number" data-skip-falsy="true">` (Note that `"0"` as a string is not falsy, but `0` as number is falsy)).


## Use integer keys as array indexes ##

By default, all serialized keys are **strings**, this includes keys that look like numbers like this:

```html
<form>
  <input type="text" name="arr[0]" value="foo"/>
  <input type="text" name="arr[1]" value="var"/>
  <input type="text" name="arr[5]" value="inn"/>
</form>
```

```javascript
$('form').serializeJSON();

// arr is an object =>
{'arr': {'0': 'foo', '1': 'var', '5': 'inn' }}
```

Which is how Rack [parse_nested_query](http://codefol.io/posts/How-Does-Rack-Parse-Query-Params-With-parse-nested-query) behaves. Remember that serializeJSON input name format is fully compatible with Rails parameters, that are parsed using this Rack method.

Use the option `useIntKeysAsArrayIndex` to interpret integers as array indexes:

```javascript
$('form').serializeJSON({useIntKeysAsArrayIndex: true});

// arr is an array =>
{'arr': ['foo', 'var', undefined, undefined, undefined, 'inn']}
```

**Note**: this was the default behavior of serializeJSON before version 2. You can use this option for backwards compatibility.


## Defaults ##

All options defaults are defined in `$.serializeJSON.defaultOptions`. You can just modify it to avoid setting the option on every call to `serializeJSON`.

For example:

```javascript
$.serializeJSON.defaultOptions.parseAll = true; // parse booleans, numbers and nulls by default

$('form').serializeJSON(); // No options => then use $.serializeJSON.defaultOptions

// returns =>
{
  "bool": {
    "true": true,
    "false": false,
  }
  "number": {
    "0": 0,
    "1": 1,
    "2.2": 2.2,
    "-2.25": -2.25,
  }
  "null": null,
  "string": "text is always string",
  "empty": ""
}
```


Alternatives
------------

I found others solving the same problem:

 * https://github.com/macek/jquery-serialize-object
 * https://github.com/hongymagic/jQuery.serializeObject
 * https://github.com/danheberden/jquery-serializeForm
 * https://github.com/maxatwork/form2js (plain js, no jQuery)
 * https://github.com/serbanghita/formToObject.js (plain js, no jQuery)
 * https://gist.github.com/shiawuen/2634143 (simpler but small)

But none of them checked what I needed at the time `serializeJSON` was created. Factors that differentiate `serializeJSON` from most of the alternatives:

 * Simple and small code base. The minimified version is 1Kb.
 * Yet flexible enough with features like nested objects, unchecked-checkboxes and custom types.
 * Implemented on top of jQuery (or Zepto) `serializeArray`, that creates a JavaScript array of objects, ready to be encoded as a JSON string. It takes into account the W3C rules for [successful controls](http://www.w3.org/TR/html401/interact/forms.html#h-17.13.2), making `serializeJSON` as standard and stable as it can be.
 * The format for the input field names is the same used by Rails (from [Rack::Utils.parse_nested_query](http://codefol.io/posts/How-Does-Rack-Parse-Query-Params-With-parse-nested-query)), that is successfully used by many backend systems and already well understood by many front end developers.
 * Exaustive test suite helps iterate on new releases and bugfixes with confidence.
 * Compatible with [bower](https://github.com/bower/bower), [zepto.js](http://zeptojs.com/) and pretty much every version of [jQuery](https://jquery.com/).


Contributions
-------------

Contributions are awesome. Feature branch *pull requests* are the preferred method. Just make sure to add tests for it. To run the jasmine specs, just open `spec/spec_runner_jquery.html` in your browser.

Changelog
---------
 * *2.8.1* (Dec 09, 2016): Identify issue #67 and throw a descriptive error with a link to the issue, that explains why nested arrays of objects with checkboxes with unchecked values are not supported.
 * *2.8.0* (Dec 09, 2016): Add options `skipFalsyValuesForFields`, `skipFalsyValuesForTypes` and attr `data-skip-falsy` to easily skip falsy values (which includes empty strings). Thanks to [milkaknap](https://github.com/milkaknap).
 * *2.7.2* (Dec 19, 2015): Bugfix #55 (Allow data types with the `data-value-type` attribute to use brackets in names). Thanks to [stricte](https://github.com/stricte).
 * *2.7.1* (Dec 12, 2015): Bugfix #54 (`data-value-type` attribute only works with input elements). Thanks to [madrabaz](https://github.com/madrabaz).
 * *2.7.0* (Nov 28, 2015): Allow to define custom types with the `data-value-type` attribute. Thanks to [madrabaz](https://github.com/madrabaz).
 * *2.6.2* (Oct 24, 2015): Add support for AMD/CommonJS/Browserify modules. Thanks to [jisaacks](https://github.com/jisaacks).
 * *2.6.1* (May 13, 2015): Bugfix #43 (Fix IE 8 compatibility). Thanks to [rywall](https://github.com/rywall).
 * *2.6.0* (Apr 24, 2015): Allow to define custom types with the option `customTypes` and inspect/override default types with the option `defaultTypes`. Thanks to [tygriffin](https://github.com/tygriffin) for the [pull request](https://github.com/marioizquierdo/jquery.serializeJSON/pull/40).
 * *2.5.0* (Mar 11, 2015): Override serialized properties if using the same name, even for nested values, instead of crashing the script, fixing issue#29. Also fix a crash when using Zepto and the data-unchecked-value option.
 * *2.4.2* (Feb 04, 2015): Ignore disabled checkboxes with "data-unchecked-value". Thanks to [skarr](https://github.com/skarr) for the [pull request](https://github.com/marioizquierdo/jquery.serializeJSON/pull/33).
 * *2.4.1* (Oct 12, 2014): Add `:auto` type, that works like the `parseAll` option, but targeted to a single input.
 * *2.4.0* (Oct 12, 2014): Implement :types. Types allow to easily specify how to parse each input.
 * *2.3.2* (Oct 11, 2014): Bugfix #27 (parsing error on nested keys like name="foo[inn[bar]]"). Thanks to [danlo](https://github.com/danlo) for finding the issue.
 * *2.3.1* (Oct 06, 2014): Bugfix #22 (ignore checkboxes with no name when doing `checkboxUncheckedValue`). Thanks to [KATT](https://github.com/KATT) for finding and fixing the issue.
 * *2.3.0* (Sep 25, 2014): Properly spell "data-unckecked-value", change for "data-unchecked-value"
 * *2.2.0* (Sep 17, 2014): Add option `checkboxUncheckedValue` and attribute `data-unckecked-value` to allow parsing unchecked checkboxes.
 * *2.1.0* (Jun 08, 2014): Add option `parseWithFunction` to allow custom parsers. And fix issue #14: empty strings were parsed as a zero when `parseNumbers` option was true.
 * *2.0.0* (May 04, 2014): Nested keys are always object attributes by default (discussed on issue #12). Set option `$.serializeJSON.defaultOptions.useIntKeysAsArrayIndex = true;` for backwards compatibility (see **Options** section). Thanks to [joshuajabbour](https://github.com/joshuajabbour) for finding the issue.
 * *1.3.0* (May 03, 2014): Accept options {parseBooleans, parseNumbers, parseNulls, parseAll} to modify what type to values are interpreted from the strings. Thanks to [diaswrd](https://github.com/diaswrd) for finding the issue.
 * *1.2.3* (Apr 12, 2014): Lowercase filenames.
 * *1.2.2* (Apr 03, 2014): Now also works with [Zepto.js](http://zeptojs.com/).
 * *1.2.1* (Mar 17, 2014): Refactor, cleanup, lint code and improve test coverage.
 * *1.2.0* (Mar 11, 2014): Arrays with empty index and objects with empty values are added and not overriden. Thanks to [kotas](https://github.com/kotas).
 * *1.1.1* (Feb 16, 2014): Only unsigned integers are used to create arrays. Alphanumeric keys are always for objects. Thanks to [Nicocin](https://github.com/Nicocin).
 * *1.0.2* (Jan 07, 2014): Tag to be on the jQuery plugin registry.
 * *1.0.1* (Aug 20, 2012): Bugfix: ensure that generated arrays are being displayed when parsed with JSON.stringify
 * *1.0.0* (Aug 20, 2012): Initial release

Author
-------

Written and maintained by [Mario Izquierdo](https://github.com/marioizquierdo)
