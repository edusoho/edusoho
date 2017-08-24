<?php

namespace Biz\CloudPlatform\QueueJob;

use Biz\CloudPlatform\Service\PushService;
use Codeages\Biz\Framework\Queue\AbstractJob;

class PushJob extends AbstractJob
{
    public function execute()
    {
        $context = $this->getBody();
        $from = $context['from'];
        $to = $context['to'];
        $body = $context['body'];

        $this->getPushService()->push($from, $to, $body);
    }

    /**
     * @return PushService
     */
    protected function getPushService()
    {
        return $this->biz->service('CloudPlatform:PushService');
    }
}
