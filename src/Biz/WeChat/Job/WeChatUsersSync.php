<?php

namespace Biz\WeChat\Job;

use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class WeChatUsersSync extends AbstractJob
{
    public function execute()
    {
        $this->getWeChatService()->batchSyncOfficialWeChatUsers();
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }
}
