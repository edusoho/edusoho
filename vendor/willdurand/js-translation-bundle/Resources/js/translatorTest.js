module('Translator', {
    setup: function() {
        Translator.reset();
    }
});

test('api definition', function() {
    expect(5);

    ok(Translator, 'Translator is defined');
    ok($.isFunction(Translator.trans), 'Translator.trans is a function');
    ok($.isFunction(Translator.transChoice), 'Translator.transChoice is a function');
    ok($.isFunction(Translator.add), 'Translator.add is a function');
    ok($.isFunction(Translator.fromJSON), 'Translator.fromJSON is a function');
});

test('add()', function() {
    expect(1);

    strictEqual(Translator.add('foo', 'bar'), Translator, 'The add method returns a Translator');
});

test('trans()', function() {
    expect(11);

    Translator.add('foo', 'bar', 'Foo');
    Translator.add('foo.with.arg', 'This is Ba %arg%');
    Translator.add('foo.with.arg', 'This is Ba %arg%', 'Foo');
    Translator.add('foo.with.args', 'There are %bananas% bananas and %apples% apples.', 'Foo');
    Translator.add('foo.with.replaces', '%repeat% %repeat% %repeat% !!!', 'Bar');
    Translator.add('empty', '', 'Foo');
    Translator.add('empty', '');

    // Basic
    equal(Translator.trans('foo', {}, 'Foo'), 'bar', 'Returns the correct message for the given key');
    equal(Translator.trans('foo.with.arg', {}, 'Foo'), 'This is Ba %arg%', 'Returns the correct message for the given key');
    equal(Translator.trans('foo.with.args', {}, 'Foo'), 'There are %bananas% bananas and %apples% apples.', 'Returns the correct message for the given key');

    equal(Translator.trans(''), '', 'Empty key returns empty message');
    equal(Translator.trans('unknown.key'), 'unknown.key', 'Unknown key returns the key as message');

    // Placeholders
    equal(Translator.trans('foo.with.arg', { arg: 'Bar' }, 'Foo'), 'This is Ba Bar', 'Returns the message with correct replaces');
    equal(Translator.trans('foo.with.args', { bananas: 10, apples: 2 }, 'Foo'), 'There are 10 bananas and 2 apples.', 'Returns the message with correct replaces');
    equal(Translator.trans('foo.with.replaces', { repeat: 'ah' }, 'Bar'), 'ah ah ah !!!', 'Returns the message with correct repeat replaces');

    // Empty string translations
    equal(Translator.trans('empty', {}, 'Foo'), '', 'An empty string translation should return the empty string and not the key.');

    // Message not in a domain with replaces
    equal(Translator.trans('Message not in the domain with %arg%', {arg: 'Bar'}), 'Message not in the domain with Bar', 'Message not in the domain with args returns the processed message');

    // special case dollar ($0 is treated specially in reg-exps; especially in IE11)
    equal(Translator.trans('foo.with.arg', { arg: '$0.01' }, 'Foo'), 'This is Ba $0.01', 'Returns the message with correct replaces with $0 present in target value');
});

