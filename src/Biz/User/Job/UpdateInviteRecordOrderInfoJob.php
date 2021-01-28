<?php

namespace Biz\User\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateInviteRecordOrderInfoJob extends AbstractJob
{
    public function execute()
    {
        $conditions = array();
        $count = $this->getInviteRecordService()->countRecords($conditions);
        $pageCount = 200;
        $page = ceil($count / $pageCount);
        for ($i = 0; $i < $page; ++$i) {
            $this->getInviteRecordService()->flushOrderInfo($conditions, $i * $pageCount, ($i + 1) * $pageCount);
        }
    }

    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    protected function getBiz()
    {
        return $this->biz;
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }
}
