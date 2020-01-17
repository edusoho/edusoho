<?php

namespace Biz\QiQiuYun\Service\Impl;

use Biz\BaseService;
use QiQiuYun\SDK\Service\ESopService;
use QiQiuYun\SDK\Service\NotificationService;

class QiQiuYunSdkProxyServiceImpl extends BaseService
{
    public function pushEventTracking($action, $data = null)
    {
        try {
            $this->getESopService()->submitEventTracking(array(
                'action' => $action,
                'data' => $data,
            ));
        } catch (\Exception $e) {
            $this->getLogger()->error('pushEventTrackingError: '.$e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz['qiQiuYunSdk.notification'];
    }

    /**
     * @return ESopService
     */
    protected function getESopService()
    {
        return $this->biz['qiQiuYunSdk.esOp'];
    }
}
