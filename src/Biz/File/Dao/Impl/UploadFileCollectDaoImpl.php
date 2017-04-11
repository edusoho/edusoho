<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileCollectDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileCollectDaoImpl extends GeneralDaoImpl implements UploadFileCollectDao
{
    protected $table = 'upload_files_collection';

    public function getByUserIdAndFileId($userId, $fileId)
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'fileId' => $fileId,
        ));
    }

    public function findByUserIdAndFileIds($ids, $userId)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $parameters = array_merge($ids, array($userId));
        $sql = "SELECT * FROM {$this->table()} WHERE fileId IN ({$marks}) and userId = ? ";

        return $this->db()->fetchAll($sql, $parameters);
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE userId = ? ";

        return $this->db()->fetchAll($sql, array($userId));
    }

    public function declares()
    {
        return array(
        );
    }
}
