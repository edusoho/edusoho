<?php

namespace Biz\Certificate\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RecordDao extends GeneralDaoInterface
{
    public function findExpiredRecords($certificateId);
}
