<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\SettingDao;

class SettingDaoImpl extends BaseDao implements SettingDao
{
    protected $table = 'setting';

    public function getSetting($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id));
        }

        );
    }

    public function addSetting($setting)
    {
        $affected = $this->getConnection()->insert($this->table, $setting);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert setting error.');
        }

        return $this->getSetting($this->getConnection()->lastInsertId());
    }

    public function findAllSettings()
    {
        $that = $this;

        return $this->fetchCached("all", function () use ($that) {
            $sql = "SELECT * FROM {$that->getTable()}";
            return $that->getConnection()->fetchAll($sql, array());
        }

        );
    }

    public function deleteSettingByName($name)
    {
        $result = $this->getConnection()->delete($this->table, array('name' => $name));
        $this->clearCached();
        return $result;
    }
    public function deleteByNamespaceAndName($namespace,$name)
    {
        $result = $this->getConnection()->delete($this->table, array('namespace'=>$namespace,'name' => $name));
        $this->clearCached();
        return $result;
    }
}
