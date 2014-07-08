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
        if (empty($currentTheme['config'])) {
            return $this->createThemeConfig($currentTheme['name'], $config);
        }
        return $this->editThemeConfig($currentTheme['name'], $config);
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
        return $this->getThemeConfigDao()->updateThemeConfigByName($name, array(
            'config' => $config,
            'updatedTime' => time(),
            'updatedUserId' => $this->getCurrentUser()->id
        ));
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