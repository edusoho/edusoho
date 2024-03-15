<?php

namespace Biz\QiQiuYun\Service\Impl;

use Biz\BaseService;
use Biz\QiQiuYun\Service\QiQiuYunSdkProxyService;
use QiQiuYun\SDK\Service\ESopService;
use QiQiuYun\SDK\Service\NotificationService;

class QiQiuYunSdkProxyServiceImpl extends BaseService implements QiQiuYunSdkProxyService
{
    public function pushEventTracking($action, $data = null)
    {
        try {
            $this->getESopService()->submitEventTracking([
                'action' => $action,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $this->getLogger()->error('pushEventTrackingError: '.$e->getMessage(), $e->getTrace());
        }
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->biz['ESCloudSdk.notification'];
    }

    /**
     * @return ESopService
     */
    protected function getESopService()
    {
        return $this->biz['qiQiuYunSdk.esOp'];
    }
}
