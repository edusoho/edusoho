<?php
namespace Topxia\Service\MoneyCard\Tests;

use Topxia\Service\Common\BaseTestCase;

class MoneyCardServiceTest extends BaseTestCase
{
    /**
     * @batchName 学习卡批次名称
     * @money 充值的金额
     * @coin 学习卡充值可获得的虚拟币数量
     * @cardPrefix 学习卡前缀
     * @cardLength 学习卡位数，必须在6-32位之间
     * @number 生成数量，必须在1-1000之间
     * @note  批次说明，用于说明本批次学习卡
     * @deadline  有效时间
     * @passwordLength 密码长度
     */
    public function testCreateMoneyCard()
    {
        $deadtime      = date('Y-m-d', strtotime('+1 day'));
        $moneyCardData = array(
            'batchName'      => 'testbatchname',
            'note'           => 'testnote',
            'coin'           => 100,
            'number'         => 10,
            'cardPrefix'     => 'testPrefix',
            'cardLength'     => 10,
            'passwordLength' => 16,
            'deadline'       => $deadtime
        );
        $batch = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
        $this->assertEquals('testPrefix', $batch['cardPrefix']);
        $this->assertEquals('testbatchname', $batch['batchName']);
        $this->assertEquals('testnote', $batch['note']);
        $this->assertEquals('100', $batch['coin']);
        $this->assertEquals('10', $batch['number']);
        $this->assertEquals('10', $batch['cardLength']);
        $this->assertEquals($deadtime, $batch['deadline']);
    }

    public function testGetBatch()
    {
        $MoneyCard1 = $this->createMoneyCard('MoneyCard1');
        $batch      = $this->getMoneyCardService()->getBatch($MoneyCard1['id']);
        $this->assertEquals('MoneyCard1', $batch['batchName']);

    }

    public function testSearchBatchs()
    {
        $MoneyCard1 = $this->createMoneyCard('MoneyCard1');
        $MoneyCard2 = $this->createMoneyCard('MoneyCard2');
        $conditions = array(
            'batchName' => 'MoneyCard1'
        );
        $orderBy = array('createdTime', 'ASC');
        $result  = $this->getMoneyCardService()->searchBatchs($conditions, $orderBy, 0, 20);
        $this->assertEquals(1, count($result));
    }

    public function testSearchBatchsCount()
    {
        $MoneyCard1 = $this->createMoneyCard('MoneyCard1');
        $MoneyCard2 = $this->createMoneyCard('MoneyCard2');
        $conditions = array(
            'batchName' => 'MoneyCard1'
        );
        $orderBy = array('createdTime', 'ASC');
        $count   = $this->getMoneyCardService()->searchBatchsCount($conditions, $orderBy, 0, 20);
        $this->assertEquals(1, $count);
    }

    public function testLockBatch()
    {
        $MoneyCard = $this->createMoneyCard('MoneyCard');
        $lock      = $this->getMoneyCardService()->lockBatch($MoneyCard['id']);
        $this->assertEquals("invalid", $lock['batchStatus']);
        $this->assertEquals($MoneyCard['id'], $lock['id']);
    }

    public function testUnlockBatch()
    {
        $MoneyCard = $this->createMoneyCard("MoneyCard");
        $lock      = $this->getMoneyCardService()->lockBatch($MoneyCard['id']);
        $unlock    = $this->getMoneyCardService()->unlockBatch($lock['id']);
        $this->assertEquals("normal", $unlock['batchStatus']);
        $this->assertEquals("invalid", $lock['batchStatus']);
        $this->assertEquals($MoneyCard['id'], $lock['id']);
        $this->assertEquals($MoneyCard['id'], $unlock['id']);
        $this->assertEquals($lock['id'], $unlock['id']);
    }

    public function testDeleteBatch()
    {
        $MoneyCard = $this->createMoneyCard('MoneyCard');
        $this->getMoneyCardService()->deleteBatch($MoneyCard['id']);
        $batch = $this->getMoneyCardService()->getBatch($MoneyCard['id']);
        $this->assertNull($batch);
    }

    public function testGetMoneyCard()
    {
        $MoneyCard = array('id' => null);
        $cardData  = $this->getMoneyCardService()->getMoneyCard($MoneyCard['id']);
        $this->assertNull($cardData);
    }

    public function testGetMoneyCardByIds()
    {
        $ids       = array();
        $cardsData = $this->getMoneyCardService()->getMoneyCardByIds($ids);
        $this->assertEquals(0, sizeof($cardsData));
    }

