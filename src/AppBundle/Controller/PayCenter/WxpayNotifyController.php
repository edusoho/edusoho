<?php

namespace AppBundle\Controller\PayCenter;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WxpayNotifyController extends BaseController
{
    public function notifyAction(Request $request, $type)
    {
        $returnXml = $request->getContent();
        
        $result = $this->getPayService()->notifyPaid('wechat', $returnXml);

        return new Response($result);
    }

    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }
}
