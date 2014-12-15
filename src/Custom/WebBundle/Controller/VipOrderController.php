<?php
namespace Custom\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;
use Topxia\WebBundle\Controller\OrderController;

class VipOrderController extends OrderController
{

    public function buyAction(Request $request)
    {
        
        if (!$this->setting('vip.enabled')) {
            return $this->createMessageResponse('info', '会员专区已关闭');
        }

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
        $buyType = $request->query->get('buyType');
        if(empty($buyType)){
            $buyType = $this->setting('vip.buy_type');
        }
        if (empty($selectedLevel) && !empty($levels)) {
            $selectedLevel = $levels[0]['id'];
        }
        
        return $this->render('VipBundle:VipOrder:buy.html.twig', array(
            'levels' => $this->makeLevelChoices($levels),
            'selectedLevel' => $selectedLevel,
            'prices' => $this->makeLevelPrices($levels),
            'payments' => $this->getEnabledPayments(),
            'isAdmin' => $currentUser->isAdmin(),
            'defaultBuyMonth' => $this->setting('vip.default_buy_months'),
            'buyType' => $buyType
        ));
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

    public function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    public function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    public function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

}