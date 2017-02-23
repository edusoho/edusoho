<?php


namespace Biz\Task\Dao;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ViewLogDao extends GeneralDaoInterface
{
    public function searchViewLogsGroupByTime($conditions, $startTime, $endTime);
}