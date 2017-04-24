<?php

namespace Biz\Announcement\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AnnouncementDao extends GeneralDaoInterface
{
    public function deleteByTargetIdAndTargetType($targetId, $targetType);
}
