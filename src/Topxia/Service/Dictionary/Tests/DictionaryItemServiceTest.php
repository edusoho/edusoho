<?php
namespace Topxia\Service\Dictionary\Tests;

use Topxia\Service\Common\BaseTestCase;

class DictionaryItemServiceTest extends BaseTestCase
{
	public function testGetDictionaryItem()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);
		$dictionary = $this->getDictionaryItemService()->getDictionaryItem($newDictionaryItem['id']);

		$this->assertEquals($dictionary,$newDictionaryItem);

	}

	public function testDeleteDictionaryItem()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);
		$this->getDictionaryItemService()->deleteDictionaryItem($newDictionaryItem['id']);
		$result = $this->getDictionaryItemService()->getDictionaryItem($newDictionaryItem['id']);
		$this->assertEmpty($result);
	}

	public function testUpdateDictionaryItem()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);

		$updateInfo = array(
			'name' => '教学',
			'weight' => 2,
			);
		$dictionary = $this->getDictionaryItemService()->updateDictionaryItem($newDictionaryItem['id'], $updateInfo);

		$this->assertEquals($dictionary['name'],$updateInfo['name']);
		$this->assertEquals($dictionary['weight'],$updateInfo['weight']);
	}

	public function testAddDictionsry()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);

		$dictionary = $this->getDictionaryItemService()->getDictionaryItem($newDictionaryItem['id']);

		$this->assertEquals($dictionary['type'],$dictionaryInfo['type']);
		$this->assertEquals($dictionary['name'],$dictionaryInfo['name']);
		$this->assertEquals($dictionary['weight'],$dictionaryInfo['weight']);
		$this->assertEquals($dictionary['createdTime'],$dictionaryInfo['createdTime']);
	}

	public function testFindAllDictionariesOrderByWeight()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);
		$dictionary = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);

		$dictionaryInfo2 = array(
			'type' => 'quitReason',
			'name' => '教师能力差',
			'weight' => 3,
			'createdTime' =>time()
			);
		$dictionaryTemp = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);

		if ($dictionaryTemp['weight']>$dictionary['weight']) {
			$dictionariesTemp = array($dictionaryTemp,$dictionary);
		} else {
			$dictionariesTemp = array($dictionary,$dictionaryTemp);
		}
		$dictionaries = $this->getDictionaryItemService()->findAllDictionariesOrderByWeight();
		$this->assertEquals($dictionaries,$dictionariesTemp);
	}

	public function findDictionaryItemByName()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);
		$newDictionaryItem = $this->getDictionaryItemService()->addDictionaryItem($dictionaryInfo);

		$dictionary = $this->getDictionaryItemService()->findDictionaryByName($newDictionaryItem['name']);

		$this->assertEquals($dictionay,$newDictionaryItem);
	}

	protected function getDictionaryItemService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryItemService');
    }
}