<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;
use Biz\Util\Service\MobileDeviceService;
use Biz\System\Service\LogService;
use Topxia\MobileBundleV2\Controller\MobileBaseController;

class AppDevice extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (!ArrayToolkit::requireds($params, array('imei', 'platform'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if ($this->getMobileDeviceService()->addMobileDevice($params)) {
            $this->getLogService()->info(MobileBaseController::MOBILE_MODULE, 'regist_device', '注册客户端', $params);
        }

        return array('success' => true);
    }

    /**
     * @return MobileDeviceService
     */
    protected function getMobileDeviceService()
    {
        return $this->service('Util:MobileDeviceService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }
}
