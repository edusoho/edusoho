JsTranslationBundle
===================

A pretty nice way to expose your Symfony2 translation messages to your client
applications.

**Important:** This documentation has been written for version `2.0.0` and above
of this bundle. For version `1.x`, please read:
[https://github.com/willdurand/BazingaJsTranslationBundle/blob/1.2.1/Resources/doc/index.md](https://github.com/willdurand/BazingaJsTranslationBundle/blob/1.2.1/Resources/doc/index.md).
Also, you might be interested in this [UPGRADE
guide](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/UPGRADE.md).

- [Installation](#installation)
- [Usage](#usage)
    - [Load Translations](#load-translations)
        - [Domains](#domains)
        - [Locales](#locales)
        - [Loading via JSON](#loading-via-json)
    - [The dump Command](#the-dump-command)
        - [Assetic](#assetic)
    - [The JS Translator](#the-js-translator)
        - [Message Placeholders / Parameters](#message-placeholders--parameters)
        - [Pluralization](#pluralization)
        - [Get The Locale](#get-the-locale)
- [Examples](#examples)
- [More configuration](#more-configuration)
- [Reference Configuration](#reference-configuration)
- [Testing](#testing)

Installation
------------

### Require via Composer

Install the bundle:

    composer require "willdurand/js-translation-bundle"

Register the bundle in `app/AppKernel.php`:

``` php
<?php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
    );
}
```

Register the routing in `app/config/routing.yml` _(optional: Because the dump command does not depend on the router component)_:

``` yaml
# app/config/routing.yml
_bazinga_jstranslation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"
```

Publish assets:

    php bin/console assets:install --symlink web

### Require via NPM (optional)

Install the package:

    npm install bazinga-translator --save

This step is optional because the files exposed by the npm package are also part of the composer bundle.
Normally you would do this if you prefer to keep all your front-end dependencies in one place, or if you wish to include the `Translator` object as a module dependency in your JS files.

**Important**: it is strongly recommended that you use the same version of the composer bundle and the npm package.


Usage
-----

To use the `Translator` object in your JS files you can either load it globally or `require` / `import` it as a module.

* To load it globally add the following line to your template:

``` html
<script src="{{ asset('bundles/bazingajstranslation/js/translator.min.js') }}"></script>
```

* To load it as a module you must be using a module bundler, like `webpack` and it is recommended that you install the translator via `npm`. Then in your JS files you can do:

``` js
// ES2015
import Translator from 'bazinga-translator';
```

``` js
// ES5
var Translator = require('bazinga-translator');
```

Then add the current application's locale into your layout, by adding a `lang`
attribute to the `html` tag:

```html
<html lang="{{ app.request.locale|split('_')[0] }}">
```

Now, you are done with the basic setup, and you can specify the [translation
files](https://symfony.com/doc/current/translation.html#translation-resource-file-names-and-locations)
you want to load.

### Load Translations

Loading translations is a matter of adding a new `script` tag as follows:

``` html
<script src="{{ url('bazinga_jstranslation_js') }}"></script>
```

This will use the current `locale` and will return the translated messages found
in each `messages.CURRENT_LOCALE.*` files of your project.

In case you do not want to expose an entire translation domain to your frontend,
you can manually add translations to the translator collections. This simulates
the way how the above script would add translations, but allows you to use any
other renderer (like `Twig` or `php`) to make translations accessible

```twig
<script>
/**
 * Adds a translation entry.
 *
 * @param {String} id         The message id
 * @param {String} message    The message to register for the given id
 * @param {String} [domain]   The domain for the message or null to use the default
 * @param {String} [locale]   The locale or null to use the default
 * @return {Object}           Translator
 * @api public
 */
Translator.add(
    'translation_key',
    '{{ 'translation_key'|trans }}',
    'messages',
    'en'
);
</script>
```
Manually adding single translations allows your translators to change the translation
or placeholder ordering without the need of a separate translation domain, or without
having to change the `Twig` or `js` view.

#### Domains

You can add translations that are bound to a given
[domain](http://symfony.com/doc/current/components/translation/introduction.html#using-message-domains):

``` html
<script src="{{ url('bazinga_jstranslation_js', { 'domain': 'DOMAIN_NAME' }) }}"></script>
```

This will use the current `locale` and will return the translated messages found
in each `DOMAIN_NAME.CURRENT_LOCALE.*` file of your project.

#### Locales

You can use the `locales` **query parameter** to get translations in a specific
language, or to load translation messages in several languages at once:

``` html
<script src="{{ url('bazinga_jstranslation_js', { 'domain': 'DOMAIN_NAME', 'locales': 'MY_LOCALE' }) }}"></script>
```

This will return the translated messages found in each `DOMAIN_NAME.MY_LOCALE.*`
files of your project.

``` html
<script src="{{ url('bazinga_jstranslation_js', { 'domain': 'DOMAIN_NAME', 'locales': 'fr,en' }) }}"></script>
```

This will return the translated messages found in each `DOMAIN_NAME.(fr|en).*`
file of your project.

#### Loading via JSON

Alternatively, you can load your translated messages via JSON (e.g. using
the `fetch` API, jQuery's `ajax()` or RequireJS's text plugin). Just amend the above mentioned
URLs to also contain the `'_format': 'json'` parameter like so:

``` html
{{ url('bazinga_jstranslation_js', { '_format': 'json' }) }}
```

Then, feed the translator via `Translator.fromJSON(myRetrievedJSONString)`.

### The `dump` Command

This bundle provides a command to dump the translation files:

    php bin/console bazinga:js-translation:dump [target] [--format=js|json] [--pattern=/translations/{domain}.{_format}] [--merge-domains]

The optional `target` argument allows you to override the target directory to
dump JS translation files in. By default, it generates files in the `web/js/`
directory.

The `--format` option allows you to specify which formats must be included in the output.
If you only need JSON files in your project you can do `--format=json`.

The `--pattern` option allows you to specify the url pattern that will be generated when generating the file with the routes (E.g: /translations/{domain}.{_format}). There is no dependency with the router component.

The `--merge-domains` option when set will generate only one file per locale with all the domains in it.
For cases where you prefer to load all language strings at once.

You have to load a `config.js` file, which contains the configuration for the
JS Translator, then you can load all translation files that have been dumped.
Note that dumped files don't contain any configuration, they only add messages
to the JS Translator.

#### Assetic

The command below is useful if you use
[Assetic](http://symfony.com/doc/current/cookbook/assetic/asset_management.html):

```twig
{% javascripts
    'bundles/bazingajstranslation/js/translator.min.js'
    'js/translations/config.js'
    'js/translations/*/*.js' %}
    <script src="{{ asset_url }}"></script>
{% endjavascripts %}
```

In the example above, all translation files from your entire project will be
loaded. Of course you can load specific domains: `js/translations/admin/*.js`.

The default translation URLs let a controller dump the translations. If you
make use of the Assetic, you need to manually dump the translations each time
a translation changes because the Assetic links will point to a static file.

### The JS Translator

The `Translator` object implements the Symfony2
[`TranslatorInterface`](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/TranslatorInterface.php)
and provides the same `trans()` and `transChoice()` methods:

``` javascript
Translator.trans('key', {}, 'DOMAIN_NAME');
// the translated message or undefined

Translator.transChoice('key', 1, {}, 'DOMAIN_NAME');
// the translated message or undefined
```

> **Note:** The JavaScript is AMD ready.

#### Message Placeholders / Parameters

The `trans()` method accepts a second argument that takes an array of parameters:

``` javascript
Translator.trans('key', { "foo" : "bar" }, 'DOMAIN_NAME');
// will replace each "%foo%" in the message by "bar".
```

You can override the placeholder delimiters by setting the `placeHolderSuffix`
and `placeHolderPrefix` attributes.

The `transChoice()` method accepts this array of parameters as third argument:

``` javascript
Translator.transChoice('key', 123, { "foo" : "bar" }, 'DOMAIN_NAME');
// will replace each "%foo%" in the message by "bar".
```

> Read the official documentation about Symfony2 [message
placeholders](https://symfony.com/doc/current/translation.html#message-placeholders).

#### Pluralization

Probably the best feature provided by this bundle! It allows you to use
pluralization exactly like you would do using the Symfony Translator
component.

``` yaml
# app/Resources/messages.en.yml
apples: "{0} There is no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples"
```

``` javascript
Translator.locale = 'en';

Translator.transChoice('apples', 0, {"count" : 0});
// will return "There is no apples"

Translator.transChoice('apples', 1, {"count" : 1});
// will return "There is one apple"

Translator.transChoice('apples', 2, {"count" : 2});
// will return "There are 2 apples"

Translator.transChoice('apples', 10, {"count" : 10});
// will return "There are 10 apples"

Translator.transChoice('apples', 19, {"count" : 19});
// will return "There are 19 apples"

Translator.transChoice('apples', 20, {"count" : 20});
// will return "There are many apples"

Translator.transChoice('apples', 100, {"count" : 100});
// will return "There are many apples"
```

For more information, read the official documentation  about
[pluralization](https://symfony.com/doc/current/translation.html#pluralization).

#### Get The Locale

You can get the current locale by accessing the `locale` attribute:

``` javascript
Translator.locale;
// will return the current locale.
```

By default, the `locale` is set to the value defined in the `lang` attribute of
the `html` tag.


Examples
--------

Consider the following translation files:

``` yaml
# app/Resources/translations/Hello.fr.yml
foo: "Bar"
ba:
    bar: "Hello world"

placeholder: "Hello %username%!"
```

``` yaml
# app/Resources/translations/messages.fr.yml
placeholder: "Hello %username%, how are you?"
```

You can do:

``` javascript
Translator.trans('foo');
// will return 'Bar'

Translator.trans('foo', {}, 'Hello');
// will return 'Bar'

Translator.trans('ba.bar');
// will return 'Hello world'

Translator.trans('ba.bar', {}, 'Hello');
// will return 'Hello world'

Translator.trans('placeholder', {} , 'messages');
// will return 'Hello %username%, how are you?'

Translator.trans('placeholder', {} , 'Hello');
// will return 'Hello %username%!'

Translator.trans('placeholder', { "username" : "will" }, 'messages');
// will return 'Hello will, how are you?'

Translator.trans('placeholder', { "username" : "will" }, 'Hello');
// will return 'Hello will!'

Translator.trans('placeholder', { "username" : "will" });
// will return 'Hello will!' as the `Hello` messages have been loaded before the `messages` ones
```


More configuration
------------------

#### Locale Fallback

If some of your translations are not complete you can enable a fallback for
untranslated messages:

``` yaml
bazinga_js_translation:
    locale_fallback: en  # It is recommended to set the same value used for the
                         # translator fallback.
```

#### Default Domain

You can define the default domain used when translation messages are added
without any given translation domain:

``` yaml
bazinga_js_translation:
    default_domain:       messages
```

#### Active locales

By default, all locales are dumped.
You can define an array of active locales:

``` yaml
bazinga_js_translation:
    active_locales:
        - fr
        - en
```

#### Active Domains

By default, all domains are dumped.
You can define an array of active domains:

``` yaml
bazinga_js_translation:
    active_domains:
        - messages
```


Reference Configuration
-----------------------

``` yaml
# app/config/config*.yml
bazinga_js_translation:
    locale_fallback:      en
    default_domain:       messages
```


Testing
-------

### PHP

Setup the test suite using [Composer](https://getcomposer.org/):

    $ composer install --dev

Run it using PHPUnit:

    $ phpunit

### JavaScript

You can run the JavaScript test suite using [PhantomJS](http://phantomjs.org/):

    $ phantomjs Resources/js/run-qunit.js file://`pwd`/Resources/js/index.html
