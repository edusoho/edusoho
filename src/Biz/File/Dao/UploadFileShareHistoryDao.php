<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileShareHistoryDao extends GeneralDaoInterface
{
    public function findByUserId($userId);
}
