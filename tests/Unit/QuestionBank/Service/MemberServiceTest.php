<?php

namespace Tests\Unit\QuestionBank\Service;

use Biz\BaseTestCase;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;

class MemberServiceTest extends BaseTestCase
{
    public function testFindMembersByBankId()
    {
        $bankId = 1;
        $field = ['bankId' => '1', 'userId' => '1'];
        $this->createQuestionBank();
        $this->getMemberService()->createMember($field);

        $result = $this->getMemberService()->findMembersByBankId($bankId);
        $this->assertEquals('1', $result[0]['userId']);
    }

    public function testGetMemberByBankIdAndUserId()
    {
        $bankId = 1;
        $userId = 1;
        $fields = ['bankId' => '1', 'userId' => '1'];
        $this->createQuestionBank();
        $this->getMemberService()->createMember($fields);

        $result = $this->getMemberService()->getMemberByBankIdAndUserId($bankId, $userId);
        $this->assertEquals('1', $result['userId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateMemberWithInvalidArgumentException()
    {
        $fields = [];
        $this->getMemberService()->createMember($fields);
    }

    public function testCreateMember()
    {
        $fields = ['bankId' => '1', 'userId' => '1'];
        $this->createQuestionBank();

        $result = $this->getMemberService()->createMember($fields);
        $this->assertEquals('1', $result['userId']);
    }

    public function testBatchDeleteByBankIdWithEmptyArgumentException()
    {
        $bankId = [];
        $result = $this->getMemberService()->batchDeleteByBankId($bankId);

        $this->assertEquals(null, $result);
    }

    public function testBatchDeleteByBankId()
    {
        $this->createQuestionBank();
        $bankId = 1;
        $this->getMemberService()->batchDeleteByBankId($bankId);

        $result = $this->getMemberService()->findMembersByBankId($bankId);
        $this->assertEquals([], $result);
    }

    public function testFindMembersByUserId()
    {
        $userId = 1;
        $fields = ['bankId' => '1', 'userId' => '1'];
        $this->createQuestionBank();
        $this->getMemberService()->createMember($fields);

        $result = $this->getMemberService()->findMembersByBankId($userId);
        $this->assertEquals(1, $result[0]['userId']);
    }

    public function testBatchCreateMembersWithEmptyArgumentException()
    {
        $userIds = [];
        $bankId = [];

        $result = $this->getMemberService()->batchCreateMembers($userIds, $bankId);
        $this->assertEquals(null, $result);
    }

    public function testBatchCreateMembers()
    {
        $userIds = ['1'];
        $bankId = 1;
        $this->createQuestionBank();

        $result = $this->getMemberService()->batchCreateMembers($bankId, $userIds);
        $this->assertTrue($result);
    }

    public function testResetBankMembers()
    {
        $members = '1';
        $bankId = 1;
        $this->createQuestionBank();
        $this->getMemberService()->resetBankMembers($bankId, $members);

        $result = $this->getMemberService()->findMembersByBankId($bankId);
        $this->assertEquals(1, $result[0]['userId']);
    }

    public function testIsMemberInBankWithEmptyMember()
    {
        $bankId = 1;
        $userId = 2;
        $this->createQuestionBank();

        $result = $this->getMemberService()->isMemberInBank($bankId, $userId);
        $this->assertFalse($result);
    }

    public function testIsMemberInBank()
    {
        $bankId = 1;
        $userId = 1;
        $fields = ['bankId' => '1', 'userId' => '1'];
        $this->createQuestionBank();
        $this->getMemberService()->createMember($fields);

        $result = $this->getMemberService()->isMemberInBank($bankId, $userId);
        $this->assertTrue($result);
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
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }
}
