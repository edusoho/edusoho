<?php

namespace Biz\Theme\Service;

interface ThemeService
{
    public function isAllowedConfig();

    public function createThemeConfig($name, $config);

    public function editThemeConfig($name, $config);

    public function getThemeConfigByName($name);

    public function getCurrentThemeConfig();

    public function saveCurrentThemeConfig($config);

    public function saveConfirmConfig();

    public function resetConfig();

    public function resetCurrentConfig();

    public function changeTheme($theme);
}
