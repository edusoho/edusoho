<?php

namespace Biz\Sms\Job;

use Biz\Crontab\Service\Job;
use Topxia\Service\Common\ServiceKernel;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Sms\SmsProcessor\SmsProcessorFactory;

class SmsSendOneHourJob implements Job
{
    public function execute($params)
    {
        $smsType = 'sms_live_play_one_hour';
        $dayIsOpen = $this->getSmsService()->isOpen($smsType);
        $parameters = array();

        if ($dayIsOpen) {
            $targetType = $params['targetType'];
            $targetId = $params['targetId'];
            $processor = SmsProcessorFactory::create($targetType);
            $return = $processor->getUrls($targetId, $smsType);
            $callbackUrls = $return['urls'];
            $count = ceil($return['count'] / 1000);
            try {
                $api = CloudAPIFactory::create('leaf');
                $result = $api->post('/sms/sendBatch', array('total' => $count, 'callbackUrls' => $callbackUrls));
                $this->getLogService()->info('sms', 'sms-sendbatch', 'callbackUrls', $callbackUrls);
                $this->getLogService()->info('sms', 'sms-sendbatch', 'result', empty($result) ? array() : $result);
            } catch (\RuntimeException $e) {
                throw new \RuntimeException($this->getKernel()->trans('发送失败！'));
            }
        }
    }

    protected function getLogService()
    {
        return $this->getKernel()->createService('System:LogService');
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
