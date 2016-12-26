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
			
			$results = $dao->search($testConditon['testCondition'], array(), 0, 10);
			foreach ($results as $key => $result) {
				$this->assertArrayEquals($result, $testConditon['expectedResults'][$key], $testFields);
			}
		}
	}
}