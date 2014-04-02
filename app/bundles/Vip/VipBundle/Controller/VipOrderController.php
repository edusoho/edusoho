<?php
namespace Vip\VipBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\WebBundle\Controller\OrderController;

class VipOrderController extends OrderController
{

    public function buyAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if ($member) {
            return $this->redirect($this->generateUrl('vip_renew'));
        }

        $levels = $this->getLevelService()->findEnabledLevels();
        $selectedLevel = $request->query->get('level', 0);
        if (empty($selectedLevel) && !empty($levels)) {
            $selectedLevel = $levels[0]['id'];
        }

        return $this->render('VipBundle:VipOrder:buy.html.twig', array(
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

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if (empty($member)) {
            return $this->redirect($this->generateUrl('vip_buy'));
        }

        $level = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($level) or empty($level['enabled'])) {
            return $this->createMessageResponse('info', '该会员类型已经关闭，不能续费');
        }

        return $this->render('VipBundle:VipOrder:renew.html.twig', array(
            'member' => $member,
            'level' => $level,
            'prices' => $this->makeLevelPrices(array($level)),
        ));
    }

    public function upgradeAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }

        $member = $this->getVipService()->getMemberByUserId($currentUser->id);
        if (empty($member)) {
            return $this->redirect($this->generateUrl('vip_buy'));
        }

        $level = $this->getLevelService()->getLevel($member['levelId']);
        if (empty($level)) {
            return $this->createMessageResponse('error', '该会员类型不存在，不能升级！');
        }

        $levels = $this->getLevelService()->findNextEnabledLevels($level['id']);
        if (empty($levels)) {
            return $this->createMessageResponse('info', '没有可升级的会员等级。');
        }

        return $this->render('VipBundle:VipOrder:upgrade.html.twig', array(
            'member' => $member,
            'level' => $level,
            'prices' => $this->makeLevelPrices(array($level)),
            'levels' => $levels
        ));
    }

    public function upgradeAmountAction(Request $request)
    {

        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $newLevelId = $request->query->get('levelId');

        $amount = $this->getVipService()->calUpgradeMemberAmount($currentUser->id, $newLevelId);

        return $this->createJsonResponse($amount);
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
        $order['snPrefix'] = 'V';
        $order['data'] = $orderData;

        $payRequestParams = array(
            'returnUrl' => $this->generateUrl('vip_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('vip_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('vip', array(), true),
        );

        $order = $this->getOrderService()->createOrder($order);

        return $this->forward('TopxiaWebBundle:Order:submitPayRequest', array(
            'order' => $order,
            'requestParams' => $payRequestParams,
        ));

    }

    public function upgradePayAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        if (empty($currentUser)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $orderData = $request->request->all();
        if (empty($orderData['level'])) {
            return $this->createMessageResponse('error', '订单数据缺失，创建会员升级订单失败。');
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
        $order['title'] = "升级会员到 {$level['name']}";
        $order['targetType'] = 'vip';
        $order['targetId'] = $level['id'];
        $order['payment'] = 'alipay';
        $order['amount'] = $this->getVipService()->calUpgradeMemberAmount($currentUser->id, $level['id']);
        $order['snPrefix'] = 'M';
        $order['data'] = array('type' => 'upgrade', 'level' => $level['id']);

        $payRequestParams = array(
            'returnUrl' => $this->generateUrl('vip_pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('vip_pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('vip', array(), true),
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
                $controller->getVipService()->becomeMember(
                    $order['userId'],
                    $order['data']['level'],
                    $order['data']['duration'], 
                    $order['data']['unit'], 
                    $order['id']
                );
            } elseif ($order['data']['type'] == 'renew') {
                $controller->getVipService()->renewMember(
                    $order['userId'],
                    $order['data']['duration'], 
                    $order['data']['unit'], 
                    $order['id']
                );
            } elseif ($order['data']['type'] == 'upgrade') {
                $controller->getVipService()->upgradeMember(
                    $order['userId'],
                    $order['data']['level'], 
                    $order['id']
                );
            }

            $controller->generateUrl('vip');
        });
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
        $choices = array();
        foreach ($levels as $level) {
            $choices[$level['id']] = $level['name'];
        }
        return $choices;
    }

    public function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

}