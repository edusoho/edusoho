<?php

namespace Biz\DestroyAccount\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DestroyAccountRecordDao extends GeneralDaoInterface
{
    public function getLastAuditDestroyAccountRecordByUserId($userId);
}
