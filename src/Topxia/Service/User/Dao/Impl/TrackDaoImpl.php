<?php

namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\TrackDao;

class TrackDaoImpl extends BaseDao implements TrackDao
{
    protected $table = 'user_track';

    public function addTrack($track)
    {
        $affected = $this->getConnection()->insert($this->table, $track);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert track error.');
        }
        return $this->getTrack($this->getConnection()->lastInsertId());
    }

    public function getTrack($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }
}