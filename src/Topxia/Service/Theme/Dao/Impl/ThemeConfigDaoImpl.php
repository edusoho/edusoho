<?php

namespace Topxia\Service\Theme\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Theme\Dao\ThemeConfigDao;

class ThemeConfigDaoImpl extends BaseDao implements ThemeConfigDao
{
    protected $table = 'theme_config';

    public $serializeFields = array(
        'config'        => 'json',
        'allConfig'     => 'json',
        'confirmConfig' => 'json'
    );

    public function getThemeConfig($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $themeConfig = $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
            return $themeConfig ? $that->createSerializer()->unserialize($themeConfig, $that->serializeFields) : null;
        }

        );
    }

    public function getThemeConfigByName($name)
    {
        $that = $this;

        return $this->fetchCached("name:{$name}", $name, function ($name) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE name = ? LIMIT 1";
            $themeConfig = $that->getConnection()->fetchAssoc($sql, array($name)) ?: null;

            return $themeConfig ? $that->createSerializer()->unserialize($themeConfig, $that->serializeFields) : null;
        }

        );
    }

    public function addThemeConfig($themeConfig)
    {
        $themeConfig = $this->createSerializer()->serialize($themeConfig, $this->serializeFields);
        $affected    = $this->getConnection()->insert($this->table, $themeConfig);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert themeConfig error.');
        }

        return $this->getThemeConfig($this->getConnection()->lastInsertId());
    }

    public function updateThemeConfigByName($name, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('name' => $name));
        $this->clearCached();
        return $this->getThemeConfigByName($name);
    }
}
