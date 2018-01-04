<?php

namespace Bazinga\JsTranslationBundle\Tests\Finder;

use Bazinga\Bundle\JsTranslationBundle\Dumper\TranslationDumper;
use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 * @author Hugo Monteiro <hugo.monteiro@gmail.com>
 */
class TranslationDumperTest extends WebTestCase
{
    const JS_CONFIG = <<<JS
(function (t) {
t.fallback = 'en';
t.defaultDomain = 'messages';
})(Translator);

JS;

    const JS_EN_MERGED_TRANSLATIONS = <<<JS
(function (t) {
// en
t.add("foo", "bar", "foo", "en");
t.add("hello", "hello", "messages", "en");
t.add(7, "Nos occasions", "numerics", "en");
t.add(8, "Nous contacter", "numerics", "en");
t.add(12, "pr\u00e9nom", "numerics", "en");
t.add(13, "nom", "numerics", "en");
t.add(14, "adresse", "numerics", "en");
t.add(15, "code postal", "numerics", "en");
})(Translator);

JS;

    const JS_EN_MESSAGES_TRANSLATIONS = <<<JS
(function (t) {
// en
t.add("hello", "hello", "messages", "en");
})(Translator);

JS;

    const JS_EN_NUMERICS_TRANSLATIONS = <<<JS
(function (t) {
// en
t.add(7, "Nos occasions", "numerics", "en");
t.add(8, "Nous contacter", "numerics", "en");
t.add(12, "pr\u00e9nom", "numerics", "en");
t.add(13, "nom", "numerics", "en");
t.add(14, "adresse", "numerics", "en");
t.add(15, "code postal", "numerics", "en");
})(Translator);

JS;

    const JS_FR_MERGED_TRANSLATIONS = <<<JS
(function (t) {
// fr
t.add("hello", "bonjour", "messages", "fr");
t.add(7, "Nos occasions", "numerics", "fr");
t.add(8, "Nous contacter", "numerics", "fr");
t.add(12, "pr\u00e9nom", "numerics", "fr");
t.add(13, "nom", "numerics", "fr");
t.add(14, "adresse", "numerics", "fr");
t.add(15, "code postal", "numerics", "fr");
})(Translator);

JS;

    const JS_FR_MESSAGES_TRANSLATIONS = <<<JS
(function (t) {
// fr
t.add("hello", "bonjour", "messages", "fr");
})(Translator);

JS;

    const JS_FR_NUMERICS_TRANSLATIONS = <<<JS
(function (t) {
// fr
t.add(7, "Nos occasions", "numerics", "fr");
t.add(8, "Nous contacter", "numerics", "fr");
t.add(12, "pr\u00e9nom", "numerics", "fr");
t.add(13, "nom", "numerics", "fr");
t.add(14, "adresse", "numerics", "fr");
t.add(15, "code postal", "numerics", "fr");
})(Translator);

JS;

    const JSON_CONFIG = <<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON;

