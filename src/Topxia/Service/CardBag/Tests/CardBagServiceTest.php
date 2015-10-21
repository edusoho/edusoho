<?php
namespace Topxia\Service\CardBag\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\UserService;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\System\SettingService;

class CardBagServiceTest extends BaseTestCase
{

    public function testAddCard()
    {   
        $this->createUser();
        $this->getSettingService()->set('coupon',array('enabled'=>'1'));
            $time = date("Y-m-d",time()+86400);
            $couponData = array(
            'name' => 'testCoupon', 
            'prefix' => 'testPrefix', 
            'type' => 'minus', 
            'rate' => '10.0', 
            'generatedNum' => 5, 
            'digits' => 8, 
            'deadline' => $time, 
            'targetType' => 'course'
        );
        $couponBanch = $this->getCouponService()->generateCoupon($couponData);
        $testCoupons = $this->getCouponService()->findCouponsByBatchId($couponBanch['id'],0,2);
        $card = array(
            'cardType' => 'moneyType',
            'cardId' => $testCoupons[1]['id']
        );
        $results = $this->getCardBagService()->addCard($card);
        $this->assertEquals($results['cardId'],$testCoupons[1]['id']);

        
    }


    protected function getCardBagService()
    {
        return $this->getServiceKernel()->createService('CardBag.CardBagService'); 
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    }

    protected function getSettingService()
    {

        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }


    private function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normal";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;
    }

}