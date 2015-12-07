<?php

namespace Topxia\Service\Theme;

interface ThemeService
{
    public function getCurrentThemeConfig();

    public function getCurrentThemeConfirmConfig();

    public function saveCurrentThemeConfig($config);

    public function saveConfirmConfig();

    public function resetConfig();

    public function isAllowedConfig();

    public function isAllowedGracefulConfig();

    public function resetCurrentConfig();
}