    public function testSearchMoneyCards()
    {
        $batch      = $this->createMoneyCard('moneycard');
        $conditions = array(
            'batchId' => $batch['id']
        );
        $orderBy = array('id', 'DESC');
        $cards   = $this->getMoneyCardService()->searchMoneyCards($conditions, $orderBy, 0, 20);
        $this->assertEquals($batch['id'], $cards[0]['batchId']);
    }

    public function testSearchMoneyCardsCount()
    {
        $conditions = array(
            'batchId' => -1
        );
        $count = $this->getMoneyCardService()->searchMoneyCardsCount($conditions);
        $this->assertEquals(0, $count);
    }

    public function testLockMoneyCard()
    {
        $batch      = $this->createMoneyCard('batch');
        $conditions = array(
            'batchId' => $batch['id']
        );
        $orderBy = array('id', 'DESC');
        $cards   = $this->getMoneyCardService()->searchMoneyCards($conditions, $orderBy, 0, 20);
        $lock    = $this->getMoneyCardService()->lockMoneyCard($cards[0]['id']);
        $this->assertEquals('invalid', $lock['cardStatus']);
    }

    public function testUnlockMoneyCard()
    {
        $batch      = $this->createMoneyCard('batch');
        $conditions = array(
            'batchId' => $batch['id']
        );
        $orderBy = array('id', 'DESC');
        $cards   = $this->getMoneyCardService()->searchMoneyCards($conditions, $orderBy, 0, 20);
        $lock    = $this->getMoneyCardService()->lockMoneyCard($cards[0]['id']);
        $unlock  = $this->getMoneyCardService()->unlockMoneyCard($lock['id']);
        $this->assertEquals($lock['id'], $unlock['id']);
        $this->assertEquals('normal', $unlock['cardStatus']);
        $this->assertEquals('invalid', $lock['cardStatus']);
    }

    public function testUpdateBatch()
    {
        $batch       = $this->createMoneyCard('batch');
        $fields      = array('note' => 'note');
        $updateBatch = $this->getMoneyCardService()->updateBatch($batch['id'], $fields);
        $this->assertEquals($batch['id'], $updateBatch['id']);
        $this->assertEquals('note', $updateBatch['note']);
        $this->assertEquals('batchnote', $batch['note']);
    }

    public function testUpdateMoneyCard()
    {
        $batch      = $this->createMoneyCard('batch');
        $conditions = array(
            'batchId' => $batch['id']
        );
        $orderBy    = array('id', 'DESC');
        $cards      = $this->getMoneyCardService()->searchMoneyCards($conditions, $orderBy, 0, 20);
        $fields     = array('cardStatus' => 'invalid');
        $updateCard = $this->getMoneyCardService()->updateMoneyCard($cards[0]['id'], $fields);
        $this->assertEquals($updateCard['id'], $cards[0]['id']);
        $this->assertEquals('invalid', $updateCard['cardStatus']);
        $this->assertEquals('normal', $cards[0]['cardStatus']);
    }

    public function testUseMoneyCard()
    {
        $batch      = $this->createMoneyCard('batch');
        $conditions = array(
            'batchId' => $batch['id']
        );
        $orderBy = array('id', 'DESC');
        $cards   = $this->getMoneyCardService()->searchMoneyCards($conditions, $orderBy, 0, 20);
        $fields  = array(
            'cardStatus'     => 'recharged',
            'rechargeTime'   => time(),
            'rechargeUserId' => 1
        );
        $useCard = $this->getMoneyCardService()->useMoneyCard($cards[0]['id'], $fields);
        $this->assertEquals('recharged', $useCard['cardStatus']);
        $this->assertEquals('1', $useCard['rechargeUserId']);
        $this->assertEquals($cards[0]['id'], $useCard['id']);

    }

    public function testReceiveMoneyCard()
    {
        $batch   = $this->createMoneyCard('batch');
        $user    = $this->createUser();
        $receive = $this->getMoneyCardService()->receiveMoneyCard($batch['token'], $user['id']);
        $this->assertEquals('success', $receive['code']);
    }

    protected function createMoneyCard($MoneyCard)
    {
        $deadtime      = date('Y-m-d', strtotime('+1 day'));
        $moneyCardData = array(
            'batchName'      => "{$MoneyCard}",
            'note'           => $MoneyCard.'note',
            'coin'           => 100,
            'number'         => 1,
            'cardPrefix'     => $MoneyCard.'Prefix',
            'cardLength'     => 10,
            'passwordLength' => 16,
            'deadline'       => $deadtime
        );
        return $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    }

    protected function createUser()
    {
        $User             = array();
        $User['email']    = 'User@User.com';
        $User['nickname'] = 'User';
        $User['password'] = 'User';
        return $this->getUserService()->register($User);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getMoneyCardService()
    {
        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }
}
