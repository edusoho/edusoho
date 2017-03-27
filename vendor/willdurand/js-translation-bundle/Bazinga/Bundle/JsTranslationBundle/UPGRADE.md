From 1.x to 2.0
===============

* The package name has been rename from `willdurand/expose-translation-bundle`
  to `willdurand/js-translation-bundle`.

* This bundle now requires Symfony `2.3` and above.

* The bundle has been renamed from `BazingaExposeTranslationBundle` to
  `BazingaJsTranslationBundle`.

* The namespace has been changed to be consistent with the other _Bazinga_
  bundles:

```
// before
new \Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle()

// after
new \Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle()
```

* The routing definition that you have to import has changed:

```yml
# app/config/routing.yml

# before
_bazinga_exposetranslation:
    resource: "@BazingaExposeTranslationBundle/Resources/config/routing/routing.yml"

# after
_bazinga_jstranslation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"
```

* As the bundle's name has changed, asset paths have changed as well:

```
// before
<script type="text/javascript" src="{{ asset('bundles/bazingaexposetranslation/js/translator.min.js') }}"></script>

// after
<script src="{{ asset('bundles/bazingajstranslation/js/translator.min.js') }}"></script>
```

* Same thing for how translation files are loaded:

```
// before
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js') }}"></script>

// after
<script src="{{ url('bazinga_jstranslation_js') }}"></script>
```

* The configuration root name has changed:

```yaml
# app/config/config*.yml

# before
bazinga_expose_translation: ~

# after
bazinga_js_translation: ~
```

* The JS `Translator` now mimics the Symfony `TranslatorInterface` and `get()`,
  `has()` methods have since been removed. Methods `trans()` and `transChoice()`
  have been introduced:

```
// before
Translator.get('foo')
Translator.get('apples', {"count" : 0}, 0);

// after
Translator.trans('foo')
Translator.transChoice('apples', 0, {"count" : 0});
```

* Messages keys (aka ids) were prefixed by their translation domain, and you
  could get a translation by using the following `DOMAIN:id`. This is not
  possible anymore. You must pass the `DOMAIN` to the `trans()`/`transChoice()`
  methods, or let the `Translator` find the `id` itself (hopefully).

```
// before
Translator.get('HELLO:foo')

// after
Translator.trans('foo', {}, 'HELLO')
```

* The `defaultDomains` configuration parameter has been removed. This parameter
  was useful to retrieve a translation domain when not provided. This is now
  automatically done.

* The route pattern has changed:

```
// before
/i18n/DOMAIN/LOCALE.js

// after
/translations/DOMAIN.js
```

By default, it serves the translation messages for the current application's
locale. However, you can ask for different locales by using the `locales` query
parameter:

```
/translations/DOMAIN.js?locales=fr,en
```

* The commands have been renamed from `bazinga:expose-translation:*` to
  `bazinga:js-translation:*`.

* The `dump` command has evolved, and now generates files into
  `web/translations`. It generates both JavaScript and JSON files, without
  configuration sections (i.e. it generates files that only add translation
  messages to the JS `Translator`). Two special files, `config.js` and
  `config.json`, are now generated and contain the configuration for the JS
  `Translator`.

* You should add the current application's locale into your layout, by adding
  a lang attribute to the html tag:

```html
<html lang="{{ app.request.locale }}">
```

* The route's placeholder named `domain_name` has gone, it is now named
  `domain`.
