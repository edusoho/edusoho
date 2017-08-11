<?php

namespace Tests\Unit\Article;

use Biz\Article\Service\CategoryService;
use Biz\BaseTestCase;

class CategoryServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateCategoeryWithoutName()
    {
        $categoery = array(
            'code' => 'destest',
            'parentId' => 0,
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
        $ids = array($createdCategory1['id'], $createdCategory2['id']);
        $finds = $this->getCategoryService()->findCategoriesByIds($ids);
        $this->assertEquals(2, count($finds));
    }

    public function testGetCategoryByParentId()
    {
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 1);
        $createdCategory2 = $this->createCategory('dwwsqq', 'code3', 3);
        $finds = $this->getCategoryService()->getCategoryByParentId(1);
        $this->assertEquals($createdCategory1['id'], $finds['id']);
        $this->assertEquals($createdCategory1['name'], $finds['name']);
        $this->assertEquals($createdCategory1['code'], $finds['code']);
        $this->assertEquals($createdCategory1['parentId'], $finds['parentId']);
    }

    public function testGetCategoryTree()
    {
        $createdCategory1 = $this->createCategory('deew', 'code1');
        $createdCategory2 = $this->createCategory('deewddd', 'code2', $createdCategory1['id']);
        $createdCategory3 = $this->createCategory('dwwsqq', 'code3', $createdCategory1['id']);
        $tree = $this->getCategoryService()->getCategoryTree();

        $this->assertEquals(count($tree), 3);
    }

    public function testUpdateCategory()
    {
        $createdCategory1 = $this->createCategory('deewddd', 'code2', 1);
        $updateField = array('name' => 'name1', 'code' => 'testcode', 'weight' => 3, 'parentId' => 3);
        $updateCategory = $this->getCategoryService()->updateCategory($createdCategory1['id'], $updateField);

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

    public function testFindCategoryTreeIds()
    {
        $category1 = $this->createCategory('name1', 'code1', 0);
        $category2 = $this->createCategory('name1-1', 'code1_1', $category1['id']);
        $category3 = $this->createCategory('name1-2', 'code1_2', $category1['id']);
        $category4 = $this->createCategory('name2-1', 'code2_1', $category3['id']);
        $category5 = $this->createCategory('name5', 'code5', 0);

        $categoryIds = $this->getCategoryService()->findCategoryTreeIds($category1['id'], $isPublished = true);
        $this->assertEquals(4, count($categoryIds));

        $categoryIds = $this->getCategoryService()->findCategoryTreeIds($category3['id'], $isPublished = true);
        $this->assertEquals(2, count($categoryIds));
    }

    private function createCategory($name = 'cate', $code = 'code1', $parentId = 0)
    {
        $category = array(
            'name' => $name,
            'code' => $code,
            'parentId' => $parentId,
        );

        return $this->getCategoryService()->createCategory($category);
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }
}
