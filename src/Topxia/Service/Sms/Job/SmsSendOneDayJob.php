<?php
namespace Topxia\Service\Sms\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Sms\SmsProcessor\SmsProcessorFactory;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

class SmsSendOneDayJob implements Job
{
    public function execute($params)
    {
        $smsType = 'sms_live_play_one_day';
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
                    $result = $api->post("/sms/sendBatch", array('total' => $count, 'callbackUrls' => $callbackUrls));
                } catch (\RuntimeException $e) {
                    throw new \RuntimeException("发送失败！");
            }   
        }
    }

    protected function getSmsService()
    {
        return ServiceKernel::instance()->createService('Sms.SmsService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
