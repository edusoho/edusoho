<?php

namespace Codeages\PluginBundle\System;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;

class DictCollector
{
    protected $locale;
    protected $cacheDir;
    protected $debug;
    protected $files;

    public function __construct(array $files, $cacheDir, $debug, $locale)
    {
        $this->files = $files;
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
        $this->locale = $locale;
    }

    private function loadDictFile()
    {
        $resources = array();
        $dict = array();
        $defaultDict = array();

        foreach ($this->files as $file) {
            $resources[] = new FileResource($file);

            $basename = basename($file);
            $basenameParts = explode('.', $basename);
            $locale = $basenameParts[1];

            $localeDict = isset($dict[$locale]) ? $dict[$locale] : array();
            $dict[$locale] = array_merge($localeDict, Yaml::parse(file_get_contents($file)));

            if ($locale == $this->locale) {
                $defaultDict = array_merge($defaultDict, Yaml::parse(file_get_contents($file)));
            }
        }

        $this->cacheDictFile($dict, $defaultDict, $resources);
    }

    private function cacheDictFile($dict, $defaultDict, $resources)
    {
        foreach ($dict as $key => $localDict) {
            $cacheFile = $this->cacheDir."/dict.{$key}.php";
            $cache = new ConfigCache($cacheFile, $this->debug);
            if ($key != $this->locale) {
                $localDict = array_merge($defaultDict, $localDict);
            }
            $cache->write(sprintf('<?php return %s;', var_export($localDict, true)), $resources);
        }
    }

    private function getDict($userLocale)
    {
        $userLocaleCacheFile = $this->cacheDir."/dict.{$userLocale}.php";
        $cache = new ConfigCache($userLocaleCacheFile, $this->debug);
        if ($cache->isFresh() === false) {
            $this->loadDictFile();
        }
        $dict = require $userLocaleCacheFile;

        return $dict;
    }

    public function getDictText($userLocale, $name, $key, $default = '')
    {
        $dict = $this->getDict($userLocale);
        if (!isset($dict[$name][$key])) {
            return $default;
        }

        return (string) ($dict[$name][$key]);
    }

    public function getDictMap($userLocale, $name)
    {
        $dict = $this->getDict($userLocale);
        if (!isset($dict[$name])) {
            return array();
        }

        return (array) ($dict[$name]);
    }
}
