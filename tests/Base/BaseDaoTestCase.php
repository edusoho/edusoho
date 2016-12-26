<?php

namespace Tests\Base;

use Biz\BaseTestCase;

class BaseDaoTestCase extends BaseTestCase
{
	public function searchTestUtil($dao, $testConditons, $testFields)
	{
		foreach ($testConditons as $testConditon) {
			$count = $dao->count($testConditon['condition']);
			$this->assertEquals($count, $testConditon['countResult']);
			
			$results = $dao->search($testConditon['condition'], array(), 0, 10);
			foreach ($results as $key => $result) {
				$this->assertArrayEquals($result, $testConditon['results'][$key], $testFields);
			}
		}
	}
}