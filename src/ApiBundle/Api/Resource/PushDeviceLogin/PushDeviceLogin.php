<?php

namespace ApiBundle\Api\Resource\PushDeviceLogin;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;

class PushDeviceLogin extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $user = $this->getCurrentUser();
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, array('provider', 'provider_reg_id', 'device_token', 'os', 'os_version', 'model'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $device = $this->getPushDeviceService()->getPushSdk()->registerDevice($params);

        $localDevice = $this->getPushDeviceService()->getPushDeviceByRegId($device['reg_id']);
        if (!empty($user['id'])) {
            $this->logoutPushDevice($user['id']);
        }

        if (!empty($localDevice)) {
            $pushDevice = $this->getPushDeviceService()->updatePushDevice($localDevice['id'], array('userId' => $user['id']));
            $this->getPushDeviceService()->getPushSdk()->setDeviceActive($pushDevice['regId'], 1);
        } else {
            $pushDevice = $this->getPushDeviceService()->createPushDevice(array('userId' => $user['id'], 'regId' => $device['reg_id']));
        }

        return $pushDevice;
    }

    protected function logoutPushDevice($userId)
    {
        $devices = $this->getPushDeviceService()->findPushDevicesByUserId($userId);
        foreach ($devices as $device) {
            $this->getPushDeviceService()->updatePushDevice($device['id'], array('userId' => 0));
            $this->getPushDeviceService()->getPushSdk()->setDeviceActive($device['regId'], 0);
        }
    }

    /**
     * @return \Biz\PushDevice\Service\Impl\PushDeviceServiceImpl
     */
    protected function getPushDeviceService()
    {
        return $this->service('PushDevice:PushDeviceService');
    }
}
