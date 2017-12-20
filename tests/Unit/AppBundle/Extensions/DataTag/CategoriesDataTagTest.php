<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CategoriesDataTag;

class CategoriesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('name' => '课程', 'code' => 'course', 'depth' => 2));

        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => 0,
        ));

        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => 0,
        ));

        $category1_1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1_1',
            'code' => 'c1_1',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => $category1['id'],
        ));

        $category1_2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1_2',
            'code' => 'c1_2',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => $category1['id'],
        ));

        $category2_1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2_1',
            'code' => 'c2_1',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => $category2['id'],
        ));

        $category2_2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2_2',
            'code' => 'c2_2',
            'groupId' => $group['id'],
            'weight' => 1,
            'parentId' => $category2['id'],
        ));

        $datatag = new CategoriesDataTag();

        $categories = $datatag->getData(array('group' => 'course'));
        $this->assertEquals(6, count($categories));

        $categories = $datatag->getData(array('group' => 'course', 'parentId' => $category1['id']));
        $this->assertEquals(2, count($categories));
        foreach ($categories as $category) {
            $this->assertEquals($category1['id'], $category['parentId']);
        }
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
