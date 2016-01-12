<?php
namespace Mooc\Service\Theme\Impl;

use Topxia\Service\Theme\ThemeServiceImpl as BaseThemeServiceImpl;

class ThemeServiceImpl extends BaseThemeServiceImpl
{
    public function isAllowedConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = "慕课";
        }

        if (empty($this->themeName) && empty($this->defaultConfig)) {
            return false;
        }

        if (in_array($currentTheme['name'], array($this->themeName))) {
            return true;
        }

        return false;
    }

    protected function getThemeConfigByName($name)
    {
        $config              = $this->getThemeConfigDao()->getThemeConfigByName($name);
        $config['allConfig'] = $this->allConfig;

        if (empty($config['config'])) {
            $config['config'] = $this->defaultConfig;
        }

        if (empty($config['confirmConfig'])) {
            $config['confirmConfig'] = $this->defaultConfig;
        }

        if (empty($config['name'])) {
            $currentTheme = $this->getSettingService()->get('theme');

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = "慕课";
            }

            $config['name'] = $currentTheme['name'];
        }

        return $config;
    }

    public function getCurrentThemeConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = "慕课";
        }

        return $this->getThemeConfigByName($currentTheme['name']);
    }

    public function saveCurrentThemeConfig($config)
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = "慕课";
        }

        $currentTheme = $this->getThemeConfigDao()->getThemeConfigByName($currentTheme['name']);

        if (empty($currentTheme)) {
            $currentTheme = $this->getSettingService()->get('theme');

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = "慕课";
            }

            return $this->createThemeConfig($currentTheme['name'], $config);
        }

        return $this->editThemeConfig($currentTheme['name'], array(
            'config' => $config
        ));
    }

    public function resetConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = "慕课";
        }

        return $this->saveCurrentThemeConfig($this->defaultConfig);
    }

    public function resetCurrentConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = "慕课";
        }

        $currentTheme = $this->getThemeConfigDao()->getThemeConfigByName($currentTheme['name']);

        if (empty($currentTheme)) {
            $currentTheme = $this->getSettingService()->get('theme');

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = "慕课";
            }

            return $this->createThemeConfig($currentTheme['name'], $this->defaultConfig);
        }

        $config['config'] = empty($currentTheme['confirmConfig']) ? $this->defaultConfig : $currentTheme['confirmConfig'];
        return $this->editThemeConfig($currentTheme['name'], $config);
    }
}
