<?php

namespace Topxia\Service\Theme\Dao;

interface ThemeConfigDao
{
    public function getThemeConfig($id);

    public function getThemeConfigByName($name);

    public function addThemeConfig($themeConfig);

    public function updateThemeConfigByName($name, $fields);
}