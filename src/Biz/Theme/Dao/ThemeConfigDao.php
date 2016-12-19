<?php

namespace Biz\Theme\Dao;

interface ThemeConfigDao
{
    public function getThemeConfigByName($name);

    public function updateThemeConfigByName($name, $fields);
}
