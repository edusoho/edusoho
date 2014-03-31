<?php
namespace Member\MemberBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MemberOrderController extends BaseController
{

    public function buyAction(Request $request)
    {
        $levels = $this->getLevelService()->findEnabledLevels();
        $selectedLevel = $request->query->get('level', 0);
        if (empty($selectedLevel) && !empty($levels)) {
            $selectedLevel = $levels[0]['id'];
        }

        return $this->render('MemberBundle:MemberOrder:buy.html.twig', array(
            'levels' => $this->makeLevelChoices($levels),
            'selectedLevel' => $selectedLevel,
            'prices' => $this->makeLevelPrices($levels),
        ));
    }

    public function payAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $orderData = $request->request->all();

        $order = array();
        $order['userId'] = $currentUser->id;

        $level = $this->getLevelService()->getLevel($orderData['level']);
        if (empty($level)) {
            return $this->createMessageResponse('error', '订单数据不正确。');
        }
        if (empty($level['enabled'])) {
            return $this->createMessageResponse('error', '该会员类型管理员已关闭，不能购买！');
        }

        $unitNames = array('month' => '个月', 'year' => '年');

        $order['title'] = "购买{$level['name']} x {$orderData['duration']}{$unitNames[$orderData['unit']]}";
        $order['targetType'] = 'member';
        $order['targetId'] = $level['id'];
        $order['payment'] = 'alipay';

        if ($orderData['unit'] == 'month') {
            $order['amount'] = $level['monthPrice'] * intval($orderData['duration']);
        } elseif ($orderData['unit'] == 'year') {
            $order['amount'] = $level['yearPrice'] * intval($orderData['duration']);
        } else {
            return $this->createMessageResponse('error', '订单数据（开通时长）不正确。');
        }

        $order['snPrefix'] = 'M';


        $order = $this->getOrderService()->createOrder($order);

        return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array('order' => $order));



    }



    private function makeLevelPrices($levels)
    {
        $prices = array();
        foreach ($levels as $level) {
            $prices[$level['id']] = array();
            $prices[$level['id']]['month'] = (float)$level['monthPrice'];
            $prices[$level['id']]['year'] = (float)$level['yearPrice'];
        }
        return $prices;
    }

    private function makeLevelChoices($levels)
    {
        $radios = array();
        foreach ($levels as $level) {
            $radios[$level['id']] = $level['name'];
        }
        return $radios;
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Member:Member.LevelService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

}