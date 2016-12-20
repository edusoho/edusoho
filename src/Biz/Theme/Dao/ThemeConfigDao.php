<?php

namespace Biz\Theme\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThemeConfigDao extends GeneralDaoInterface
{
    public function getThemeConfigByName($name);

    public function updateThemeConfigByName($name, $fields);
}
