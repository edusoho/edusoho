<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Component\Payment\Payment;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayCenter extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['orderId'])) {
            throw new BadRequestHttpException('Missing params', null, ErrorCode::INVALID_ARGUMENT);
        }

        $trade = $this->getPayService()->getTradeByTradeSn($params['orderId']);

        $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($params['orderId']);

        if ($trade['status'] === 'paid') {
            $trade['paymentForm'] = array();
            $trade['paymentHtml'] = '';
        } else {
            $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($params['orderId']);
            $form = $this->makePayForm($platformCreatedResult);
            $trade['paymentForm'] = $form;
            $trade['paymentHtml'] = $this->renderView('pay-center/submit-pay-request.html.twig',
                array('form' => $form));
        }

        return $trade;
    }

    private function makePayForm($platformCreatedResult)
    {
        $form = array();
        $form['action'] = $platformCreatedResult['url'];
        $form['method'] = 'post';
        $form['params'] = array();

        return $form;
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->service('Pay:PayService');
    }
}