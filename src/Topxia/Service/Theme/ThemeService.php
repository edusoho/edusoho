<?php

namespace Topxia\Service\Theme;

interface ThemeService
{
    public function getThemeConfigByName($name);

    public function getCurrentThemeConfig();

    public function saveCurrentThemeConfig($config);

    public function saveConfirmConfig();
    
    public function resetConfig();
}