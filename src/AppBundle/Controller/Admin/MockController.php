<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\TimeMachine;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Distributor\Util\DistributorJobStatus;
use Biz\Distributor\Job\DistributorSyncJob;

class MockController extends BaseController
{
    public function indexAction()
    {
        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }
        $tokenExpireDateStr = TimeMachine::expressionToStr('+2 day');

        return $this->render('admin/mock/index.html.twig', array(
            'couponExpireDateStr' => 1,
            'tokenExpireDateStr' => $tokenExpireDateStr,
        ));
    }

    public function mockDistributorTokenAction(Request $request)
    {
        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }

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
        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $jobData = $service->findJobData();

        return $this->createJsonResponse($jobData);
    }

    public function postDataAction(Request $request)
    {
        if (!$this->getCurrentUser()->isSuperAdmin()) {
            throw new AccessDeniedException();
        }

        $type = $request->request->get('type');
        $service = $this->getDistributorService($type);
        $drpService = $service->getDrpService();

        if (!empty($drpService)) {
            $job = new DistributorSyncJob(array(), $this->getBiz());
            $result = $job->sendData($drpService, $service);
            if (DistributorJobStatus::$FINISHED == $result['status']) {
                return $this->createJsonResponse(array('result' => 'true'));
            } else {
                return $this->createJsonResponse(array('result' => $result['result']->getBody()));
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
}
