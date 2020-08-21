<?php

namespace Tests\Unit\QuestionBank\Service;

use Biz\BaseTestCase;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\User\Service\AuthService;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

class QuestionBankServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateQuestionBankWithEmptyArgumentFieldException()
    {
        $field = [];
        $this->getQuestionBankService()->createQuestionBank($field);
    }

    /**
     * @expectedException \Biz\Taxonomy\CategoryException
     * @expectedExceptionMessage exception.category.not_found
     */
    public function testCreateQuestionBankWithEmptyArgumentCategoryException()
    {
        $field = ['name' => 'test', 'categoryId' => '1'];
        $this->getQuestionBankService()->createQuestionBank($field);
    }

    public function testCreateQuestionBank()
    {
        $field = ['name' => 'QuestionBank_test', 'categoryId' => '1'];
        $this->createCategory();

        $result = $this->getQuestionBankService()->createQuestionBank($field);
        $this->assertEquals('QuestionBank_test', $result['name']);
    }

    /**
     * @expectedException \Biz\Taxonomy\CategoryException
     * @expectedExceptionMessage exception.category.not_found
     */
    public function testUpdateQuestionBankWithMembersWithEmptyCategoryException()
    {
        $this->createQuestionBank();
        $id = 1;
        $field = ['name' => 'QuestionBank_test', 'categoryId' => '2'];
        $members = [];

        $this->getQuestionBankService()->updateQuestionBankWithMembers($id, $field, $members);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUpdateQuestionBankWithEmptyArgumentException()
    {
        $id = 1;
        $field = [];
        $this->getQuestionBankService()->updateQuestionBank($id, $field);
    }

    public function testUpdateQuestionBank()
    {
        $this->createQuestionBank();
        $this->createCategory();

        $field = ['name' => 'QuestionBank_test2', 'categoryId' => '1'];
        $id = 1;

        $result = $this->getQuestionBankService()->updateQuestionBank($id, $field);
        $this->assertEquals('QuestionBank_test2', $result['name']);
    }

    public function testDeleteQuestionBank()
    {
        $this->createQuestionBank();
        $id = 1;
        $this->getQuestionBankService()->deleteQuestionBank($id);

        $result = $this->getQuestionBankService()->getQuestionBank($id);
        $this->assertEquals(null, $result['itemBank']);
    }

    public function testCanManageBank()
    {
        $bankId = 1;

        $result = $this->getQuestionBankService()->canManageBank($bankId);
        $this->assertTrue($result);
    }

    public function testFindUserManageBanksWithEmptyBanks()
    {
        $result = $this->getQuestionBankService()->findUserManageBanks();
        $this->assertEquals([], $result);
    }

    public function testFindUserManageBanks()
    {
        $this->createQuestionBank();

        $result = $this->getQuestionBankService()->findUserManageBanks();
        $this->assertEquals('QuestionBank_test', $result[0]['name']);
    }

    protected function createCategory()
    {
        $category = ['name' => 'test', 'parentId' => '0', 'id' => '1'];
        $this->getCategoryService()->createCategory($category);
    }

    protected function createQuestionBank()
    {
        $this->createCategory();
        $field = ['name' => 'QuestionBank_test', 'categoryId' => '1'];
        $this->getQuestionBankService()->createQuestionBank($field);
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->createService('ItemBank:ItemBank:ItemBankService');
    }
}
