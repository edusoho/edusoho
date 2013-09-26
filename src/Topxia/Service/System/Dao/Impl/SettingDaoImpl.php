<?php

namespace Topxia\Service\System\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\System\Dao\SettingDao;

class SettingDaoImpl extends BaseDao implements SettingDao
{
    protected $table = 'setting';

    public function getSetting($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function addSetting($setting)
    {
       $affected = $this->getConnection()->insert($this->table, $setting);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert setting error.');
        }
        return $this->getSetting($this->getConnection()->lastInsertId());
    }

    public function findAllSettings()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->getConnection()->fetchAll($sql, array());
    }

    public function deleteSettingByName($name)
    {
        return $this->getConnection()->delete($this->table, array('name' => $name));
    }
}