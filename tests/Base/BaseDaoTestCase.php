<?php

namespace Tests\Base;

use Biz\BaseTestCase;

class BaseDaoTestCase extends BaseTestCase
{
	public function searchTestUtil($dao, $mockedData, $testFields)
	{
		$declares = $dao->declares();
		$conditions = $declares['conditions'];
		foreach ($conditions as $value) {
			$index = stripos($value, "=");
			if ($index !== false && $index >=0) {
				$mockedDataKey = trim(substr($value, 0, $index));
				$conditionValue = $mockedData[$mockedDataKey];
				$valueIndex = stripos($value, ':');
				$conditionKey = substr($value, $valueIndex+1);
				$conditionKey = rtrim($conditionKey, ')');

				$condition = array($conditionKey => $conditionValue);

				$count = $dao->count($condition);
				$this->assertEquals($count, 1);
				$result = $dao->search($condition, array(), 0, 10);
				$this->assertArrayEquals($result[0], $mockedData, $testFields);
			}

			$index = stripos($value, "IN");
			if ($index !== false && $index >=0) {
				$mockedDataKey = trim(substr($value, 0, $index));
				$conditionValue = $mockedData[$mockedDataKey];
				$valueIndex = stripos($value, ':');
				$conditionKey = substr($value, $valueIndex+1);
				$conditionKey = rtrim($conditionKey, ')');

				$condition = array($conditionKey => array($conditionValue));

				$count = $dao->count($condition);
				$this->assertEquals($count, 1);
				$result = $dao->search($condition, array(), 0, 10);
				$this->assertArrayEquals($result[0], $mockedData, $testFields);
			}
		}


	}
}