test('transChoice()', function() {
    expect(31);

    Translator.add('foo.plural', '{0} Nothing|[1,Inf[ Many things', 'Foo');
    Translator.add('foo.plural.with.args', '{0} Nothing|{1} One thing|[2,Inf[ %count% things', 'Foo');
    Translator.add('foo.plural.with.inf', ']-Inf,0[ Underground|{0} Ground 0|{1} First level|[2,Inf[ High level', 'Foo');
    Translator.add('complex.plural', '{0} There is no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples', 'Foo');
    Translator.add('foo.plural.space.before.interval', ' {0} Nothing| [1,Inf[ Many things', 'Foo');
    Translator.add('foo.plural.without.space', '{0}Nothing|[1,Inf[Many things', 'Foo');
    Translator.add('foo.single', 'Things', 'Foo');
    Translator.add('foo.count.parameter', '[0,1]%count% item|]1,Inf[%count% items', 'Foo');
    Translator.add('foo.count.parameter.additional', '[0,Inf[%count% items of %foo%', 'Foo');
    Translator.add('foo.count.parameter.overriden', '[0,Inf[%count% items', 'Foo');

    // Basic
    equal(Translator.transChoice('foo.plural', null, {}, 'Foo'), '{0} Nothing|[1,Inf[ Many things', 'Returns the correct message for the given key');

    // Translations
    equal(Translator.transChoice('foo.plural', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    equal(Translator.transChoice('foo.plural.with.args', 0, { count: 0 }, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 1, { count: 1 }, 'Foo'), 'One thing', 'number = 1 returns the {1} part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 2, { count: 2 }, 'Foo'), '2 things', 'number = 2 returns the [2,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 100, { count: 100 }, 'Foo'), '100 things', 'number = 100 returns the [2,Inf[ part of the message');

    equal(Translator.transChoice('foo.plural.with.inf', -100000, {}, 'Foo'), 'Underground', 'number = -100000 returns the ]-Inf,0] part of the message');
    equal(Translator.transChoice('foo.plural.with.inf', -1, {}, 'Foo'), 'Underground', 'number = -1 returns the ]-Inf,0] part of the message');
    equal(Translator.transChoice('foo.plural.with.inf', 0, {}, 'Foo'), 'Ground 0', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural.with.inf', 1, {}, 'Foo'), 'First level', 'number = 1 returns the {1} part of the message');
    equal(Translator.transChoice('foo.plural.with.inf', 10000, {}, 'Foo'), 'High level', 'number = 1000 returns the [2,Inf[ part of the message');

    equal(Translator.transChoice('complex.plural', 0, {}, 'Foo'), 'There is no apples', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('complex.plural', 1, {}, 'Foo'), 'There is one apple', 'number = 1 returns the standard rule');
    equal(Translator.transChoice('complex.plural', 9, { count: 9 }, 'Foo'), 'There are 9 apples', 'number = 10 returns the "a_few" part of the message');
    equal(Translator.transChoice('complex.plural', 20, {}, 'Foo'), 'There are many apples', 'number = 20 returns the [20,Inf] part of the message');

    // Translations with spaces before intervals
    equal(Translator.transChoice('foo.plural.space.before.interval', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural.space.before.interval', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural.space.before.interval', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Translations witout spaces
    equal(Translator.transChoice('foo.plural.without.space', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural.without.space', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural.without.space', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Fallback to default translation
    equal(Translator.transChoice('foo.single', 1, {}, 'Foo'), 'Things', 'number = 1 returns the single available translation');
    equal(Translator.transChoice('foo.single', 2, {}, 'Foo'), 'Things', 'number = 2 returns the single available translation');

    // Message not in a domain with pluralization
    equal(Translator.transChoice('{0} Nothing|[1,Inf[ Many things', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');

    // Default count parameter
    equal(Translator.transChoice('foo.count.parameter', 0, {}, 'Foo'), '0 item', 'number = 0 returns the [0, 1] part of the message');
    equal(Translator.transChoice('foo.count.parameter', 1, {}, 'Foo'), '1 item', 'number = 1 returns the [0, 1] part of the message');
    equal(Translator.transChoice('foo.count.parameter', 5, {}, 'Foo'), '5 items', 'number = 5 returns the ]1,Inf[ part of the message');

    // Default count parameter with additional parameters
    equal(Translator.transChoice('foo.count.parameter.additional', 10, {'foo': 'bar'}, 'Foo'), '10 items of bar', 'number = 10 returns the [0,Inf[ part of the message');

    // Do not override given count parameter
    equal(Translator.transChoice('foo.count.parameter.overriden', 10, { count: 5 }, 'Foo'), '5 items', 'number = 10 returns the [0,Inf[ part of the message');
});

test('guesses domains if not specified', function() {
    expect(8);

    Translator.add('test', 'yop', 'Domain');
    Translator.add('test', 'lait', 'messages');
    Translator.add('foo.bar', 'baz', 'Domain');
    Translator.add('note.list.link', 'zur\u00fcck zur Notizliste', 'AcmeDemoBundle');

    equal(Translator.trans('test'), 'yop', 'Returns the first guessed message corresponding to the given key');
    equal(Translator.trans('test', {}, 'messages'), 'lait', 'Guesser does not impact basic usage of get');
    equal(Translator.trans('foo.bar'), 'baz', 'Returns the correct guessed message');
    equal(Translator.trans('boo.baz'), 'boo.baz', 'Returns the key as the key cannot be guessed');
    equal(Translator.trans('foo.bar'), 'baz', 'Returns the correct guessed message');
    equal(Translator.trans('foo.bar', {}, 'Domain'), 'baz', 'Returns the correct guessed message');
    equal(Translator.trans('foo.bar', {}, 'messages'), 'foo.bar', 'Returns the key as it does not exist in the given domain');
    equal(Translator.trans('note.list.link'), 'zur\u00fcck zur Notizliste');
});

test('loads data from JSON string', function() {
    expect(4);

    // accepts valid JSON string
    Translator.fromJSON('{ "locale": "en", "translations": { "en": { "messages": { "foo": "bar" } } } }');

    equal(Translator.locale, 'en', 'JSON parser processes locale from valid JSON string');
    equal(Translator.trans('foo'), 'bar', 'JSON parser processes messages from valid JSON string');
    equal(Translator.trans('foo', {}, 'messages'), 'bar', 'JSON parser processes messages from valid JSON string');
    equal(Translator.trans('foo', {}, 'messages', 'en'), 'bar', 'JSON parser processes messages from valid JSON string');
});

test('loads data from JSON object literal', function() {
    expect(6);

    Translator.fromJSON({
        "locale": "pt",
        "translations": {
            "pt": {
                "more_messages": {
                    "moo": "mar"
                }
            }
        }
    });

    equal(Translator.locale, 'pt', 'JSON parser processes locale from valid object literal');
    equal(Translator.trans('moo'), 'mar', 'JSON parser processes messages from valid object literal');
    equal(Translator.trans('moo', {}, 'more_messages'), 'mar', 'JSON parser processes messages from valid object literal');
    equal(Translator.trans('moo', {}, 'more_messages', 'pt'), 'mar', 'JSON parser processes messages from valid object literal');

    Translator.fromJSON({
        "locale": "en",
        "fallback": "en",
        "translations": {"en":[]}
    });
    equal(Translator.locale, 'en');
    equal(Translator.fallback, 'en');
});

test('deals with multiple locales', function() {
    expect(3);

    // Simulate translations/messages/en.js loading
    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');
    Translator.add('symfony2.powerful', 'Symfony2 is powerful', 'messages', 'en');

    // Simulate translations/messages/fr.js loading
    Translator.add('symfony2.great', 'J\'aime Symfony2', 'messages', 'fr');

    // Test with locale = fr
    Translator.locale = 'fr';
    equal(Translator.trans('symfony2.great'), 'J\'aime Symfony2');

    // Test with locale = en
    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');
    equal(Translator.trans('symfony2.powerful'), 'Symfony2 is powerful');
});

test('uses fallback if the given locale does not contain the message', function() {
    expect(4);

    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');

    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');

    Translator.locale = 'de';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');
    equal(Translator.trans('symfony2.great', {}, null, 'pt'), 'I like Symfony2');
    equal(Translator.trans('symfony2.great', {}, undefined, 'pt'), 'I like Symfony2');
});

test('retry on fallback when locale exist but not the domain', function() {
    expect(5);

    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');
    Translator.add('symfony2.domain', 'Just creating another default domain', 'domain', 'de');

    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');

    Translator.locale = 'de';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');
    equal(Translator.trans('symfony2.domain'), 'Just creating another default domain');
    equal(Translator.trans('symfony2.great', {}, null, 'pt'), 'I like Symfony2');
    equal(Translator.trans('symfony2.great', {}, undefined, 'pt'), 'I like Symfony2');
});

test('gets the current locale using the `lang` attribute on the `html` tag', function() {
    expect(1);

    equal(Translator.locale, 'fr');
});

test('finds longer locale names', function() {
    expect(1);

    Translator.add('symfony2.powerful', 'Symfony2 is powerful', 'messages', 'de_DE');

    Translator.locale = 'de_DE';
    equal(Translator.trans('symfony2.powerful'), 'Symfony2 is powerful');
});

test('searches in part domain, if not exists in full domain', function() {
    expect(2);

    Translator.add('symfony2.powerful', 'Ich liebe Symfony2', 'messages', 'de_DE');
    Translator.add('symfony2.great', 'Ich mag Symfony2', 'messages', 'de');
    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');

    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');

    Translator.locale = 'de_DE';
    equal(Translator.trans('symfony2.great'), 'Ich mag Symfony2');
});

test('searches in fallback domain, if not exists in full or part domain', function() {
    expect(2);

    Translator.fromJSON({
        "fallback": "en"
    });

    Translator.add('symfony2.powerful', 'Symfony2 ist gigantisch', 'messages', 'de_DE');
    Translator.add('symfony2.powerful', 'Symfony2 ist groß', 'messages', 'de');
    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');

    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');

    Translator.locale = 'de_DE';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');
});

test('works with given optional parameters', function() {
    expect(2);

    Translator.add('symfony2.great', 'Symfony2 ist gigantisch', 'messages', 'de_DE');
    Translator.add('symfony2.great', 'Symfony2 ist groß', 'messages', 'de');
    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');

    Translator.locale = 'en';
    equal(Translator.trans('symfony2.great'), 'I like Symfony2');

    equal(Translator.trans('symfony2.great', {}, 'messages', 'de_DE'), 'Symfony2 ist gigantisch');
});

test('searches in country fallback, if not exists in full domain', function() {
    expect(1);

    Translator.fromJSON({
        "fallback": "en"
    });

    Translator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');
    Translator.add('symfony2.great', 'Symfony2 ist groß', 'messages', 'de');

    Translator.locale = 'de_CH';
    equal(Translator.trans('symfony2.great'), 'Symfony2 ist groß');
});
