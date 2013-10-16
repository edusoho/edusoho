<?php

namespace Topxia\Service\Util\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Util\Dao\MediaParseDao;

class MediaParseDaoImpl extends BaseDao implements MediaParseDao
{
    protected $table = 'media_parse';

    public function getMediaParse($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findMediaParseByUuid($uuid)
    {
        $sql = "SELECT * FROM {$this->table} WHERE uuid = ?";
        return $this->getConnection()->fetchAssoc($sql, array($uuid));
    }

    public function findMediaParseByHash($hash)
    {

        $sql = "SELECT * FROM {$this->table} WHERE hash = ?";
        return $this->getConnection()->fetchAssoc($sql, array($hash));
    }

    public function addMediaParse(array $fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert MediaParse error.');
        }
        return $this->getMediaParse($this->getConnection()->lastInsertId());
    }

    public function updateMediaParse($id, array $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getMediaParse($id);
    }

}