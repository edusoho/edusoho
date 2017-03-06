<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashFlowDao extends GeneralDaoInterface
{
    public function getBySn($sn);

    public function getByOrderSn($orderSn);

    public function analysisAmount($conditions);

    public function findUserIdsByFlows($type, $createdTime, $orderBy, $start, $limit);

    public function countByTypeAndGTECreatedTime($type, $createdTime);
}
