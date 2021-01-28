<?php

namespace Biz\WeChat\Job;

use Biz\WeChat\Service\WeChatService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class WeChatUsersSync extends AbstractJob
{
    public function execute()
    {
        $this->refresh();
    }

    private function refresh($nextOpenId = '')
    {
        $result = $this->getWeChatService()->batchSyncOfficialWeChatUsers($nextOpenId);
        if (!empty($result['next_openid'])) {
            $this->refresh($result['next_openid']);
        }
    }

    /**
     * @return WeChatService
     */
    protected function getWeChatService()
    {
        return $this->biz->service('WeChat:WeChatService');
    }
}
