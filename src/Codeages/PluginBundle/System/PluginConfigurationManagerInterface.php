<?php


namespace Codeages\PluginBundle\System;


use Symfony\Component\HttpKernel\Bundle\Bundle;

interface PluginConfigurationManagerInterface
{
    /**
     * @return string
     */
    public function getActiveThemeName();

    /**
     * @return string
     */
    public function getActiveThemeDirectory();

    /**
     * @param string $name theme name
     *
     * @return PluginConfigurationManagerInterface
     */
    public function setActiveThemeName($name);

    /**
     * @return array plugins
     */
    public function getInstalledPlugins();

    /**
     * @param $plugins array
     *
     * @return PluginConfigurationManagerInterface
     */
    public function setInstalledPlugins($plugins);

    /**
     * @return Bundle[]
     */
    public function getInstalledPluginBundles();
}