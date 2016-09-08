<?php

namespace Bazinga\Bundle\JsTranslationBundle\Dumper;

use Bazinga\Bundle\JsTranslationBundle\Finder\TranslationFinder;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 */
class TranslationDumper
{
    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var TranslationFinder
     */
    private $finder;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $loaders = array();

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array List of locales translations to dump
     */
    private $activeLocales;

    /**
     * @var array List of domains translations to dump
     */
    private $activeDomains;

    /**
     * @var string
     */
    private $localeFallback;

    /**
     * @var string
     */
    private $defaultDomain;

    /**
     * @param EngineInterface   $engine         The engine.
     * @param TranslationFinder $finder         The translation finder.
     * @param RouterInterface   $router         The router.
     * @param FileSystem        $filesystem     The file system.
     * @param string            $localeFallback
     * @param string            $defaultDomain
     */
    public function __construct(
        EngineInterface $engine,
        TranslationFinder $finder,
        RouterInterface $router,
        Filesystem $filesystem,
        $localeFallback = '',
        $defaultDomain  = '',
        array $activeLocales = array(),
        array $activeDomains = array()
    ) {
        $this->engine         = $engine;
        $this->finder         = $finder;
        $this->router         = $router;
        $this->filesystem     = $filesystem;
        $this->localeFallback = $localeFallback;
        $this->defaultDomain  = $defaultDomain;
        $this->activeLocales  = $activeLocales;
        $this->activeDomains  = $activeDomains;
    }

    /**
     * Get array of active locales
     */
    public function getActiveLocales()
    {
        return $this->activeLocales;
    }

    /**
     * Get array of active locales
     */
    public function getActiveDomains()
    {
        return $this->activeDomains;
    }

    /**
     * Add a translation loader if it does not exist.
     *
     * @param string          $id     The loader id.
     * @param LoaderInterface $loader A translation loader.
     */
    public function addLoader($id, $loader)
    {
        if (!array_key_exists($id, $this->loaders)) {
            $this->loaders[$id] = $loader;
        }
    }

    /**
     * Dump all translation files.
     *
     * @param string $target Target directory.
     */
    public function dump($target = 'web/js')
    {
        $route         = $this->router->getRouteCollection()->get('bazinga_jstranslation_js');
        $requirements  = $route->getRequirements();
        $formats       = explode('|', $requirements['_format']);

        $routeDefaults = $route->getDefaults();
        $defaultFormat = $routeDefaults['_format'];

        $parts = array_filter(explode('/', $route->getPath()));
        $this->filesystem->remove($target. '/' . current($parts));

        $this->dumpConfig($route, $formats, $target);
        $this->dumpTranslations($route, $formats, $target);
    }

    private function dumpConfig($route, array $formats, $target)
    {
        foreach ($formats as $format) {
            $file = sprintf('%s/%s',
                $target,
                strtr($route->getPath(), array(
                    '{domain}'  => 'config',
                    '{_format}' => $format
                ))
            );

            $this->filesystem->mkdir(dirname($file));

            if (file_exists($file)) {
                $this->filesystem->remove($file);
            }

            file_put_contents(
                $file,
                $this->engine->render('BazingaJsTranslationBundle::config.' . $format . '.twig', array(
                    'fallback'      => $this->localeFallback,
                    'defaultDomain' => $this->defaultDomain,
                ))
            );
        }
    }

    private function dumpTranslations($route, array $formats, $target)
    {
        foreach ($this->getTranslations() as $locale => $domains) {
            foreach ($domains as $domain => $translations) {
                foreach ($formats as $format) {
                    $content = $this->engine->render('BazingaJsTranslationBundle::getTranslations.' . $format . '.twig', array(
                        'translations'   => array($locale => array(
                            $domain => $translations,
                        )),
                        'include_config' => false,
                    ));

                    $file = sprintf('%s/%s',
                        $target,
                        strtr($route->getPath(), array(
                            '{domain}'  => sprintf('%s/%s', $domain, $locale),
                            '{_format}' => $format
                        ))
                    );

                    $this->filesystem->mkdir(dirname($file));

                    if (file_exists($file)) {
                        $this->filesystem->remove($file);
                    }

                    file_put_contents($file, $content);
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getTranslations()
    {
        $translations = array();
        $activeLocales = $this->activeLocales;
        $activeDomains = $this->activeDomains;
        foreach ($this->finder->all() as $file) {
            list($extension, $locale, $domain) = $this->getFileInfo($file);

            if ( (count($activeLocales) > 0 && !in_array($locale, $activeLocales)) || (count($activeDomains) > 0 && !in_array($domain, $activeDomains)) ) {
                continue;
            }

            if (!isset($translations[$locale])) {
                $translations[$locale] = array();
            }

            if (!isset($translations[$locale][$domain])) {
                $translations[$locale][$domain] = array();
            }

            if (isset($this->loaders[$extension])) {
                $catalogue = $this->loaders[$extension]
                    ->load($file, $locale, $domain);

                $translations[$locale][$domain] = array_replace_recursive(
                    $translations[$locale][$domain],
                    $catalogue->all($domain)
                );
            }
        }

        return $translations;
    }

    private function getFileInfo($file)
    {
        $filename  = explode('.', $file->getFilename());
        $extension = end($filename);
        $locale    = prev($filename);

        $domain = array();
        while (prev($filename)) {
            $domain[] = current($filename);
        }
        $domain = implode('.', $domain);

        return array($extension, $locale, $domain);
    }
}
