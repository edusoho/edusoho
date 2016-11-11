<?php

namespace Topxia\Service\Subtitle\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Subtitle\Dao\SubtitleDao;

class SubtitleDaoImpl extends BaseDao implements SubtitleDao
{
    protected $table = 'subtitle';

    public function findSubtitlesByMediaId($mediaId)
    {
        $sql = "SELECT * FROM {$this->table} where mediaId=?";
        return $this->getConnection()->fetchAll($sql, array($mediaId));
    }

    public function getSubtitle($id)
    {
        $sql = "SELECT * from {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function addSubtitle($subtitle)
    {
        $affected = $this->getConnection()->insert($this->table, $subtitle);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Subtitle error.');
        }

        return $this->getSubtitle($this->getConnection()->lastInsertId());
    }

    public function deleteSubtitle($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

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
