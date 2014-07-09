<?php
namespace Topxia\Service\Theme\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Theme\ThemeService;

class ThemeServiceImpl extends BaseService implements ThemeService
{
    public function getThemeConfigByName($name)
    {
        return $this->getThemeConfigDao()->getThemeConfigByName($name);
    }

    public function getCurrentThemeConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');
        return $this->getThemeConfigByName($currentTheme['name']);
    }

    public function saveCurrentThemeConfig($config)
    {
        $currentTheme = $this->getCurrentThemeConfig();
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
        $currentTheme = $this->getCurrentThemeConfig();

        return $this->editThemeConfig($currentTheme['name'], array(
            'confirmConfig' => null,
            'config' => null
        ));
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
        return $this->createDao('Theme.ThemeConfigDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}