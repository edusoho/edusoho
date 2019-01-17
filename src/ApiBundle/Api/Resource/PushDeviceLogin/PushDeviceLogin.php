<?php

namespace ApiBundle\Api\Resource\PushDeviceLogin;

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

        $device = $this->getPushDeviceService()->getPushSdk()->registerDevices($params);

        $localDevice = $this->getPushDeviceService()->getPushDeviceByRegId($device['reg_id']);
        if (!empty($localDevice)) {
            return $this->getPushDeviceService()->updatePushDevice($localDevice['id'], array('userId' => $user['id']));
        } else {
            return $this->getPushDeviceService()->createPushDevice(array('userId' => $user['id'], 'regId' => $device['reg_id']));
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
