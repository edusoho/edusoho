<?php

namespace Tests\Unit\Card\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\Card\Service\CardService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\BaseTestCase;

class CardServiceTest extends BaseTestCase
{
    public function testAddCard()
    {
        $card = $this->generateCard();
        $results = $this->getCardService()->addCard($card);
        $this->assertEquals($card['cardId'], $results['cardId']);
        $this->assertEquals($card['cardType'], $results['cardType']);
        $this->assertEquals($card['deadline'], $results['deadline']);
        $this->assertEquals($card['userId'], $results['userId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testAddCardWithParamsException()
    {
        $card = $this->generateCard();
        unset($card['cardId']);
        $this->getCardService()->addCard($card);
    }

    public function testGetCardByCardId()
    {
        $card = $this->generateCard();
        $this->getCardService()->addCard($card);

        $results = $this->getCardService()->getCardByCardId($card['cardId']);

        $this->assertEquals($card['cardId'], $results['cardId']);
        $this->assertEquals($card['cardType'], $results['cardType']);
        $this->assertEquals($card['deadline'], $results['deadline']);
        $this->assertEquals($card['userId'], $results['userId']);
    }

    public function testGetCardByUserId()
    {
        $card = $this->generateCard();
        $this->getCardService()->addCard($card);

        $results = $this->getCardService()->getCardByUserId($card['userId']);
        $this->assertEquals($card['cardId'], $results['cardId']);
        $this->assertEquals($card['cardType'], $results['cardType']);
        $this->assertEquals($card['deadline'], $results['deadline']);
        $this->assertEquals($card['userId'], $results['userId']);
    }

    public function testGetCardByCardIdAndCardType()
    {
        $this->mockBiz(
            'Card:CardDao',
            array(
                array(
                    'functionName' => 'getByCardIdAndCardType',
                    'returnValue' => array('id' => 111, 'cardId' => 'cardId'),
                    'withParams' => array(1, 'a'),
                ),
            )
        );
        $result = $this->getCardService()->getCardByCardIdAndCardType(1, 'a');

        $this->assertEquals(array('id' => 111, 'cardId' => 'cardId'), $result);
    }

    public function testUpdateCardByCardIdAndCardType()
    {
        $card = $this->generateCard();
        $this->getCardService()->addCard($card);
        $timestamp = time();
        $results = $this->getCardService()->updateCardByCardIdAndCardType($card['cardId'], $card['cardType'], array(
            'deadline' => $timestamp,
        ));

        $this->assertEquals($timestamp, $results['deadline']);
    }

    public function testGetCard()
    {
        $card = $this->generateCard();
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

    public function testFindCardDetailByCardTypeAndCardId()
    {
        $this->mockBiz(
            'Coupon:CouponService',
            array(
                array(
                    'functionName' => 'getCoupon',
                    'returnValue' => array('id' => 111, 'code' => 'code'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getCardService()->findCardDetailByCardTypeAndCardId('coupon', 111);

        $this->assertEquals(array('id' => 111, 'code' => 'code'), $result);
    }

    public function testFindCardDetailsByCardTypeAndCardIds()
    {
        $this->mockBiz(
            'Coupon:CouponService',
            array(
                array(
                    'functionName' => 'getCouponsByIds',
                    'returnValue' => array(array('id' => 111, 'code' => 'code')),
                    'withParams' => array(array(111)),
                ),
            )
        );
        $result = $this->getCardService()->findCardDetailsByCardTypeAndCardIds('coupon', array(111));

        $this->assertEquals(array(array('id' => 111, 'code' => 'code')), $result);
    }

    /**
     * @expectedException \Biz\Card\CardException
     */
    public function testFindCardsByUserIdAndCardTypeEmptyCardType()
    {
        $user = $this->createUser();
        $card1 = $this->generateCard($user);
        $this->getCardService()->addCard($card1);
        $cardLists = $this->getCardService()->findCardsByUserIdAndCardType($user['id'], '');
    }

    public function testSearchCards()
    {
        $user = $this->createUser();
        $card1 = $this->generateCard($user);
        $this->getCardService()->addCard($card1);
        $card2 = $this->generateCard($user);
        $this->getCardService()->addCard($card2);
        $conditions = array(
            'userId' => $user['id'],
        );
        $orderBy = array('createdTime' => 'ASC');
        $result = $this->getCardService()->searchCards($conditions, $orderBy, 0, 20);
        $this->assertEquals(2, count($result));
    }

    public function testFindCardsByCardIds()
    {
        $time = time() + 86400;
        $user = $this->createUser();
        $card1 = array(
            'cardType' => 'moneyCard',
            'cardId' => 1,
            'userId' => $user['id'],
            'deadline' => $time,
        );
        $this->getCardService()->addCard($card1);
        $card2 = array(
            'cardType' => 'moneyCard',
            'cardId' => 2,
            'userId' => $user['id'],
            'deadline' => $time,
        );
        $this->getCardService()->addCard($card2);
        $ids = array(
            $card1['cardId'],
            $card2['cardId'],
        );

        $cardLists = $this->getCardService()->findCardsByCardIds($ids);
        $this->assertCount(2, $cardLists);
    }

    public function testSortArrayByField()
    {
        $arr = array(array('a' => 2), array('a' => 4), array('a' => 4));
        $result = $this->getCardService()->sortArrayByField($arr, 'a');

        $this->assertEquals(array(array('a' => 2), array('a' => 4), array('a' => 4)), $result);
    }

    public function testSortArrayByKey()
    {
        $arr = array(array('a' => 2), array('a' => 4), array('a' => 4));
        $result = $this->getCardService()->sortArrayByKey($arr, 'a');

        $this->assertEquals(array(array('a' => 2), array('a' => 4), array('a' => 4)), $result);
    }

    public function testFindCurrentUserAvailableCouponForTargetTypeAndTargetId()
    {
        $result = $this->getCardService()->findCurrentUserAvailableCouponForTargetTypeAndTargetId('course', 1);

        $this->assertEmpty($result);
    }

    public function testIsAvailable()
    {
        $result = ReflectionUtils::invokeMethod($this->getCardService(), 'isAvailable', array(array('status' => 'used'), 'all', 'fullDiscount'));
        $this->assertFalse($result);

        $result = ReflectionUtils::invokeMethod($this->getCardService(), 'isAvailable', array(array('status' => 'receive', 'deadline' => time() - 2 * 86400), 'all', 'fullDiscount'));
        $this->assertFalse($result);

        $result = ReflectionUtils::invokeMethod($this->getCardService(), 'isAvailable', array(array('status' => 'receive', 'deadline' => time(), 'targetType' => 'all'), 'all', 1));
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($this->getCardService(), 'isAvailable', array(array('status' => 'receive', 'deadline' => time(), 'targetType' => 'course', 'targetId' => 1), 'course', 1));
        $this->assertTrue($result);
    }

    public function testPrepareRecordConditions()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'searchUsers',
                    'returnValue' => array(array('id' => 1)),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($this->getCardService(), '_prepareRecordConditions', array(array(
            'nickname' => 'stu_1',
            'startDateTime' => 1561543220,
            'endDateTime' => 1561553220,
        )));

        $this->assertEquals(1561543220, $result['reciveStartTime']);
        $this->assertEquals(1561553220, $result['reciveEndTime']);
    }

    /**
     * @return CardService
     */
    protected function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function generateCard($currentUser = null)
    {
        $time = time() + 86400;
        $user = null == $currentUser ? $this->createUser() : $currentUser;

        return array(
            'cardType' => 'moneyCard',
            'cardId' => 1,
            'userId' => $user['id'],
            'deadline' => $time,
        );
    }

    private function createUser()
    {
        $user = array();
        $user['email'] = 'user@user.com';
        $user['nickname'] = 'user';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');

        return $user;
    }
}
