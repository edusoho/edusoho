<?php

namespace Tests\Unit\Taxonomy;

use Biz\Taxonomy\Service\CategoryService;
use Biz\BaseTestCase;

class CategoryServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->getCategoryService()->addGroup(array('id' => 1, 'code' => 'code1', 'name' => '课程分类1', 'depth' => 3));
        $this->getCategoryService()->addGroup(array('id' => 2, 'code' => 'code2', 'name' => '课程分类2', 'depth' => 3));
        $this->getCategoryService()->addGroup(array('id' => 3, 'code' => 'code3', 'name' => '课程分类3', 'depth' => 3));
    }

    public function testFindCategoriesByGroupIdAndParentId()
    {
        $this->mockBiz(
            'Taxonomy:CategoryDao',
            array(
                array(
                    'functionName' => 'findByGroupIdAndParentId',
                    'withParams' => array(1, 2),
                    'returnValue' => array(
                        array('id' => 1),
                    ),
                ),
            )
        );

        $result = $this->getCategoryService()->findCategoriesByGroupIdAndParentId(1, 2);
        $this->assertEquals(array(
            array('id' => 1),
        ), $result);
    }

    public function testFindCategoriesByGroupIdAndParentIdWithWrongParams()
    {
        $result = $this->getCategoryService()->findCategoriesByGroupIdAndParentId(-1, 2);
        $this->assertEquals(array(), $result);
    }

    public function testGetCategoryWithEmptyId()
    {
        $result = $this->getCategoryService()->getCategory(0);
        $this->assertEquals(null, $result);
    }

    public function testGetCategoryStructureTree()
    {
        $result = $this->getCategoryService()->getCategoryStructureTree(1);
        $this->assertEquals(array(), $result);
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

    public function testAddCategory()
    {
        $rootCategory = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1, 'parentId' => 0);
        $rootCategory = $this->getCategoryService()->createCategory($rootCategory);

        $category = array('name' => '测试分类1', 'code' => 'code2', 'weight' => 100, 'groupId' => 1, 'parentId' => $rootCategory['id']);
        $category = $this->getCategoryService()->createCategory($category);

        $this->assertEquals('1', $rootCategory['id']);
        $this->assertEquals('1', $category['parentId']);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddCategoryWithNoParentId()
    {
        $category = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $category = $this->getCategoryService()->createCategory($category);

        $this->assertNotEmpty($category);
        $this->assertEquals('1', $category['path']);
        $this->assertEquals('code', $category['code']);
        $this->assertEquals('测试分类1', $category['name']);
        $this->assertEquals(100, $category['weight']);
        $this->assertEquals(1, $category['groupId']);
        $this->assertEquals(0, $category['parentId']);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddCategoryWithNotExistParentId()
    {
        $category = array('name' => '', 'code' => 'code', 'weight' => 100, 'groupId' => 1, 'parentId' => 11111);
        $this->getCategoryService()->createCategory($category);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddCategoryWithEmptyCategoryName()
    {
        $category = array('name' => '', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $this->getCategoryService()->createCategory($category);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddCategoryWithNotExistGroupId()
    {
        $category = array('name' => 'name', 'code' => 'code', 'weight' => 100, 'groupId' => 100000);
        $this->getCategoryService()->createCategory($category);
    }

    /**
     * @group add
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testAddCategoryWithCodeAlreayExist()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $this->getCategoryService()->createCategory($categoryA);
        $categoryB = array('name' => '测试分类1', 'code' => 'code', 'weight' => 50, 'groupId' => 1);
        $this->getCategoryService()->createCategory($categoryB);
    }

    /**
     * @group get
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testGetCategory()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $createdCategory = $this->getCategoryService()->createCategory($categoryA);
        $foundCategory = $this->getCategoryService()->getCategory($createdCategory['id']);
        $this->assertEquals($createdCategory, $foundCategory);
    }

    /**
     * @group get
     */
    public function testGetCategoryWithNotExistCategoryId()
    {
        $foundCategory = $this->getCategoryService()->getCategory(999);
        $this->assertNull($foundCategory);
    }

    /**
     * @group get
     */
    public function testGetCategoryByCode()
    {
        $this->mockBiz(
            'Taxonomy:CategoryDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'withParams' => array('code'),
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );
        $foundCategory = $this->getCategoryService()->getCategoryByCode('code');
        $this->assertEquals(array(
            'id' => 1,
        ), $foundCategory);
    }

    /**
     * @group get
     */
    public function testFindCategories()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => 0, 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => 0, 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);
        $categories = $this->getCategoryService()->findCategories(1);
        $this->assertContains($createdCategoryA, $categories);
        $this->assertContains($createdCategoryB, $categories);
    }

    public function testFindCategoriesWithMagicOpen()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'parentId' => 0, 'groupId' => 1);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'parentId' => 0, 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('magic'),
                    'returnValue' => array('enable_org' => 1),
                ),
            )
        );
        $categories = $this->getCategoryService()->findCategories(1);
        $this->assertContains($createdCategoryA, $categories);
        $this->assertContains($createdCategoryB, $categories);
    }

    /**
     * @group get
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testFindCategoriesWithNotExistGroupId()
    {
        $this->getCategoryService()->findCategories(999);
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

    public function testFindGroupRootCategories()
    {
        $this->mockBiz(
            'Taxonomy:CategoryGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'withParams' => array('parentCodeA'),
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );
        $parentCategoryA = array('name' => '测试分类1', 'code' => 'parentCodeA', 'parentId' => 0, 'groupId' => 1);
        $createdParentCategoryA = $this->getCategoryService()->createCategory($parentCategoryA);

        $results = $this->getCategoryService()->findGroupRootCategories($createdParentCategoryA['code']);
        $this->assertContains($createdParentCategoryA, $results);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testFindGroupRootCategoriesWithEmptyGroup()
    {
        $this->mockBiz(
            'Taxonomy:CategoryGroupDao',
            array(
                array(
                    'functionName' => 'getByCode',
                    'withParams' => array('parentCodeA'),
                    'returnValue' => array(),
                ),
            )
        );
        $this->getCategoryService()->findGroupRootCategories('parentCodeA');
    }

    public function testFindCategoryChildrenIds()
    {
        $this->mockBiz(
            'Taxonomy:CategoryGroupDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );
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

    public function testFindCategoryChildrenIdsWithEmptyCategory()
    {
        $results = $this->getCategoryService()->findCategoryChildrenIds(999);
        $this->assertEquals(array(), $results);
    }

    public function testFindCategoryBreadcrumbsWithEmptyCategory()
    {
        $results = $this->getCategoryService()->findCategoryBreadcrumbs(999);
        $this->assertEquals(array(), $results);
    }

    public function testFindCategoryBreadcrumbs()
    {
        $this->mockBiz(
            'Taxonomy:CategoryGroupDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'id' => 1,
                    ),
                ),
            )
        );
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

    /**
     * @group get
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testGetCategoryTree()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'codeA', 'weight' => 100, 'groupId' => 1);
        $createdCategoryA = $this->getCategoryService()->createCategory($categoryA);
        $categoryB = array('name' => '测试分类2', 'code' => 'codeB', 'weight' => 10, 'groupId' => 1, 'parentId' => $createdCategoryA['id']);
        $createdCategoryB = $this->getCategoryService()->createCategory($categoryB);
        $categoryC = array('name' => '测试分类3', 'code' => 'codeC', 'weight' => 20, 'groupId' => 1, 'parentId' => $createdCategoryB['id']);
        $this->getCategoryService()->createCategory($categoryC);
        $categories = $this->getCategoryService()->getCategoryTree(1);

        $this->assertEquals(3, count($categories));
        $paths = array('1', '1|2', '1|2|3');

        foreach ($categories as $key => $category) {
            $this->assertContains($category['path'], $paths);
        }
    }

    /**
     * @group get
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testGetCategoryTreeWithNotExistGroupId()
    {
        $this->getCategoryService()->getCategoryTree(999);
    }

    /**
     * @group update
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUpdateCategory()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $createdCategory = $this->getCategoryService()->createCategory($categoryA);
        $updateCategory = $this->getCategoryService()->updateCategory($createdCategory['id'], array(
            'code' => 'xxx',
            'name' => '测试分类2',
            'weight' => 20,
            'groupId' => 1, ));

        $this->assertEquals('xxx', $updateCategory['code']);
        $this->assertEquals('测试分类2', $updateCategory['name']);
        $this->assertEquals(20, $updateCategory['weight']);
        $this->assertEquals(1, $updateCategory['groupId']);
        $this->assertEquals(0, $updateCategory['parentId']);
        $this->assertEquals('1', $updateCategory['path']);
    }

    /**
     * @group delete
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testDeleteCategory()
    {
        $categoryA = array('name' => '测试分类1', 'code' => 'code', 'weight' => 100, 'groupId' => 1);
        $createdCategory = $this->getCategoryService()->createCategory($categoryA);
        $result = $this->getCategoryService()->deleteCategory($createdCategory['id']);
        $category = $this->getCategoryService()->getCategory($createdCategory['id']);
        $this->assertEquals(1, $result);
        $this->assertFalse($category);

        $result = $this->getCategoryService()->deleteCategory($createdCategory['id']);
        $this->assertEquals(0, $result);
    }

    /**
     * @group group
     */
    public function testGetGroups()
    {
        $groups = $this->getCategoryService()->getGroups(0, 2);
        $this->assertEquals(2, count($groups));
        $this->assertContains(array('id' => 1, 'code' => 'code1', 'name' => '课程分类1', 'depth' => 3), $groups);
        $this->assertContains(array('id' => 2, 'code' => 'code2', 'name' => '课程分类2', 'depth' => 3), $groups);
    }

    /**
     * @group group
     */
    public function testGetGroup()
    {
        $group = $this->getCategoryService()->getGroup(1);
        $this->assertEquals(array('id' => 1, 'code' => 'code1', 'name' => '课程分类1', 'depth' => 3), $group);

        $group = $this->getCategoryService()->getGroup(999);
        $this->assertNull($group);
    }

    /**
     * @group current
     */
    public function testGetGroupByCode()
    {
        $group = $this->getCategoryService()->getGroupByCode('code1');
        $this->assertEquals(array('id' => 1, 'code' => 'code1', 'name' => '课程分类1', 'depth' => 3), $group);

        $group = $this->getCategoryService()->getGroupByCode('xxx');
        $this->assertFalse($group);
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
