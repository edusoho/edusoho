<?php

namespace Biz\BehaviorVerification\Service\Impl;

use Biz\BaseService;
use Biz\BehaviorVerification\Service\BehaviorVerificationBlackIpService;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\System\Service\LogService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class BehaviorVerificationServiceImpl extends BaseService implements BehaviorVerificationService
{

    public function behaviorVerification($request)
    {
        if ($request->isXmlHttpRequest()){
            $encryptedPoint = $request->request->get('encryptedPoint');
            $coordinate = $this->getBehaviorVerificationCoordinateService()->decryptCoordinate($encryptedPoint);
            $clientIp = $request->getClientIp();
            if ($this->getBehaviorVerificationBlackIpService()->isInBlackIpList($clientIp)) {
                $this->getLogger()->info("坐标：$coordinate ，IP: $clientIp 在IP黑名单中，请求被拦截。");
                $this->getBehaviorVerificationCoordinateService()->isRobot($coordinate);
                return true;
            }

            if ($this->getBehaviorVerificationCoordinateService()->isRobot($coordinate)) {
                $this->getBehaviorVerificationBlackIpService()->addBlackIpList($clientIp);
                $this->getLogService()->info('behavior_verification', 'add_black_list', "$coordinate 坐标异常, $clientIp 被加入黑名单。");
                return true;
            }
        }
        $this->getLogger()->info("坐标：$coordinate ，IP: $clientIp 正在执行请求。");
        return false;
    }

    /**
     * @return BehaviorVerificationCoordinateService
     */
    protected function getBehaviorVerificationCoordinateService()
    {
        return $this->createService('BehaviorVerification:BehaviorVerificationCoordinateService');
    }

    /**
     * @return BehaviorVerificationBlackIpService
     */
    protected function getBehaviorVerificationBlackIpService()
    {
        return $this->createService('BehaviorVerification:BehaviorVerificationBlackIpService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getLogger()
    {
        $logger = new Logger('BehaviorVerification.INFO');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/c.log', Logger::INFO));
        return $logger;
    }
}