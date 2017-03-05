<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileShareHistoryDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileShareHistoryDaoImpl extends GeneralDaoImpl implements UploadFileShareHistoryDao
{
    protected $table = 'upload_files_share_history';

    public function findByUserId($sourceUserId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE sourceUserId = ? ORDER BY createdTime DESC;";

        return $this->db()->fetchAll($sql, array($sourceUserId)) ?: array();
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'sourceUserId = :sourceUserId',
                'targetUserId = :targetUserId',
                'id = :id',
                'isActive = :isActive',
            ),
            'orderbys' => array(
                'createdTime',
            ),
        );
    }
}
