<?php

namespace Biz\Theme\Service\Impl;

use Biz\BaseService;
use Biz\Theme\Service\ThemeService;
use Topxia\Service\Common\ServiceKernel;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use AppBundle\System;

class ThemeServiceImpl extends BaseService implements ThemeService
{
    private $defaultConfig;
    private $allConfig;
    private $themeName;

    public function __construct()
    {
        parent::__construct(ServiceKernel::instance()->getBiz());
        $currentTheme = $this->getSettingService()->get('theme');

        try {
            $this->defaultConfig = $this->getKernel()->getParameter("theme_{$currentTheme['uri']}_default");
            $this->allConfig = $this->getKernel()->getParameter("theme_{$currentTheme['uri']}_all");

            if ($this->getKernel()->hasParameter("theme_{$currentTheme['uri']}_extend")) {
                $allConfigExtend = $this->getKernel()->getParameter("theme_{$currentTheme['uri']}_extend");
                $this->allConfig = array_merge_recursive($this->allConfig, $allConfigExtend["theme_{$currentTheme['uri']}_all"]);
            }

            $this->themeName = $this->getKernel()->getParameter("theme_{$currentTheme['uri']}_name");
        } catch (\Exception $e) {
            $this->setConfigAndNameByThemeConfig($currentTheme);
        }
    }

    public function isAllowedConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = '简墨';
        }

        if (empty($this->themeName) && empty($this->defaultConfig)) {
            return false;
        }

        if (in_array($currentTheme['name'], array($this->themeName))) {
            return true;
        }

        return false;
    }

    public function createThemeConfig($name, $config)
    {
        return $this->getThemeConfigDao()->create(array(
            'name' => $name,
            'config' => $config,
            'updatedTime' => time(),
            'createdTime' => time(),
            'updatedUserId' => $this->getCurrentUser()->id,
        ));
    }

    public function editThemeConfig($name, $config)
    {
        $config['updatedTime'] = time();
        $config['updatedUserId'] = $this->getCurrentUser()->id;

        return $this->getThemeConfigDao()->updateThemeConfigByName($name, $config);
    }

    public function getThemeConfigByName($name)
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

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = '简墨';
            }

            $config['name'] = $currentTheme['name'];
        }

        return $config;
    }

    public function getCurrentThemeConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = '简墨';
        }

        return $this->getThemeConfigByName($currentTheme['name']);
    }

    public function saveCurrentThemeConfig($config)
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = '简墨';
        }

        $currentTheme = $this->getThemeConfigDao()->getThemeConfigByName($currentTheme['name']);

        if (empty($currentTheme)) {
            $currentTheme = $this->getSettingService()->get('theme');

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = '简墨';
            }

            return $this->createThemeConfig($currentTheme['name'], $config);
        }

        return $this->editThemeConfig($currentTheme['name'], array(
            'config' => $config,
        ));
    }

    public function saveConfirmConfig()
    {
        $currentTheme = $this->getCurrentThemeConfig();

        return $this->editThemeConfig($currentTheme['name'], array(
            'confirmConfig' => $currentTheme['config'],
        ));
    }

    public function resetConfig()
    {
        return $this->saveCurrentThemeConfig($this->defaultConfig);
    }

    public function resetCurrentConfig()
    {
        $currentTheme = $this->getSettingService()->get('theme');

        if (!isset($currentTheme['name'])) {
            $currentTheme['name'] = '简墨';
        }

        $currentTheme = $this->getThemeConfigDao()->getThemeConfigByName($currentTheme['name']);

        if (empty($currentTheme)) {
            $currentTheme = $this->getSettingService()->get('theme');

            if (!isset($currentTheme['name'])) {
                $currentTheme['name'] = '简墨';
            }

            return $this->createThemeConfig($currentTheme['name'], $this->defaultConfig);
        }

        $config['config'] = empty($currentTheme['confirmConfig']) ? $this->defaultConfig : $currentTheme['confirmConfig'];

        return $this->editThemeConfig($currentTheme['name'], $config);
    }

    public function changeTheme($theme)
    {
        if (empty($theme)) {
            return false;
        }
        if (!$this->isThemeSupportEs($theme)) {
            return false;
        }

        $this->getSettingService()->set('theme', $theme);
        $pluginConfigurationManager = new PluginConfigurationManager($this->biz['kernel.root_dir']);

        $pluginConfigurationManager->setActiveThemeName($theme['code'])->save();

        return true;
    }

    private function isThemeSupportEs($theme)
    {
        if ($theme['protocol'] < 3 || version_compare(rtrim($theme['support_version'], '+'), System::VERSION, '>')) {
            return false;
        }

        return true;
    }

    private function setConfigAndNameByThemeConfig($currentTheme)
    {
        $rootDir = dirname($this->biz['kernel.root_dir']);
        $code = empty($currentTheme['code']) ? '' : $currentTheme['code'];
        $parameters = $rootDir."/web/themes/{$code}/config/parameter.json";
        if (!is_file($parameters)) {
            $this->defaultConfig = array();
            $this->allConfig = array();
            $this->themeName = null;
        } else {
            $parameters = file_get_contents($parameters);
            $parameters = json_decode($parameters, true);
            $this->defaultConfig = $parameters["theme_{$code}_default"];
            $this->allConfig = $parameters["theme_{$code}_all"];
            $this->themeName = $parameters["theme_{$code}_name"];
        }
    }

    protected function getThemeConfigDao()
    {
        return $this->createDao('Theme:ThemeConfigDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
