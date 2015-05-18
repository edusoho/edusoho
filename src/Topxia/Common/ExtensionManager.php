<?php

namespace Topxia\Common;

use Symfony\Component\Finder\Finder;
use Topxia\Common\ExtensionalBundle;

class ExtensionManager
{
    protected $kernel;

    protected $bundles;

    protected $booted;

    protected $statusTemplates;

    private static $_instance;

    private function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->bundles = array(
            'DataTag' => array(),
            'StatusTemplate' => array(),
            'DataDict' => array(),
        );
        $this->booted = false;
        $this->statusTemplates = array();
    }

    public static function init($kernel)
    {
        if (self::$_instance) {
            return self::$_instance;
        }

        self::$_instance = new self($kernel);

        return self::$_instance;
    }

    public static function instance()
    {
        if (empty(self::$_instance)) {
            throw new \RuntimeException('ExtensionManager尚未实例化。');
        }
        return self::$_instance;
    }

    public function renderStatus($status, $mode)
    {
        $this->boot();
        $this->loadStatusTemplates();

        if (!isset($this->statusTemplates[$status['type']])) {
            return '无法显示该动态。';
        }

        return $this->kernel->getContainer()->get('templating')->render(
            $this->statusTemplates[$status['type']],
            array('status' => $status, 'mode' => $mode)
        );
    }

    private function boot()
    {
        if ($this->booted) {
            return ;
        }

        $this->getExtensionalBundles();
    }

    private function loadStatusTemplates()
    {
        if (!empty($this->statusTemplates)) {
            return $this->statusTemplates;
        }

        $finder = new Finder();
        $finder->files()->name('*.tpl.html.twig')->depth('== 0');

        $root = realpath($this->kernel->getContainer()->getParameter('kernel.root_dir') . '/../');

        foreach($this->bundles['StatusTemplate'] as $bundle) {
            $directory = $bundle->getPath() . '/Extensions/StatusTemplate';
            if (!is_dir($directory)) {
                continue;
            }

            $finder->in($directory);
        }

        foreach ($finder as $file) {
            $type = $file->getBasename('.tpl.html.twig');
            $path = str_replace($root, '@root', $file->getRealPath());
            $this->statusTemplates[$type] = $path;
        }

        return $this->statusTemplates;
    }

    private function getExtensionalBundles()
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!($bundle instanceof ExtensionalBundle)) {
                continue;
            }

            $enableds = $bundle->getEnabledExtensions();
            foreach (array_keys($this->bundles) as $enabled) {
                if (!in_array($enabled, $enableds)) {
                    continue;
                }
                $this->bundles[$enabled][] = $bundle;
            }
        }

        return $this->bundles;
    }

}