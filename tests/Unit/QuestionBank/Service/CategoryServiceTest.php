<?php

namespace Tests\Unit\QuestionBank\Service;

use Biz\BaseTestCase;
use Biz\QuestionBank\Dao\CategoryDao;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;

class CategoryServiceTest extends BaseTestCase
{
    public function testCreateCategory()
    {
        $category = ['name' => 'test', 'parentId' => '0'];

        $result = $this->getCategoryService()->createCategory($category);
        $this->assertEquals('test', $result['name']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateCategoryWithInvalidArgumentException()
    {
        $category = [];
        $this->getCategoryService()->createCategory($category);
    }

    /**
     * @expectedException \Biz\Taxonomy\CategoryException
     * @expectedExceptionMessage exception.category.not_found
     */
    public function testUpdateCategoryWithEmptyArgumentCategoryException()
    {
        $id = 0;
        $fields = [];

        $this->getCategoryService()->updateCategory($id, $fields);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUpdateCategoryWithEmptyArgumentFieldException()
    {
        $this->createCategory();

        $id = 1;
        $fields = [];
        $this->getCategoryService()->updateCategory($id, $fields);
    }

    public function testUpdateCategory()
    {
        $this->createCategory();

        $id = 1;
        $fields = ['name' => 'test2'];
        $result = $this->getCategoryService()->updateCategory($id, $fields);

        $this->assertEquals('test2', $result['name']);
    }

    /**
     * @expectedException \Biz\Taxonomy\CategoryException
     * @expectedExceptionMessage exception.category.not_found
     */
    public function testDeleteCategoryWithEmptyArgumentCategoryException()
    {
        $id = 0;
        $fields = [];

        $this->getCategoryService()->deleteCategory($id, $fields);
    }

    public function testDeleteCategory()
    {
        $this->createCategory();
        $id = 1;
        $this->getCategoryService()->deleteCategory($id);

        $result = $this->getCategoryDao()->get($id);
        $this->assertEquals(null, $result['name']);
    }

    public function testFindCategoryChildrenWithEmptyArgumentCategory()
    {
        $id = 0;

        $result = $this->getCategoryService()->findCategoryChildren($id);
        $this->assertEquals([], $result);
    }

    public function testFindCategoryChildren()
    {
        $this->createCategory();
        $id = 1;

        $result = $this->getCategoryService()->findCategoryChildren($id);
        $this->assertEquals([], $result);
    }

    public function testGetCategoryAndBankMixedTreeArray()
    {
        $this->mockBiz('QuestionBank:QuessstionBankService', [
            [
                'functionName' => 'findUserManageBanks',
                'returnValue' => [],
            ],
        ]);

        $result = $this->getCategoryService()->getCategoryAndBankMixedTree();
        $this->assertEquals([], $result);
    }

    public function testGetCategoryAndBankMixedTree()
    {
        $this->createCategory();
        $fields = ['name' => 'QuestionBank_test', 'categoryId' => '1'];
        $this->getQuestionBankService()->createQuestionBank($fields);

        $result = $this->getCategoryService()->getCategoryAndBankMixedTree();
        $this->assertEquals('QuestionBank_test', $result[1]['name']);
    }

    public function testFindAllCategoriesByParentId()
    {
        $this->createCategory();
        $parentId = 0;

        $result = $this->getCategoryService()->findAllCategoriesByParentId($parentId);
        $this->assertEquals('test', $result[1]['name'
        ]);
    }

    protected function createCategory()
    {
        $category = ['name' => 'test', 'parentId' => '0', 'id' => '1'];
        $this->getCategoryService()->createCategory($category);
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    /**
     * @return CategoryDao
     */
    protected function getCategoryDao()
    {
        return $this->createDao('QuestionBank:CategoryDao');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
