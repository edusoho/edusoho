<?php

namespace Tests\Base;

use Biz\BaseTestCase;

class BaseDaoTestCase extends BaseTestCase
{
	public function searchTestUtil($dao, $mockedData, $conditions, $testFields)
	{
		foreach ($conditions as $condition) {
			$count = $dao->count($condition);
			$this->assertEquals($count, 1);
			$result = $dao->search($condition, array(), 0, 10);
			$this->assertArrayEquals($result[0], $mockedData, $testFields);
		}


	}
}