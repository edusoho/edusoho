<?php

namespace Biz\Xapi\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface StatementDao extends GeneralDaoInterface
{
    public function callbackStatusPushedAndPushedTimeByUuids(array $ids, $pushTime);

    public function retryStatusPushingToCreatedByCreatedTime($createdTime);
}
