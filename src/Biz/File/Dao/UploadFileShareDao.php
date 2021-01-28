<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileShareDao extends GeneralDaoInterface
{
    public function findByTargetUserIdAndIsActive($targetUserId, $active = 1);

    public function findByUserId($sourceUserId);

    public function findBySourceUserIdAndTargetUserId($sourceUserId, $targetUserId);

    public function findActiveByUserId($sourceUserId);
}
