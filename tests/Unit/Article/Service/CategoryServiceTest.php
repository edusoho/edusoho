<?php

namespace Tests\Unit\Article\Service;

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

    public function testGetCategoryByCode()
    {
        $createdCategory = $this->createCategory();

        $result = $this->getCategoryService()->getCategoryByCode('code1');
        $this->assertEquals($createdCategory, $result);
    }

    public function testGetCategoryStructureTree()
    {
        $createdCategory = $this->createCategory();
        $tree = $this->getCategoryService()->getCategoryStructureTree();

        $result = reset($tree);
        $this->assertEquals($createdCategory['name'], $result['name']);
    }

    public function testIsCategoryCodeAvaliable()
    {
        $createdCategory = $this->createCategory();
        $result1 = $this->getCategoryService()->isCategoryCodeAvaliable('');
        $this->assertFalse($result1);

        $result2 = $this->getCategoryService()->isCategoryCodeAvaliable('test', 'test');
        $this->assertTrue($result2);

        $result3 = $this->getCategoryService()->isCategoryCodeAvaliable($createdCategory['code'], 'test');
        $this->assertFalse($result3);
    }

    public function testFindCategoryBreadcrumbsWithEmptyCategory()
    {
        $results = $this->getCategoryService()->findCategoryBreadcrumbs(999);
        $this->assertEquals(array(), $results);
    }

    public function testFindCategoryBreadcrumbs()
    {
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $this->getCategoryService()->createCategory($categoryB);
        $results = $this->getCategoryService()->findCategoryBreadcrumbs($createdCategoryA['id']);
        $parentCategory = reset($results);
        $childCategory = end($results);
        $this->assertEquals($createdParentCategoryA['code'], $parentCategory['code']);
        $this->assertEquals($createdCategoryA['code'], $childCategory['code']);
    }

    public function testMakeNavCategories()
    {
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $this->getCategoryService()->createCategory($categoryA);
        $this->getCategoryService()->createCategory($categoryB);

        $results = $this->getCategoryService()->makeNavCategories('codeA');
        $this->assertEquals(3, count($results));
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

    public function testSortCategories()
    {
        $rootCategory = array('name' => '测试分类1', 'code' => 'code', 'groupId' => 1, 'parentId' => 0);
        $rootCategory = $this->getCategoryService()->createCategory($rootCategory);

        $category = array('name' => '测试分类1', 'code' => 'code2', 'groupId' => 1, 'parentId' => $rootCategory['id']);
        $category = $this->getCategoryService()->createCategory($category);

        $this->getCategoryService()->sortCategories(array($rootCategory['id'], $category['id']));

        $expectedRootCategory = $this->getCategoryService()->getCategory($rootCategory['id']);
        $expectedCategory = $this->getCategoryService()->getCategory($category['id']);

        $this->assertEquals($rootCategory['weight'] + 1, $expectedRootCategory['weight']);
        $this->assertEquals($category['weight'] + 2, $expectedCategory['weight']);
    }

    public function testFindCategoryChildrenIds()
    {
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);

        $results = $this->getCategoryService()->findCategoryChildrenIds($createdParentCategoryA['id']);
        $this->assertContains($createdCategoryA['id'], $results);
        $this->assertContains($createdCategoryB['id'], $results);
    }

    public function testFindAllCategoriesByParentId()
    {
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);
        $categories = $this->getCategoryService()->findAllCategoriesByParentId($createdParentCategoryA['id']);
        $this->assertContains($createdCategoryA, $categories);
        $this->assertContains($createdCategoryB, $categories);
    }

    public function findCategoriesCountByParentId()
    {
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => $createdParentCategoryA['id'], 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);
        $categories = $this->getCategoryService()->findCategoriesCountByParentId($createdParentCategoryA['id']);
        $this->assertContains($createdCategoryA, $categories);
        $this->assertContains($createdCategoryB, $categories);
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
