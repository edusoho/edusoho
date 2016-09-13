<?php

namespace Bazinga\JsTranslationBundle\Tests\Finder;

use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class TranslationDumperTest extends WebTestCase
{
    private $target;

    private $filesystem;

    private $dumper;

    public function setUp()
    {
        $client    = static::createClient();
        $container = $client->getContainer();

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

    public function testDump()
    {
        $this->dumper->dump($this->target);

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

        $this->assertEquals(<<<JS
(function (Translator) {
    // fr
    Translator.add("hello", "bonjour", "messages", "fr");
})(Translator);

JS
        , file_get_contents($this->target . '/translations/messages/fr.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    // en
    Translator.add("hello", "hello", "messages", "en");
})(Translator);

JS
        , file_get_contents($this->target . '/translations/messages/en.js'));

        $this->assertEquals(<<<JS
(function (Translator) {
    Translator.fallback      = 'en';
    Translator.defaultDomain = 'messages';
})(Translator);

JS
        , file_get_contents($this->target . '/translations/config.js'));

        $this->assertEquals(<<<JSON
{
    "translations": {"fr":{"messages":{"hello":"bonjour"}}}
}

JSON
        , file_get_contents($this->target . '/translations/messages/fr.json'));

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"messages":{"hello":"hello"}}}
}

JSON
        , file_get_contents($this->target . '/translations/messages/en.json'));

        $this->assertEquals(<<<JSON
{
    "fallback": "en",
    "defaultDomain": "messages"
}

JSON
        , file_get_contents($this->target . '/translations/config.json'));

        $this->assertEquals(<<<JSON
{
    "translations": {"en":{"numerics":{"7":"Nos occasions","8":"Nous contacter","12":"pr\u00e9nom","13":"nom","14":"adresse","15":"code postal"}}}
}

JSON
        , file_get_contents($this->target . '/translations/numerics/en.json'));
    }
}
