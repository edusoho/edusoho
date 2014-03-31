<?php
namespace Member\MemberBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\WebBundle\Controller\OrderController;

class MemberOrderController extends OrderController
{

    public function buyAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getMemberService()->getMemberByUserId($currentUser->id);
        if ($member) {
            return $this->redirect($this->generateUrl('member_renew'));
        }

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

    public function renewAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getMemberService()->getMemberByUserId($currentUser->id);
        if (empty($member)) {
            return $this->redirect($this->generateUrl('member_buy'));
        }

        $level = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($level) or empty($level['enabled'])) {
            return $this->createMessageResponse('info', '该会员类型已经关闭，不能续费');
        }

        return $this->render('MemberBundle:MemberOrder:renew.html.twig', array(
            'member' => $member,
            'level' => $level,
            'prices' => $this->makeLevelPrices(array($level)),
        ));
    }


    public function payAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $orderData = $request->request->all();

        if (!ArrayToolkit::requireds($orderData, array('type', 'level', 'unit', 'duration'))) {
            return $this->createMessageResponse('error', '订单数据缺失，创建会员订单失败。');
        }

        if (!in_array($orderData['type'], array('new', 'renew'))) {
            return $this->createMessageResponse('error', '购买类型不正确，创建会员订单失败。');
        }

        $orderData['duration'] = intval($orderData['duration']);
        if (empty($orderData['duration'])) {
            return $this->createMessageResponse('error', '会员开通时长不正确，创建会员订单失败。');
        }

        $orderData = ArrayToolkit::parts($orderData, array('type', 'level', 'unit', 'duration'));
        if (!in_array($orderData['unit'], array('month', 'year'))) {
            return $this->createMessageResponse('error', '付费方式不正确，创建会员订单失败。');
        }

        $orderData['level'] = intval($orderData['level']);
        $level = $this->getLevelService()->getLevel($orderData['level']);
        if (empty($level)) {
            return $this->createMessageResponse('error', '会员等级不存在，创建会员订单失败。');
        }
        if (empty($level['enabled'])) {
            return $this->createMessageResponse('error', '会员类型已关闭，创建会员订单失败。');
        }

        $order = array();
        $unitNames = array('month' => '个月', 'year' => '年');

        $order['userId'] = $currentUser->id;
        $order['title'] = ($orderData['type'] == 'renew' ? '续费' : '购买') .  "{$level['name']} x {$orderData['duration']}{$unitNames[$orderData['unit']]}";
        $order['targetType'] = 'member';
        $order['targetId'] = $level['id'];
        $order['payment'] = 'alipay';
        $order['amount'] = $level[$orderData['unit'] . 'Price'] * $orderData['duration'];
        $order['snPrefix'] = 'M';
        $order['data'] = $orderData;

        $payRequestParams = array(
            'returnUrl' => $this->generateUrl('member_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('member_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('member', array(), true),
        );

        $order = $this->getOrderService()->createOrder($order);

        return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
            'order' => $order,
            'requestParams' => $payRequestParams,
        ));

    }

    public function payReturnAction(Request $request, $name)
    {
        $controller = $this;
        return $this->doPayReturn($request, $name, function($order) use ($controller) {
            if ($order['data']['type'] == 'new') {
                $controller->getMemberService()->becomeMember(
                    $order['userId'],
                    $order['data']['level'],
                    $order['data']['duration'], 
                    $order['data']['unit'], 
                    $order['id']
                );
            } elseif ($order['data']['type'] == 'renew') {
                $controller->getMemberService()->renewMember(
                    $order['userId'],
                    $order['data']['duration'], 
                    $order['data']['unit'], 
                    $order['id']
                );
            }
        }, null);
    }

    public function payNotifyAction(Request $request, $name)
    {
        return $this->doPayNotify($request, $name, function($order) {

        });
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

    public function getMemberService()
    {
        return $this->getServiceKernel()->createService('Member:Member.MemberService');
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