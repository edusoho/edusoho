<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileCollectDao extends GeneralDaoInterface
{
    public function findByUserIdAndFileIds($ids, $userId);

    public function getByUserIdAndFileId($userId, $fileId);

    public function findByUserId($userId);
}
