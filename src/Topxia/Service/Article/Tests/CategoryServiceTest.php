<?php
namespace Topxia\Service\Article\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

class CategoeryServiceTest extends BaseTestCase
{
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateCategoeryWithoutName()
    {
        $categoery = array(
            'code'     => 'destest',
            'weight'   => 12,
            'parentId' => 0
        );
        $this->getCategoryService()->createCategory($categoery);
    }

    public function testCreateCategoery()
    {
        $createdCategory = $this->createCategory();
        $this->assertEquals('cate', $createdCategory['name']);
        $this->assertEquals('code1', $createdCategory['code']);
        $this->assertEquals(12, $createdCategory['weight']);
        $this->assertEquals(0, $createdCategory['parentId']);
    }

    public function testFindCategoriesByIds()
    {
        $this->createCategory('deew', 'code1');
        $createdCategory1 = $this->createCategory('deewddd', 'code2');
        $createdCategory2 = $this->createCategory('dwwsqq', 'code3');
        $ids              = array($createdCategory1['id'], $createdCategory2['id']);
        $finds            = $this->getCategoryService()->findCategoriesByIds($ids);
        $this->assertEquals(2, count($finds));

    }

    public function testGetCategoryByParentId()
    {
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 11, 1);
        $createdCategory2 = $this->createCategory('dwwsqq', 'code3', 12, 3);
        $finds            = $this->getCategoryService()->getCategoryByParentId(1);
        $this->assertEquals($createdCategory1['id'], $finds['id']);
        $this->assertEquals($createdCategory1['name'], $finds['name']);
        $this->assertEquals($createdCategory1['code'], $finds['code']);
        $this->assertEquals($createdCategory1['weight'], $finds['weight']);
        $this->assertEquals($createdCategory1['parentId'], $finds['parentId']);

    }

    private function createCategory($name = 'cate', $code = 'code1', $weight = 12, $parentId = 0)
    {
        $category = array(
            'name'     => $name,
            'code'     => $code,
            'weight'   => $weight,
            'parentId' => $parentId
        );
        return $this->getCategoryService()->createCategory($category);
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }
}
