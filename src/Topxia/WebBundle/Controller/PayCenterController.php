<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class PayCenterController extends BaseController
{

	public function showAction(Request $request)
	{
		$fields = $request->query->all();
		$order = $this->getOrderService()->getOrder($fields["id"]);

		return $this->render('TopxiaWebBundle:PayCenter:show.html.twig', array(
            'order' => $order,
            'payments' => $this->getEnabledPayments(),
            'returnUrl' => $fields["returnUrl"],
            'notifyUrl' => $fields["notifyUrl"],
            'showUrl' => $fields["showUrl"],
        ));
	}

	public function payAction(Request $request)
	{
		$fields = $request->request->all();
		$user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '用户未登录，支付失败。');
        }

		if(!array_key_exists("orderId", $fields)) {
			return $this->createMessageResponse('error', '缺少订单，支付失败');
		}

		$order = $this->getOrderService()->getOrder($fields["orderId"]);

		if($user["id"] != $order["userId"]) {
			return $this->createMessageResponse('error', '不是您创建的订单，支付失败');
		}

		if ($order['status'] == 'paid') {
            return $this->redirect($this->generateUrl('course_show', array('id' => $order['targetId'])));
        } else {

            $payRequestParams = array(
                'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
                'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
                'showUrl' => $this->generateUrl('course_show', array('id' => $order['targetId']), true),
            );

            return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
                'order' => $order,
                'requestParams' => $payRequestParams,
            ));
        }
	}

    public function payReturnAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayReturn($request, $name, function($success, $order) use(&$controller) {
            if (!$success) {
                $controller->generateUrl('course_show', array('id' => $order['targetId']));
            }

            $controller->getCourseOrderService()->doSuccessPayOrder($order['id']);

            return $controller->generateUrl('course_show', array('id' => $order['targetId']));
        });
    }

    public function payNotifyAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayNotify($request, $name, function($success, $order) use(&$controller) {
            if (!$success) {
                return ;
            }

            $controller->getCourseOrderService()->doSuccessPayOrder($order['id']);

            return ;
        });
    }

	private function getEnabledPayments()
    {
        $enableds = array();

        $setting = $this->setting('payment', array());

        if (empty($setting['enabled'])) {
            return $enableds;
        }

        $payNames = array('alipay');
        foreach ($payNames as $payName) {
            if (!empty($setting[$payName . '_enabled'])) {
                $enableds[$payName] = array(
                    'type' => empty($setting[$payName . '_type']) ? '' : $setting[$payName . '_type'],
                );
            }
        }

        return $enableds;
    }

	protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }


}