<?php

namespace ESCloud\SDK\Service;

class AppPushService extends BaseService
{
    protected $host = 'test-push-service.edusoho.cn';

    protected $service = 'push';

    public function inspectTenant()
    {
        return $this->request('GET', '/v1/tenant/inspect');
    }

    public function enableTenant()
    {
        return $this->request('POST', '/v1/tenant/enable');
    }

    public function disableTenant()
    {
        return $this->request('POST', '/v1/tenant/disable');
    }

    public function bindDevice($params)
    {
        return $this->request('POST', '/v1/device/bind', $params);
    }

    public function unbindDevice($userId)
    {
        return $this->request('POST', '/v1/device/unbind', ['userId' => $userId]);
    }
}
