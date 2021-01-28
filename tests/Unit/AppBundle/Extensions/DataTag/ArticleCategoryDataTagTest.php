<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ArticleCategoryDataTag;

class ArticleCategoryDataTagTest extends BaseTestCase
{
    /**
     * @group current
     *
     * @return [type] [description]
     */
    public function testGetData()
    {
        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'parentId' => 0,
        ));

        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => 0,
        ));

        $category1_1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1_1',
            'code' => 'c1_1',
            'weight' => 1,
            'parentId' => $category1['id'],
        ));

        $category1_2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1_2',
            'code' => 'c1_2',
            'weight' => 1,
            'parentId' => $category1['id'],
        ));

        $category2_1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2_1',
            'code' => 'c2_1',
            'weight' => 1,
            'parentId' => $category2['id'],
        ));

        $category2_2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2_2',
            'code' => 'c2_2',
            'weight' => 1,
            'parentId' => $category2['id'],
        ));

        $dataTag = new ArticleCategoryDataTag();
        $categories = $dataTag->getData(array());

        $this->assertEquals(6, count($categories));
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article:CategoryService');
    }
}
