<?php

namespace AppBundle\Common;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\Exception\InvalidArgumentException;

/**
 * Class ExtensionManager.
 *
 * @deprecated since version 7.0 to be removed in 8.0, use codeages_plugin.dict_twig_extension twig extension
 */
class ExtensionManager
{
    protected $kernel;

    protected $bundles;

    protected $booted;

    protected $statusTemplates;

    protected $dataDict;

    protected $dataTagClassmap;

    protected $dataTags;

    private static $_instance;

    private function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->bundles = array(
            'DataTag' => array(),
            'StatusTemplate' => array(),
            'DataDict' => array(),
            'NotificationTemplate' => array(),
        );
        $this->booted = false;
        $this->statusTemplates = array();
        $this->dataDict = array();
        $this->dataTagClassmap = array();
        $this->dataTags = array();
        $this->notificationTemplates = array();
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
            throw new InvalidArgumentException(self::getServiceKernel()->trans('ExtensionManager尚未实例化。'));
        }

        return self::$_instance;
    }

    /**
     * @param $status array(
     *  'type' => 'task_start', // src/AppBundle/Extensions/StatusTemplate/{type}.tpl.html.twig
     * )
     * @param mode  分为 simple 和 full
     */
    public function renderStatus($status, $mode)
    {
        // src/AppBundle/Extensions/StatusTemplate/*.tpl.html.twig
        $this->loadTemplates('statusTemplates');

        if (!isset($this->statusTemplates[$status['type']])) {
            return self::getServiceKernel()->trans('无法显示该动态。');
        }

        return $this->kernel->getContainer()->get('templating')->render(
            $this->statusTemplates[$status['type']],
            array('status' => $status, 'mode' => $mode)
        );
    }

    public function getDataDict($type)
    {
        return $this->kernel->getContainer()->get('codeages_plugin.dict_twig_extension')->getDict($type);
    }

    /**
     * @name {name}DataTag.php 中的 {name}, 如 OrgDataTag 的 name为 Org
     */
    public function getDataTag($name)
    {
        if (isset($this->dataTags[$name])) {
            return $this->dataTags[$name];
        }

        $this->loadDataTagClassmap();

        if (!isset($this->dataTagClassmap[$name])) {
            throw new InvalidArgumentException(self::getServiceKernel()->trans('数据标签`%name%`尚未定义。', array('%name%' => $name)));
        }

        $class = $this->dataTagClassmap[$name];

        $this->dataTags[$name] = new $class();

        return $this->dataTags[$name];
    }

    /**
     * @param $notification array(
     *  'type' => 'global', // src/AppBundle/Extensions/NotificationTemplate/{type}.tpl.html.twig
     * )
     */
    public function renderNotification($notification)
    {
        $this->loadTemplates('notificationTemplates');

        if (!isset($this->notificationTemplates[$notification['type']])) {
            return ' <li class="media">
                      <div class="pull-left">
                        <span class="glyphicon glyphicon-volume-down media-object"></span>
                      </div>
                      <div class="media-body">
                        <div class="notification-body">
                            无法显示该通知。
                        </div>
                      </div>
                    </li>';
        }

        return $this->kernel->getContainer()->get('templating')->render(
            $this->notificationTemplates[$notification['type']],
            array('notification' => $notification)
        );
    }

    private function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->getExtensionalBundles();
    }

    private function loadDataTagClassmap()
    {
        $this->boot();

        if (!empty($this->dataTagClassmap)) {
            return $this->dataTagClassmap;
        }

        $finder = new Finder();
        $finder->files()->name('*DataTag.php')->depth('== 0');

        $dirNamespaces = array();

        foreach ($this->bundles['DataTag'] as $bundle) {
            $directory = $bundle->getPath().'/Extensions/DataTag';

            if (!is_dir($directory)) {
                continue;
            }

            $dirNamespaces[$directory] = $bundle->getNamespace().'\\Extensions\\DataTag';

            $finder->in($directory);
        }
        foreach ($finder as $file) {
            $name = $file->getBasename('DataTag.php');
            $this->dataTagClassmap[$name] = $dirNamespaces[$file->getPath()]."\\{$name}DataTag";
        }

        return $this->dataTagClassmap;
    }

    private function loadDataDict()
    {
        $this->boot();

        if (!empty($this->dataDict)) {
            return $this->dataDict;
        }

        $files = array();

        foreach ($this->bundles['DataDict'] as $bundle) {
            $file = $bundle->getPath().'/Resources/config/dict.yml';

            if (!file_exists($file)) {
                continue;
            }

            $this->dataDict = array_merge($this->dataDict, Yaml::parse(file_get_contents($file)));
        }

        return $this->dataDict;
    }

    private function loadTemplates($type)
    {
        $this->boot();

        if (!empty($this->$type)) {
            return $this->$type;
        }

        $finder = new Finder();
        $finder->files()->name('*.tpl.html.twig')->depth('== 0');

        $root = realpath($this->kernel->getContainer()->getParameter('kernel.root_dir').'/../');
        $bundleName = substr(ucwords($type), 0, strlen(ucwords($type)) - 1);

        foreach ($this->bundles[$bundleName] as $bundle) {
            $directory = $bundle->getPath().'/Extensions/'.$bundleName;

            if (!is_dir($directory)) {
                continue;
            }

            $finder->in($directory);
        }

        $tempName = array();

        foreach ($finder as $file) {
            $template = $file->getBasename('.tpl.html.twig');
            $path = str_replace($root, '@root', $file->getRealPath());
            $tempName[$template] = $path;
        }

        if ('statusTemplates' == $type) {
            $this->statusTemplates = $tempName;
        } elseif ('notificationTemplates' == $type) {
            $this->notificationTemplates = $tempName;
        }

        return $tempName;
    }

    private function getExtensionalBundles()
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!method_exists($bundle, 'getEnabledExtensions')) {
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

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
