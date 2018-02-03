<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\TimeMachine;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Distributor\Job\DistributorSyncJob;
use AppBundle\Common\ReflectionUtils;

class MockController extends BaseController
{
    public function indexAction()
    {
        $this->validate();
        $tokenExpireDateStr = TimeMachine::expressionToStr('+2 day');

        return $this->render('admin/mock/index.html.twig', array(
            'couponExpireDateStr' => 1,
            'tokenExpireDateStr' => $tokenExpireDateStr,
        ));
    }

    public function mockDistributorTokenAction(Request $request)
    {
        $this->validate();

        $data = array(
            'merchant_id' => '123',
            'agency_id' => '22221',
            'coupon_price' => $request->request->get('couponPrice'),
            'coupon_expiry_day' => $request->request->get('couponExpiryDay'),
        );

        $tokenExpireDateNum = strtotime($request->request->get('tokenExpireDateStr'));
        $token = $this->getDistributorUserService()->encodeToken($data, $tokenExpireDateNum);

        return $this->createJsonResponse(array(
            'token' => $token,
        ));
    }

    public function getPostDataAction(Request $request)
    {
        $this->validate();

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $jobData = $service->findJobData();

        return $this->createJsonResponse($jobData);
    }

    public function postDataAction(Request $request)
    {
        $this->validate();

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $drpService = $service->getDrpService();

        if (!empty($drpService)) {
            $job = new DistributorSyncJob(array(), $this->getBiz());
            $result = ReflectionUtils::invokeMethod($job, 'sendData', array($drpService, $service));
            if (DistributorJobStatus::FINISHED == $result['status']) {
                return $this->createJsonResponse(array('result' => 'true'));
            } else {
                return $this->createJsonResponse(array('result' => $result['result']));
            }
        }
    }

    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }

    protected function getDistributorService($type)
    {
        return $this->createService("Distributor:Distributor{$type}Service");
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    private function validate()
    {
        $storage = $this->getSettingService()->get('storage', array());
        if (empty($storage['cloud_access_key'])) {
            throw new AccessDeniedException('未设置教育云授权码！！！');
        }

        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }
    }
}
