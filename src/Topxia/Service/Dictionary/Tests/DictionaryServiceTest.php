<?php
namespace Topxia\Service\Dictionary\Tests;

use Topxia\Service\Common\BaseTestCase;

class DictionaryServiceTest extends BaseTestCase
{
	public function testGetDictionary()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);
		$dictionary = $this->getDictionaryService()->getDictionary($newDictionary['id']);

		$this->assertEquals($dictionary,$newDictionary);

	}

	public function testDeleteDictionary()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);
		$this->getDictionaryService()->deleteDictionary($newDictionary['id']);
		$result = $this->getDictionaryService()->getDictionary($newDictionary['id']);
		$this->assertEmpty($result);
	}

	public function testUpdateDictionary()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);

		$newDictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);

		$updateInfo = array(
			'name' => '教学',
			'weight' => 2,
			);
		$dictionary = $this->getDictionaryService()->updateDictionary($newDictionary['id'], $updateInfo);

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

		$newDictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);

		$dictionary = $this->getDictionaryService()->getDictionary($newDictionary['id']);

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
		$dictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);

		$dictionaryInfo2 = array(
			'type' => 'quitReason',
			'name' => '教师能力差',
			'weight' => 3,
			'createdTime' =>time()
			);
		$dictionaryTemp = $this->getDictionaryService()->addDictionary($dictionaryInfo);

		if ($dictionaryTemp['weight']>$dictionary['weight']) {
			$dictionariesTemp = array($dictionaryTemp,$dictionary);
		} else {
			$dictionariesTemp = array($dictionary,$dictionaryTemp);
		}
		$dictionaries = $this->getDictionaryService()->findAllDictionariesOrderByWeight();
		$this->assertEquals($dictionaries,$dictionariesTemp);
	}

	public function findDictionaryByName()
	{
		$dictionaryInfo = array(
			'type' => 'quitReason',
			'name' => '教学质量差',
			'weight' => 1,
			'createdTime' =>time()
			);
		$newDictionary = $this->getDictionaryService()->addDictionary($dictionaryInfo);

		$dictionary = $this->getDictionaryService()->getDictionayByName($newDictionary['name']);

		$this->assertEquals($dictionay,$newDictionary);
	}

	protected function getDictionaryService()
    {
        return $this->getServiceKernel()->createService('Dictionary.DictionaryService');
    }
}