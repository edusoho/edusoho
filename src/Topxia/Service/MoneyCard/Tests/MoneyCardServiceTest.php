<?php
namespace Topxia\Service\MoneyCard\Tests;

use Topxia\Service\Common\BaseTestCase;

class MoneyCardServiceTest extends BaseTestCase
{   

    public function testCreateMoneyCardWithAllRight()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$this->assertGreaterThan(0, $createdMoneyCard['id']);
    }

    /**
	* @expectedException Topxia\Service\Common\ServiceException
	* @expectedExceptionMessage    ERROR! Money Value Less Than Zero!
	*/
    public function testCreateMoneyCardWithMoneyLessThanZero()
    {
    	$moneyCardData = array(
    		'money'=>-1,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$this->getMoneyCardService()->createMoneyCard($moneyCardData);
    }

    /**
	* @expectedException Topxia\Service\Common\ServiceException
	* @expectedExceptionMessage    ERROR! CardLength Less Than Zero!
	*/
    public function testCreateMoneyCardWithCardLengthLessThanZero()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>-1,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$this->getMoneyCardService()->createMoneyCard($moneyCardData);
    }

    /**
	* @expectedException Topxia\Service\Common\ServiceException
	* @expectedExceptionMessage    ERROR! Card Number Less Than Zero!
	*/
    public function testCreateMoneyCardWithNumberLessThanZero()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>8,
    		'number'=>-1,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$this->getMoneyCardService()->createMoneyCard($moneyCardData);
    }

    public function testGetMoneyCard()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertGreaterThan(0, $getedMoneyCard['id']);
    }

    public function testsSearchBatchs()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$searchedBatchs = $this->getMoneyCardService()->searchBatchs(array(),array('id', 'DESC'),0,30);
    	foreach ($searchedBatchs as $key => $value) {
    		$this->assertEquals('vip', $value['cardPrefix']);
    		$this->assertEquals(10, $value['cardLength']);
    		$this->assertEquals(5, $value['number']);
    		$this->assertEquals(0, $value['rechargedNumber']);
    		$this->assertEquals(100, $value['money']);
    		$this->assertEquals('MyNote', $value['note']);
    	}
    }

    public function testsSearchBatchsCount()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$searchedBatchsCount = $this->getMoneyCardService()->searchBatchsCount(array());
    	$this->assertEquals(1, $searchedBatchsCount);
    }

    public function testSearchMoneyCards()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$searchedMoneyCards = $this->getMoneyCardService()->searchMoneyCards(
    		array('batchId'=>$getedMoneyCard['batchId']),
    		array('id', 'DESC'),
    		0,30);

    	foreach ($searchedMoneyCards as $key => $value) {
    		$this->assertEquals(0,$value['rechargeUserId']);
    		$this->assertEquals(0,$value['rechargeTime']);
    		$this->assertEquals('normal',$value['cardStatus']);
    		$this->assertEquals($getedMoneyCard['batchId'], $value['batchId']);
    	}

    	$this->assertEquals(5, count($searchedMoneyCards));
    }

    public function testsearchMoneyCardsCount()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$searchedMoneyCardsCount = $this->getMoneyCardService()->searchMoneyCardsCount(
    		array('batchId'=>$getedMoneyCard['batchId']));
    	$this->assertEquals(5,$searchedMoneyCardsCount);
    }

    public function testLockMoneyCard()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$this->getMoneyCardService()->lockMoneyCard($createdMoneyCard['id']);
    	$lockedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertEquals('invalid', $lockedMoneyCard['cardStatus']);
    }

    public function testUnlockMoneyCard()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$this->getMoneyCardService()->lockMoneyCard($createdMoneyCard['id']);
    	$lockedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertEquals('invalid', $lockedMoneyCard['cardStatus']);
    	$this->getMoneyCardService()->unlockMoneyCard($createdMoneyCard['id']);
    	$unlockedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertEquals('normal', $unlockedMoneyCard['cardStatus']);
    }

    public function testDeleteMoneyCard()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$this->getMoneyCardService()->deleteMoneyCard($createdMoneyCard['id']);
    	$deletedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertNull($deletedMoneyCard);
    }

    public function testGetBatch()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$batch = $this->getMoneyCardService()->getBatch($getedMoneyCard['batchId']);
    	$this->assertGreaterThan(0, $batch['id']);
    }

    public function testLockBatch()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$batch = $this->getMoneyCardService()->getBatch($getedMoneyCard['batchId']);
    	$this->assertEquals('normal', $getedMoneyCard['cardStatus']);
    	$this->getMoneyCardService()->lockBatch($batch['id']);
    	$moneyCardAfterLockBatch = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertEquals('invalid', $moneyCardAfterLockBatch['cardStatus']);

    }

    public function testUnlockBatch()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$batch = $this->getMoneyCardService()->getBatch($getedMoneyCard['batchId']);
    	$this->getMoneyCardService()->lockBatch($batch['id']);
    	$this->getMoneyCardService()->unlockBatch($batch['id']);
    	$moneyCardAfterUnLockBatch = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertEquals('normal', $moneyCardAfterUnLockBatch['cardStatus']);
    }

    public function testDeleteBatch()
    {
    	$moneyCardData = array(
    		'money'=>100,
    		'cardPrefix'=>'vip',
    		'cardLength'=>10,
    		'number'=>5,
    		'note'=>'MyNote',
    		'deadline'=>time()+3600,
    		'passwordLength'=>8);
    	$createdMoneyCard = $this->getMoneyCardService()->createMoneyCard($moneyCardData);
    	$getedMoneyCard = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$batch = $this->getMoneyCardService()->getBatch($getedMoneyCard['batchId']);
    	$this->getMoneyCardService()->deleteBatch($batch['id']);
    	$moneyCardAfterDeleteBatch = $this->getMoneyCardService()->getMoneyCard($createdMoneyCard['id']);
    	$this->assertNull($moneyCardAfterDeleteBatch);
    }

    private function getMoneyCardService()
    {
        return $this->getServiceKernel()->createService('MoneyCard.MoneyCardService');
    }

}