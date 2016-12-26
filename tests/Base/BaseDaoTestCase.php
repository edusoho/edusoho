<?php

namespace Tests\Base;

use Biz\BaseTestCase;

class BaseDaoTestCase extends BaseTestCase
{
	public function searchTestUtil($dao, $testConditons, $testFields)
	{
		foreach ($testConditons as $testConditon) {
			$count = $dao->count($testConditon['testCondition']);
			$this->assertEquals($count, $testConditon['expectedCount']);
			
			$orderBy = empty($testConditon['orderBy']) ? array() : $testConditon['orderBy'];
			$results = $dao->search($testConditon['testCondition'], $orderBy, 0, 10);
			foreach ($results as $key => $result) {
				$this->assertArrayEquals($result, $testConditon['expectedResults'][$key], $testFields);
			}
		}
	}
}