<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Codeages\Biz\Pay\Service\PayService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayCenter extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['orderId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        //可能传过来的是 已经支付的order id， 不是tradeSn
        if ($this->getOrderFacadeService()->isOrderPaid($params['orderId'])) {
            return array(
                'id' => $params['orderId'],
                'status' => 'paid',
                'trade_sn' => '',
                'paymentForm' => array(),
                'paymentHtml' => '',
            );
        }

        $trade = $this->getPayService()->getTradeByTradeSn($params['orderId']);

        if ($trade['status'] === 'paid') {
            $trade['paymentForm'] = array();
            $trade['paymentHtml'] = '';
            $trade['paymentUrl'] = '';
        } else {
            if ($trade['platform'] == 'wechat' && $trade['platform_type'] == 'Mweb') {
                $trade['paymentForm'] = array();
                $trade['paymentHtml'] = '';
                $trade['paymentUrl'] = $this->generateUrl('cashier_wechat_mweb_app_trade', array('tradeSn' => $params['orderId']), UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($params['orderId']);
                $form = $this->makePayForm($platformCreatedResult);
                $trade['paymentForm'] = $form;
                $trade['paymentHtml'] = $this->renderView('ApiBundle:cashier:submit-pay.html.twig',
                    array('form' => $form));
                $trade['paymentUrl'] = '';
            }
        }

        return $trade;
    }

    private function makePayForm($platformCreatedResult)
    {
        $form = array();
        $urlParts = explode('?', $platformCreatedResult['url']);
        $form['action'] = $urlParts[0].'?_input_charset=UTF-8';
        $form['method'] = 'post';
        $form['params'] = $platformCreatedResult['data'];

        return $form;
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->service('Pay:PayService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->service('OrderFacade:OrderFacadeService');
    }
}
