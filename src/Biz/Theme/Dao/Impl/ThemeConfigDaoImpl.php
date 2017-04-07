<?php

namespace Biz\Theme\Dao\Impl;

use Biz\Theme\Dao\ThemeConfigDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ThemeConfigDaoImpl extends GeneralDaoImpl implements ThemeConfigDao
{
    protected $table = 'theme_config';

    public function getThemeConfigByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function updateThemeConfigByName($name, $fields)
    {
        $this->db()->update($this->table, $fields, array('name' => $name));

        return $this->getThemeConfigByName($name);
    }

    public function declares()
    {
        return array(
            'serializes' => array(
                'config' => 'json',
                'allConfig' => 'json',
                'confirmConfig' => 'json',
            ),
            'timestamps' => array(
                'createdTime',
                'updatedTime',
            ),
        );
    }
}
