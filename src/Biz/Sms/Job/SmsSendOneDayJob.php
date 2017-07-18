<?php

namespace Biz\Sms\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Topxia\Service\Common\ServiceKernel;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;

class SmsSendOneDayJob extends AbstractJob
{
    public function execute()
    {
        $smsType = 'sms_live_play_one_day';
        $dayIsOpen = $this->getSmsService()->isOpen($smsType);

        if ($dayIsOpen) {
            $targetType = $this->args['targetType'];
            $targetId = $this->args['targetId'];
            $processor = SmsProcessorFactory::create($targetType);
            $return = $processor->getUrls($targetId, $smsType);
            $callbackUrls = $return['urls'];
            $count = ceil($return['count'] / 1000);
            try {
                $api = CloudAPIFactory::create('leaf');
                $result = $api->post('/sms/sendBatch', array('total' => $count, 'callbackUrls' => $callbackUrls));
            } catch (\RuntimeException $e) {
                throw new \RuntimeException('发送失败！');
            }
        }
    }

    protected function getSmsService()
    {
        return $this->getKernel()->createService('Sms:SmsService');
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
