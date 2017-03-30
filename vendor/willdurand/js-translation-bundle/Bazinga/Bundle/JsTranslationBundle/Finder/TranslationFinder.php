<?php

namespace Bazinga\Bundle\JsTranslationBundle\Finder;

use Symfony\Component\Finder\Finder;

/**
 * @author William DURAND <william.durand1@gmail.com>
 * @author Markus Poerschke <markus@eluceo.de>
 * @author Hugo MONTEIRO <hugo.monteiro@gmail.com>
 */
class TranslationFinder
{
    /**
     * @var array list of translation files from the framework bundle
     */
    private $translationFilesByLocale;

    /**
     * @param array $translationFilesByLocale all the translations whose index is the locale
     */
    public function __construct(array $translationFilesByLocale)
    {
        $this->translationFilesByLocale = $translationFilesByLocale;
    }

    /**
     * Returns an array of translation files for a given domain,
     * and a given locale.
     *
     * @param string $domain A domain translation name
     * @param string $locale A locale
     *
     * @return array An array of translation files.
     */
    public function get($domain, $locale)
    {
        $filteredFilenames = $this->getTranslationFilesFromConfiguration($domain, $locale);

        return $filteredFilenames;
    }

    /**
     * Returns an array of all translation files.
     *
     * @return array An array of translation files.
     */
    public function all()
    {
        $filteredFilenames = $this->getAllTranslationFilesFromConfiguration();

        return $filteredFilenames;
    }

    /**
     * @return array all translation file names loaded from the FrameworkBundle
     */
    private function getAllTranslationFilesFromConfiguration()
    {
        $filteredFilenames = array();

        foreach ($this->translationFilesByLocale as $localeFromConfig => $resourceFilePaths) {
            foreach ($resourceFilePaths as $filename) {
                $filteredFilenames[] = $filename;
            }
        }
        return $filteredFilenames;
    }

    /**
     * @param string $domain
     * @param string $locale
     *
     * @return array all translation file names loaded from the FrameworkBundle
     */
    private function getTranslationFilesFromConfiguration($domain, $locale)
    {
        $filteredFilenames = array();

        foreach ($this->translationFilesByLocale as $localeFromConfig => $resourceFilePaths) {
            foreach ($resourceFilePaths as $filename) {
                list($currentDomain, $currentLocale) = explode('.', basename($filename), 3);

                if ($currentDomain === $domain && $currentLocale === $locale) {
                    $filteredFilenames[] = $filename;
                }
            }
        }
        return $filteredFilenames;
    }
}
