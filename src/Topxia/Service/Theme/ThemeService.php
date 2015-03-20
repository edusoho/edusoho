<?php

namespace Topxia\Service\Theme;

interface ThemeService
{
    public function getCurrentThemeConfig();

    public function saveCurrentThemeConfig($config);

    public function saveConfirmConfig();
    
    public function resetConfig();
}