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
            'parentId' => 0
        );
        $this->getCategoryService()->createCategory($categoery);
    }

    public function testCreateCategoery()
    {
        $createdCategory = $this->createCategory();
        $this->assertEquals('cate', $createdCategory['name']);
        $this->assertEquals('code1', $createdCategory['code']);
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
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 1);
        $createdCategory2 = $this->createCategory('dwwsqq', 'code3', 3);
        $finds            = $this->getCategoryService()->getCategoryByParentId(1);
        $this->assertEquals($createdCategory1['id'], $finds['id']);
        $this->assertEquals($createdCategory1['name'], $finds['name']);
        $this->assertEquals($createdCategory1['code'], $finds['code']);
        $this->assertEquals($createdCategory1['parentId'], $finds['parentId']);
    }

    public function testGetCategoryTree()
    {
        $createdCategory1 = $this->createCategory('deew', 'code1');
        $createdCategory2 = $this->createCategory('deewddd', 'code2',$createdCategory1['id']);
        $createdCategory3 = $this->createCategory('dwwsqq', 'code3',$createdCategory1['id']);
        $tree             = $this->getCategoryService()->getCategoryTree();

        $this->assertEquals(count($tree), 3);

    }

    public function testUpdateCategory()
    {
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 1);
        $updateField      = array('name' => 'name1', 'code' => 'testcode', 'weight' => 3, 'parentId' => 3);
        $updateCategory   = $this->getCategoryService()->updateCategory($createdCategory1['id'], $updateField);

        $this->assertEquals($updateField['name'], $updateCategory['name']);
        $this->assertEquals($updateField['code'], $updateCategory['code']);
        $this->assertEquals($updateField['weight'], $updateCategory['weight']);
        $this->assertEquals($updateField['parentId'], $updateCategory['parentId']);
    }

    public function testDeleteCategory()
    {
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 1);
        $this->getCategoryService()->deleteCategory($createdCategory1['id']);

        $this->assertEquals(null, $this->getCategoryService()->getCategory($createdCategory1['id']));
    }

    private function createCategory($name = 'cate', $code = 'code1', $parentId = 0)
    {
        $category = array(
            'name'     => $name,
            'code'     => $code,
            'parentId' => $parentId
        );
        return $this->getCategoryService()->createCategory($category);
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }
}
