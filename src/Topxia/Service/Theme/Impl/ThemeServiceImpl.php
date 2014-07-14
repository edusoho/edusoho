<?php
namespace Topxia\Service\Theme\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Theme\ThemeService;

class ThemeServiceImpl extends BaseService implements ThemeService
{
    private $defaultConfig;
    private $allConfig;
    private $themeName;
    public function __construct()  {
        $currentTheme = $this->getSettingService()->get('theme');

        try {
            $this->defaultConfig =  $this->getKernel()->getParameter("theme_{$currentTheme["uri"]}_default");
            $this->allConfig =  $this->getKernel()->getParameter("theme_{$currentTheme["uri"]}_all");
            $this->themeName =  $this->getKernel()->getParameter("theme_{$currentTheme["uri"]}_name");
        } catch (\Exception $e) {
            $this->defaultConfig = array();
            $this->allConfig = array();
            $this->themeName = null;
        }
    }
    public function isAllowedConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');
        if(empty($this->themeName) && empty($this->defaultConfig)){
            return false;
        }
        if (in_array($currentTheme['name'], array($this->themeName))) {
            return true;
        }
        return false;
    }

    private  function getThemeConfigByName($name)
    {
        $config = $this->getThemeConfigDao()->getThemeConfigByName($name);
        $config['allConfig'] = $this->allConfig;

        if (empty($config['config'])) {
            $config['config'] = $this->defaultConfig;
        }

        if (empty($config['confirmConfig'])) {
            $config['confirmConfig'] = $this->defaultConfig;
        }

        if (empty($config['name'])) {
            $currentTheme = $this->getSettingService()->get('theme');
            $config['name'] = $currentTheme['name'];
        }

        return $config;
    }

    public function getCurrentThemeConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');
        return $this->getThemeConfigByName($currentTheme['name']);
    }

    public function getCurrentThemeConfirmConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');
        return $this->getThemeConfigByName($currentTheme['name']);
    }

    public function saveCurrentThemeConfig($config)
    {
        $currentTheme = $this->getSettingService()->get('theme');
        $currentTheme = $this->getThemeConfigDao()->getThemeConfigByName($currentTheme['name']);

        if (empty($currentTheme)) {
            $currentTheme = $this->getSettingService()->get('theme');
            return $this->createThemeConfig($currentTheme['name'], $config);
        }
        return $this->editThemeConfig($currentTheme['name'], array(
            'config' => $config
        ));
    }

    public function saveConfirmConfig()
    {
        $currentTheme = $this->getCurrentThemeConfig();

        return $this->editThemeConfig($currentTheme['name'], array(
            'confirmConfig' => $currentTheme['config']
        ));
    }
    
    public function resetConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');
        return $this->saveCurrentThemeConfig($this->defaultConfig);
    }

    private function createThemeConfig($name, $config)
    {
        return $this->getThemeConfigDao()->addThemeConfig(array(
            'name' => $name,
            'config' => $config,
            'updatedTime' => time(),
            'createdTime' => time(),
            'updatedUserId' => $this->getCurrentUser()->id
        ));
    }

    private function editThemeConfig($name, $config)
    {
        $config['updatedTime'] = time();
        $config['updatedUserId'] = $this->getCurrentUser()->id;
        return $this->getThemeConfigDao()->updateThemeConfigByName($name, $config);
    }


    protected function getThemeConfigDao()
    {
        return $this->createDao('GracefulTheme:GracefulTheme.ThemeConfigDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}