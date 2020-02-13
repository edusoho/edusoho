<?php

namespace Biz\DestroyAccount\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface DestroyAccountRecordDao extends GeneralDaoInterface
{
    public function getLastDestroyAccountRecordByUserId($userId);

    public function getLastAuditDestroyAccountRecordByUserId($userId);
}
