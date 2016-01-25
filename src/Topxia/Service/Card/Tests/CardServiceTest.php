<?php
namespace Topxia\Service\Card\Tests;

use Topxia\Service\Common\BaseTestCase;

// use Topxia\Common\ArrayToolkit;
// use Topxia\Service\User\UserService;
// use Topxia\Service\User\CurrentUser;
// use Topxia\Service\System\SettingService;

class CardServiceTest extends BaseTestCase
{
    public function testAddCard()
    {
    }

    // public function testAddCard()
    // {
    //     $user = $this->createUser();
    //     $testCoupons = $this->generateCoupons();
    //     $card = array(
    //         'cardType' => 'moneyCard',
    //         'cardId' => $testCoupons[1]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[1]['deadline']
    //     );
    //     $results = $this->getCardService()->addCard($card);
    //     $this->assertEquals($results['cardId'],$testCoupons[1]['id']);

    // }

    // public function testGetCard()
    // {
    //     $user = $this->createUser();
    //     $testCoupons = $this->generateCoupons();
    //     $card = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[1]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[1]['deadline']
    //     );
    //     $results = $this->getCardService()->addCard($card);
    //     $cardGet = $this->getCardService()->getCard($results['id']);
    //     $this->assertEquals($results['id'],$cardGet['id']);
    //     $this->assertEquals($results['cardId'],$testCoupons[1]['id']);

    // }

    // public function testFindCardsByUserIdAndCardTypeOnce()
    // {
    //     $user = $this->createUser();
    //     $testCoupons = $this->generateCoupons();

    //     $card1 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[1]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[1]['deadline']
    //     );
    //     $this->getCardService()->addCard($card1);
    //     $card2 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[2]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[2]['deadline']
    //     );
    //     $this->getCardService()->addCard($card2);
    //     $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'],'coupon');
    //     $this->assertCount(2,$cardLists);

    // }

    // public function testFindCardsByUserIdAndCardTypeTwice()
    // {
    //     $this->setExpectedException('Exception');
    //     $user = $this->createUser();
    //     $testCoupons = $this->generateCoupons();

    //     $card1 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[1]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[1]['deadline']
    //     );
    //     $this->getCardService()->addCard($card1);
    //     $card2 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[2]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[2]['deadline']
    //     );
    //     $this->getCardService()->addCard($card2);
    //     $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'],'');
    // }

    // public function testFindCardDetailsByCardTypeAndCardIds()
    // {
    //     // $this->setExpectedException('Exception');
    //     $user = $this->createUser();
    //     $testCoupons = $this->generateCoupons();

    //     $card1 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[1]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[1]['deadline']
    //     );
    //     $cardAdd1 = $this->getCardService()->addCard($card1);
    //     $card2 = array(
    //         'cardType' => 'coupon',
    //         'cardId' => $testCoupons[2]['id'],
    //         'userId' => $user['id'],
    //         'deadline' => $testCoupons[2]['deadline']
    //     );
    //     $cardAdd2 = $this->getCardService()->addCard($card2);
    //     $ids = array($cardAdd1['cardId'],$cardAdd2['cardId']);
    //     $results = $this->getCardService()->findCardDetailsByCardTypeAndCardIds('coupon',$ids);
    //     if($results[0]['id'] == $card1['cardId']){
    //         $this->assertEquals($results[0]['deadline'],$card1['deadline']);
    //     }else{
    //         $this->assertEquals($results[0]['deadline'],$card1['deadline']);
    //     }
    //     $this->assertCount(2,$results);
    // }

    // protected function getCardService()
    // {
    //     return $this->getServiceKernel()->createService('Card.CardService');
    // }

    // protected function getCouponService()
    // {
    //     return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    // }

    // protected function getSettingService()
    // {

    //     return $this->getServiceKernel()->createService('System.SettingService');
    // }

    // protected function getUserService()
    // {
    //     return $this->getServiceKernel()->createService('User.UserService');
    // }

    // protected function generateCoupons()
    // {
    //     $this->getSettingService()->set('coupon',array('enabled'=>'1'));
    //     $time = date("Y-m-d",time()+86400);
    //     $couponData = array(
    //         'name' => 'testCoupon',
    //         'prefix' => 'testPrefix',
    //         'type' => 'minus',
    //         'rate' => '10.0',
    //         'generatedNum' => 5,
    //         'digits' => 8,
    //         'deadline' => $time,
    //         'targetType' => 'course',
    //         'token' => 'EGEwhmrDmLEq3JJEoOqs8nyY0JCc4BGJ'
    //     );
    //     $couponBanch = $this->getCouponService()->generateCoupon($couponData);
    //     $testCoupons = $this->getCouponService()->findCouponsByBatchId($couponBanch['id'],0,2);
    //     return $testCoupons;
    // }

    // private function createUser()
    // {
    //     $user = array();
    //     $user['email'] = "user@user.com";
    //     $user['nickname'] = "user";
    //     $user['password'] = "user";
    //     $user =  $this->getUserService()->register($user);
    //     $user['currentIp'] = '127.0.0.1';
    //     $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
    //     return $user;

    // }

    // private function createNormalUser()
    // {
    //     $user = array();
    //     $user['email'] = "normal@user.com";
    //     $user['nickname'] = "normal";
    //     $user['password'] = "user";
    //     $user =  $this->getUserService()->register($user);
    //     $user['currentIp'] = '127.0.0.1';
    //     $user['roles'] = array('ROLE_USER');
    //     return $user;
    // }

}
