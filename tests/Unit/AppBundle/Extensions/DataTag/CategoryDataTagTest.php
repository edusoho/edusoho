<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CategoryDataTag;

class CategoryDataTagTest extends BaseTestCase
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

        $datatag = new CategoryDataTag();
        $category = $datatag->getData(array('categoryId' => $category1['id']));
        $this->assertEquals($category1['id'], $category['id']);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
