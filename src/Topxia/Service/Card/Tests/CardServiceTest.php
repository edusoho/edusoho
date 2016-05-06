<?php
namespace Topxia\Service\Card\Tests;

use Topxia\Service\Common\BaseTestCase;

class CardServiceTest extends BaseTestCase
{
    public function testAddCard()
    {
        $card    = $this->generateCard();
        $results = $this->getCardService()->addCard($card);
        $this->assertEquals($card['cardId'], $results['cardId']);
        $this->assertEquals($card['cardType'], $results['cardType']);
        $this->assertEquals($card['deadline'], $results['deadline']);
        $this->assertEquals($card['userId'], $results['userId']);

    }

    public function testGetCard()
    {
        $card    = $this->generateCard();
        $results = $this->getCardService()->addCard($card);
        $cardGet = $this->getCardService()->getCard($results['id']);
        $this->assertEquals($results['id'], $cardGet['id']);
        $this->assertEquals($results['cardId'], $cardGet['cardId']);
        $this->assertEquals($results['cardType'], $cardGet['cardType']);
        $this->assertEquals($results['deadline'], $cardGet['deadline']);
        $this->assertEquals($results['userId'], $cardGet['userId']);
    }

    public function testFindCardsByUserIdAndCardTypeOnce()
    {
        $user = $this->createUser();

        $card1 = $this->generateCard($user);
        $this->getCardService()->addCard($card1);
        $card2 = $this->generateCard($user);
        $this->getCardService()->addCard($card2);
        $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], 'moneyCard');
        $this->assertCount(2, $cardLists);

    }

    public function testFindCardsByUserIdAndCardTypeTwice()
    {
        $this->setExpectedException('Exception');
        $user  = $this->createUser();
        $card1 = $this->generateCard($user);
        $this->getCardService()->addCard($card1);
        $card2 = $this->generateCard($user);
        $this->getCardService()->addCard($card2);
        $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], '');
    }

    public function testSearchCards()
    {
        $user  = $this->createUser();
        $card1 = $this->generateCard($user);
        $this->getCardService()->addCard($card1);
        $card2 = $this->generateCard($user);
        $this->getCardService()->addCard($card2);
        $conditions = array(
            'userId' => $user['id']
        );
        $orderBy = array('createdTime', 'ASC');
        $result  = $this->getCardService()->searchCards($conditions, $orderBy, 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindCardsByCardIds()
    {
        $time  = time() + 86400;
        $user  = $this->createUser();
        $card1 = array(
            'cardType' => 'moneyCard',
            'cardId'   => 1,
            'userId'   => $user['id'],
            'deadline' => $time
        );
        $this->getCardService()->addCard($card1);
        $card2 = array(
            'cardType' => 'moneyCard',
            'cardId'   => 2,
            'userId'   => $user['id'],
            'deadline' => $time
        );
        $this->getCardService()->addCard($card2);
        $ids = array(
            $card1['cardId'],
            $card2['cardId']
        );

        $cardLists = $this->getCardService()->findCardsByCardIds($ids);
        $this->assertCount(2, $cardLists);
    }

    protected function getCardService()
    {
        return $this->getServiceKernel()->createService('Card.CardService');
    }

    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon.CouponService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function generateCard($currentUser = null)
    {
        $time = time() + 86400;
        $user = $currentUser == null ? $this->createUser() : $currentUser;
        return array(
            'cardType' => 'moneyCard',
            'cardId'   => 1,
            'userId'   => $user['id'],
            'deadline' => $time
        );
    }

    private function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user              = array();
        $user['email']     = "normal@user.com";
        $user['nickname']  = "normal";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER');
        return $user;
    }

}
