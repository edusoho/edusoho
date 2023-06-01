<?php

namespace AppBundle\Controller;

use Biz\UnifiedPayment\Service\UnifiedPaymentService;
use Symfony\Component\HttpFoundation\Request;

class UnifiedPaymentController extends BaseController
{
    public function notifyAction(Request $request, $payment)
    {
        $result = $this->getUnifiedPaymentService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse($result);
    }

    /**
     * @return UnifiedPaymentService
     */
    protected function getUnifiedPaymentService()
    {
        return $this->createService('UnifiedPayment:UnifiedPaymentService');
    }
}
