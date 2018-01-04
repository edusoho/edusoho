<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\TimeMachine;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\AccessDeniedException;

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

    protected function getDistributorUserService()
    {
        return $this->createService('Distributor:DistributorUserService');
    }
}
