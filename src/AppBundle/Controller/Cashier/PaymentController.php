<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpFoundation\Request;

abstract class PaymentController extends BaseController
{
    protected $deviceDetector;

    protected function isMicroMessenger()
    {
        $masterRequest = $this->container->get('request_stack')->getMasterRequest();

        return strpos($masterRequest->headers->get('User-Agent'), 'MicroMessenger') !== false;
    }

    /**
     * @return OrderFacadeService
     */
    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    abstract public function notifyAction(Request $request, $payment);
}
