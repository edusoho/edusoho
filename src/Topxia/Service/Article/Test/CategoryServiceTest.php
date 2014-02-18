<?php

namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;

class CategoryServiceTest extends BaseTestCase
{
	/**
     * @group addCategory
     * @expectedException Topxia\Service\Common\ServiceException
     */
	public function testAddCategory()
	{
		$fields = array('name' => 'social', 'code' => 'category1','weight' => '100','pageSize' => '10','published' => '0','parentId' => '0');
		$category = $this->getCategoryService()->createCategory($fields);

		$this->assertGreaterThan(0, $category['id']);
		$this->assertEquals($fields['name'], $category['name']);
		$this->assertEquals('category1', $category['type']);
	}

}