<?php

namespace Topxia\Service\Theme\Dao\Impl;


use Topxia\Service\Common\BaseDao;
use GracefulTheme\Service\GracefulTheme\Dao\ThemeConfigDao;
use Topxia\Common\DaoException;
use PDO;

class ThemeConfigDaoImpl extends BaseDao implements ThemeConfigDao
{
    protected $table = 'theme_config';

    private $serializeFields = array(
            'config' => 'json',
            'allConfig' => 'json',
            'confirmConfig' => 'json',
    );

    public function getThemeConfig($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $themeConfig = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $themeConfig ? $this->createSerializer()->unserialize($themeConfig, $this->serializeFields) : null;
    }

    public function getThemeConfigByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        $themeConfig = $this->getConnection()->fetchAssoc($sql, array($name)) ? : null;

        return $themeConfig ? $this->createSerializer()->unserialize($themeConfig, $this->serializeFields) : null;
    }

    public function addThemeConfig($themeConfig)
    {
        $themeConfig = $this->createSerializer()->serialize($themeConfig, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $themeConfig);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert themeConfig error.');
        }
        return $this->getThemeConfig($this->getConnection()->lastInsertId());
    }

    public function updateThemeConfigByName($name, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('name' => $name));
        return $this->getThemeConfigByName($name);
    }

}