    const JSON_EN_MERGED_TRANSLATIONS = <<<JSON
{
    "translations": {"en":{"foo":{"foo":"bar"},"messages":{"hello":"hello"},"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON;

    const JSON_EN_MESSAGES_TRANSLATIONS = <<<JSON
{
    "translations": {"en":{"messages":{"hello":"hello"}}}
}

JSON;

    const JSON_EN_NUMERICS_TRANSLATIONS = <<<JSON
{
    "translations": {"en":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON;

    const JSON_FR_MERGED_TRANSLATIONS = <<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"},"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON;

    const JSON_FR_MESSAGES_TRANSLATIONS = <<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"}}}
}

JSON;

    const JSON_FR_NUMERICS_TRANSLATIONS = <<<JSON
{
    "translations": {"fr":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON;

    private $target;

    private $filesystem;

    private $dumper;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->target     = sys_get_temp_dir() . '/bazinga/js-translation-bundle';
        $this->filesystem = $container->get('filesystem');
        $this->dumper     = $container->get('bazinga.jstranslation.translation_dumper');

        $this->filesystem->mkdir($this->target);
    }

    public function tearDown()
    {
        if (is_dir($this->target)) {
            $this->filesystem->remove($this->target);
        }
    }

    public function testDumpPerDomain()
    {
        $this->dumper->dump(
            $this->target
        );

        foreach (array(
                     'messages/en.js',
                     'messages/en.json',
                     'messages/fr.js',
                     'messages/fr.json',
                     'foo/en.js',
                     'foo/en.json',
                     'numerics/en.js',
                     'numerics/en.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'front/en.js',
                     'front/en.json',
                     'front/fr.js',
                     'front/fr.json',
                     'messages/es.js',
                     'messages/es.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JS_EN_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/en.js'));

        $this->assertEquals(self::JS_FR_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(self::JS_EN_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/en.js'));

        $this->assertEquals(self::JS_FR_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/fr.js'));

        $this->assertEquals(self::JS_CONFIG, file_get_contents($this->target . '/translations/config.js'));

        $this->assertEquals(self::JSON_EN_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/en.json'));

        $this->assertEquals(self::JSON_FR_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/fr.json'));

        $this->assertEquals(self::JSON_EN_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/en.json'));

        $this->assertEquals(self::JSON_FR_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/fr.json'));

        $this->assertEquals(self::JSON_CONFIG, file_get_contents($this->target . '/translations/config.json'));
    }

    public function testDumpPerLocale()
    {
        $this->dumper->dump(
            $this->target,
            TranslationDumper::DEFAULT_TRANSLATION_PATTERN,
            array(),
            (object) array('domains' => true)
        );

        foreach (array(
                     'en.js',
                     'en.json',
                     'fr.js',
                     'fr.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'es.js',
                     'es.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JS_EN_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/en.js'));

        $this->assertEquals(self::JS_FR_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/fr.js'));

        $this->assertEquals(self::JS_CONFIG, file_get_contents($this->target . '/translations/config.js'));

        $this->assertEquals(self::JSON_EN_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/en.json'));

        $this->assertEquals(self::JSON_FR_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/fr.json'));

        $this->assertEquals(self::JSON_CONFIG, file_get_contents($this->target . '/translations/config.json'));
    }

    public function testDumpJsPerDomain()
    {
        $this->dumper->dump(
            $this->target,
            TranslationDumper::DEFAULT_TRANSLATION_PATTERN,
            array('js')
        );

        foreach (array(
                     'foo/en.js',
                     'messages/en.js',
                     'messages/fr.js',
                     'numerics/en.js',
                     'numerics/fr.js',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'foo/en.json',
                     'front/en.js',
                     'front/en.json',
                     'front/fr.js',
                     'front/fr.json',
                     'messages/en.json',
                     'messages/es.js',
                     'messages/es.json',
                     'messages/fr.json',
                     'numerics/en.json',
                     'numerics/fr.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JS_EN_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/en.js'));

        $this->assertEquals(self::JS_FR_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(self::JS_EN_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/en.js'));

        $this->assertEquals(self::JS_FR_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/fr.js'));

        $this->assertEquals(self::JS_CONFIG, file_get_contents($this->target . '/translations/config.js'));
    }

    public function testDumpJsonPerDomain()
    {
        $this->dumper->dump(
            $this->target,
            TranslationDumper::DEFAULT_TRANSLATION_PATTERN,
            array('json')
        );

        foreach (array(
                     'foo/en.json',
                     'messages/en.json',
                     'messages/fr.json',
                     'numerics/en.json',
                     'numerics/fr.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'foo/en.js',
                     'front/en.js',
                     'front/en.json',
                     'front/fr.js',
                     'front/fr.json',
                     'messages/en.js',
                     'messages/es.js',
                     'messages/es.json',
                     'messages/fr.js',
                     'numerics/en.js',
                     'numerics/fr.js',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JSON_EN_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/en.json'));

        $this->assertEquals(self::JSON_FR_MESSAGES_TRANSLATIONS, file_get_contents($this->target . '/translations/messages/fr.json'));

        $this->assertEquals(self::JSON_EN_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/en.json'));

        $this->assertEquals(self::JSON_FR_NUMERICS_TRANSLATIONS, file_get_contents($this->target . '/translations/numerics/fr.json'));

        $this->assertEquals(self::JSON_CONFIG, file_get_contents($this->target . '/translations/config.json'));
    }

    public function testDumpJsPerLocale()
    {
        $this->dumper->dump(
            $this->target,
            TranslationDumper::DEFAULT_TRANSLATION_PATTERN,
            array('js'),
            (object) array('domains' => true)
        );

        foreach (array(
                     'en.js',
                     'fr.js',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'en.json',
                     'es.js',
                     'es.json',
                     'fr.json',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JS_EN_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/en.js'));

        $this->assertEquals(self::JS_FR_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/fr.js'));

        $this->assertEquals(self::JS_CONFIG, file_get_contents($this->target . '/translations/config.js'));
    }

    public function testDumpJsonPerLocale()
    {
        $this->dumper->dump(
            $this->target,
            TranslationDumper::DEFAULT_TRANSLATION_PATTERN,
            array('json'),
            (object) array('domains' => true)
        );

        foreach (array(
                     'en.json',
                     'fr.json',
                 ) as $file) {
            $this->assertFileExists($this->target . '/translations/' . $file);
        }

        foreach (array(
                     'en.js',
                     'es.js',
                     'es.json',
                     'fr.js',
                 ) as $file) {
            $this->assertFileNotExists($this->target . '/translations/' . $file);
        }

        $this->assertEquals(self::JSON_EN_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/en.json'));

        $this->assertEquals(self::JSON_FR_MERGED_TRANSLATIONS, file_get_contents($this->target . '/translations/fr.json'));

        $this->assertEquals(self::JSON_CONFIG, file_get_contents($this->target . '/translations/config.json'));
    }
}
